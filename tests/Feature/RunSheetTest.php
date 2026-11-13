<?php

namespace Tests\Feature;

use App\Models\DeliveryTask;
use App\Models\User;
use App\Notifications\ChooseNewPassword;
use App\Notifications\ConfirmEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * One test group per lab feature. Run them with:  php artisan test
 */
class RunSheetTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Secret#1234';

    // ---------- small helpers ----------

    private function makeAccount(string $role = User::ROLE_DRIVER, bool $verified = true, bool $onHold = false): User
    {
        static $count = 0;
        $count++;

        $account = new User([
            'name' => "Test {$role} {$count}",
            'email' => "{$role}{$count}@example.test",
            'password' => self::PASSWORD,
        ]);
        $account->role = $role;
        $account->email_verified_at = $verified ? now() : null;
        $account->suspended_at = $onHold ? now() : null;
        $account->save();

        return $account;
    }

    private function makeTask(User $driver, string $zone, string $item, string $status = DeliveryTask::SENT): DeliveryTask
    {
        return DeliveryTask::forceCreate([
            'item_name' => $item,
            'item_quantity' => 1,
            'recipient_name' => 'Test Recipient',
            'destination' => '1 Test Street',
            'zone' => $zone,
            'driver_id' => $driver->id,
            'status' => $status,
            'received_at' => $status === DeliveryTask::RECEIVED ? now() : null,
        ]);
    }

    // ---------- Feature 1: login ----------

    public function test_pages_require_a_sign_in(): void
    {
        $this->get('/home')->assertRedirect('/login');
        $this->get('/run-sheet')->assertRedirect('/login');
        $this->get('/desk')->assertRedirect('/login');
    }

    public function test_registering_always_makes_a_driver_even_if_a_role_is_posted(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'New Person',
            'email' => 'newperson@example.test',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
            'role' => User::ROLE_DISPATCHER, // a sneaky extra field
        ])
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status'); // flash message after register

        $account = User::where('email', 'newperson@example.test')->firstOrFail();

        $this->assertSame(User::ROLE_DRIVER, $account->role);
        $this->assertNull($account->email_verified_at);
        Notification::assertSentTo($account, ConfirmEmail::class);
    }

    public function test_signing_in_shows_a_flash_message(): void
    {
        $driver = $this->makeAccount();

        $this->post('/login', ['email' => $driver->email, 'password' => self::PASSWORD])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertAuthenticatedAs($driver);
    }

    public function test_wrong_password_is_refused(): void
    {
        $driver = $this->makeAccount();

        $this->post('/login', ['email' => $driver->email, 'password' => 'not-the-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_signing_out_shows_a_flash_message(): void
    {
        $driver = $this->makeAccount();

        $this->actingAs($driver)->post('/logout')
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertGuest();
    }

    // ---------- Feature 2: the session pick ----------

    public function test_picking_a_zone_saves_it_in_the_session(): void
    {
        $driver = $this->makeAccount();

        $this->actingAs($driver)->post('/zone', ['zone' => 'Hillcrest'])
            ->assertSessionHas('zone', 'Hillcrest')
            ->assertSessionHas('status'); // flash message after changing the pick
    }

    public function test_a_zone_that_is_not_on_the_list_is_refused(): void
    {
        $driver = $this->makeAccount();

        $this->actingAs($driver)->post('/zone', ['zone' => 'Atlantis'])
            ->assertSessionHasErrors('zone');
    }

    public function test_the_run_sheet_uses_the_default_zone_until_one_is_picked(): void
    {
        $driver = $this->makeAccount();
        $this->makeTask($driver, 'Riverside', 'Riverside parcel');
        $this->makeTask($driver, 'Hillcrest', 'Hillcrest parcel');

        $this->actingAs($driver)->get('/run-sheet')
            ->assertOk()
            ->assertSee('Riverside parcel')
            ->assertDontSee('Hillcrest parcel');
    }

    public function test_the_run_sheet_only_shows_the_picked_zone(): void
    {
        $driver = $this->makeAccount();
        $this->makeTask($driver, 'Riverside', 'Riverside parcel');
        $this->makeTask($driver, 'Hillcrest', 'Hillcrest parcel');

        $this->actingAs($driver)->withSession(['zone' => 'Hillcrest'])->get('/run-sheet')
            ->assertOk()
            ->assertSee('Hillcrest parcel')
            ->assertDontSee('Riverside parcel');
    }

    public function test_signing_out_wipes_the_zone_pick(): void
    {
        $driver = $this->makeAccount();

        $this->actingAs($driver)->withSession(['zone' => 'Hillcrest'])->post('/logout')
            ->assertSessionMissing('zone');
    }

    // ---------- Feature 3: roles ----------

    public function test_only_dispatchers_can_open_the_desk(): void
    {
        $driver = $this->makeAccount(User::ROLE_DRIVER);
        $dispatcher = $this->makeAccount(User::ROLE_DISPATCHER);

        $this->actingAs($driver)->get('/desk')
            ->assertForbidden()
            ->assertSee('cleared for this page');

        $this->actingAs($driver)->get('/desk/accounts')->assertForbidden();

        $this->actingAs($dispatcher)->get('/desk')->assertOk();
    }

    public function test_dispatchers_cannot_open_the_run_sheet(): void
    {
        $dispatcher = $this->makeAccount(User::ROLE_DISPATCHER);

        $this->actingAs($dispatcher)->get('/run-sheet')->assertForbidden();
    }

    public function test_home_sends_each_role_to_its_own_page(): void
    {
        $driver = $this->makeAccount(User::ROLE_DRIVER);
        $dispatcher = $this->makeAccount(User::ROLE_DISPATCHER);

        $this->actingAs($driver)->get('/home')->assertRedirect(route('runsheet.index'));
        $this->actingAs($dispatcher)->get('/home')->assertRedirect(route('desk.index'));
    }

    // ---------- Feature 4: email verification ----------

    public function test_unconfirmed_accounts_are_sent_to_the_check_your_inbox_page(): void
    {
        $account = $this->makeAccount(User::ROLE_DRIVER, verified: false);

        $this->actingAs($account)->get('/run-sheet')->assertRedirect(route('verification.notice'));
    }

    public function test_the_emailed_link_confirms_the_account(): void
    {
        $account = $this->makeAccount(User::ROLE_DRIVER, verified: false);

        $link = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $account->id,
            'hash' => sha1($account->email),
        ]);

        $this->actingAs($account)->get($link)->assertRedirect(route('home'));

        $this->assertTrue($account->fresh()->hasVerifiedEmail());
    }

    // ---------- Feature 5: password reset ----------

    public function test_the_reset_link_is_emailed_and_sets_a_new_password(): void
    {
        Notification::fake();
        $account = $this->makeAccount();

        $this->post('/forgot-password', ['email' => $account->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($account, ChooseNewPassword::class, function (ChooseNewPassword $notification) use ($account) {
            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $account->email,
                'password' => 'BrandNew#5678',
                'password_confirmation' => 'BrandNew#5678',
            ])->assertRedirect(route('login'));

            return true;
        });

        $this->assertTrue(Hash::check('BrandNew#5678', $account->fresh()->password));
    }

    // ---------- Driver and dispatcher use cases ----------

    public function test_a_dispatcher_can_send_a_task_to_a_driver(): void
    {
        $dispatcher = $this->makeAccount(User::ROLE_DISPATCHER);
        $driver = $this->makeAccount(User::ROLE_DRIVER);

        $this->actingAs($dispatcher)->post(route('desk.tasks.store'), [
            'item_name' => 'Box of cables',
            'item_quantity' => 2,
            'item_notes' => 'Fragile',
            'recipient_name' => 'Ms. Reyes',
            'destination' => '5 Harbor Road',
            'zone' => 'Old Harbor',
            'driver_id' => $driver->id,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('delivery_tasks', [
            'item_name' => 'Box of cables',
            'driver_id' => $driver->id,
            'status' => DeliveryTask::SENT,
        ]);
    }

    public function test_a_driver_can_confirm_their_own_task_but_not_someone_elses(): void
    {
        $driver = $this->makeAccount();
        $other = $this->makeAccount();
        $mine = $this->makeTask($driver, 'Riverside', 'Mine');
        $theirs = $this->makeTask($other, 'Riverside', 'Theirs');

        $this->actingAs($driver)->post(route('runsheet.confirm', $theirs))->assertForbidden();

        $this->actingAs($driver)->post(route('runsheet.confirm', $mine))->assertSessionHas('status');

        $this->assertSame(DeliveryTask::RECEIVED, $mine->fresh()->status);
        $this->assertNotNull($mine->fresh()->received_at);
        $this->assertSame(DeliveryTask::SENT, $theirs->fresh()->status);
    }

    public function test_a_dispatcher_can_put_a_driver_on_hold_and_reinstate_them(): void
    {
        $dispatcher = $this->makeAccount(User::ROLE_DISPATCHER);
        $driver = $this->makeAccount();

        $this->actingAs($dispatcher)->patch(route('desk.accounts.hold', $driver))->assertSessionHas('status');
        $this->assertTrue($driver->fresh()->isOnHold());

        $this->actingAs($dispatcher)->patch(route('desk.accounts.reinstate', $driver))->assertSessionHas('status');
        $this->assertFalse($driver->fresh()->isOnHold());
    }

    public function test_an_account_on_hold_cannot_sign_in(): void
    {
        $driver = $this->makeAccount(User::ROLE_DRIVER, onHold: true);

        $this->post('/login', ['email' => $driver->email, 'password' => self::PASSWORD])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_an_account_put_on_hold_while_signed_in_is_signed_out(): void
    {
        $driver = $this->makeAccount(User::ROLE_DRIVER, onHold: true);

        $this->actingAs($driver)->get('/run-sheet')->assertRedirect(route('login'));
    }
}
