<?php

namespace Database\Seeders;

use App\Models\DeliveryTask;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Feature 3: the restricted role can only be created here (or straight in the database).
        $this->makeAccount('Alex Rivera', 'dispatcher@runsheet.test', 'Dispatch@123', User::ROLE_DISPATCHER);

        // Example driver: the default role every new account gets.
        $driver = $this->makeAccount('Jamie Cruz', 'driver@runsheet.test', 'Driver@123', User::ROLE_DRIVER);

        if (DeliveryTask::count() > 0) {
            return; // already seeded, don't pile up duplicate tasks
        }

        // item, quantity, notes, recipient, destination, zone, hours ago, already confirmed?
        $samples = [
            ['Printer paper, A4', 5, 'Leave at the front desk.', 'Ms. Lopez', '12 Rowing Lane', 'Riverside', 1, false],
            ['Office chair', 1, 'Heavy box. Ask for help at the loading dock.', 'Mr. Tan', '88 Willow Court', 'Riverside', 26, true],
            ['Birthday cake box', 1, 'Keep it level. Deliver before 5 PM.', 'Ate Marites', '4 Summit Road', 'Hillcrest', 2, false],
            ['First-aid kits', 3, 'Keep dry.', 'Harbor Clinic', '9 Dockside Avenue', 'Old Harbor', 3, false],
            ['Signed contract envelope', 2, 'Hand to the receptionist only.', 'Mr. Reyes', 'Gate 3, Runway Road', 'Airport Strip', 30, true],
        ];

        foreach ($samples as [$item, $quantity, $notes, $recipient, $destination, $zone, $hoursAgo, $confirmed]) {
            DeliveryTask::forceCreate([
                'item_name' => $item,
                'item_quantity' => $quantity,
                'item_notes' => $notes,
                'recipient_name' => $recipient,
                'destination' => $destination,
                'zone' => $zone,
                'driver_id' => $driver->id,
                'status' => $confirmed ? DeliveryTask::RECEIVED : DeliveryTask::SENT,
                'received_at' => $confirmed ? now()->subHours($hoursAgo)->addMinutes(20) : null,
                'created_at' => now()->subHours($hoursAgo),
                'updated_at' => now()->subHours($hoursAgo),
            ]);
        }
    }

    /**
     * forceCreate() skips the $fillable list, which is how the seeder can set the role.
     * The example accounts start with a confirmed email so they can sign in straight away.
     */
    private function makeAccount(string $name, string $email, string $password, string $role): User
    {
        return User::where('email', $email)->first() ?? User::forceCreate([
            'name' => $name,
            'email' => $email,
            'password' => $password, // the "hashed" cast on the User model turns this into a hash
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }
}
