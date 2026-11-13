<?php

namespace App\Models;

use App\Notifications\ChooseNewPassword;
use App\Notifications\ConfirmEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * One account in RunSheet. ("User" is just Laravel's default model name.)
 * Every account is either a driver or a dispatcher; see the two role constants.
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /** Default role: every new account gets this one. */
    public const ROLE_DRIVER = 'driver';

    /** Restricted role: it can't be picked on the register form. It is set in the seeder or the database. */
    public const ROLE_DISPATCHER = 'dispatcher';

    /**
     * Fields that may be mass-assigned (for example from a form).
     * "role" is deliberately NOT listed, so a role sent from a form is ignored.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'suspended_at' => 'datetime',
            'password' => 'hashed', // assigning a plain password stores its hash
        ];
    }

    public function isDispatcher(): bool
    {
        return $this->role === self::ROLE_DISPATCHER;
    }

    public function isDriver(): bool
    {
        return $this->role === self::ROLE_DRIVER;
    }

    /** True while a dispatcher has this account on hold. */
    public function isOnHold(): bool
    {
        return $this->suspended_at !== null;
    }

    /** The delivery tasks sent to this driver. */
    public function tasks(): HasMany
    {
        return $this->hasMany(DeliveryTask::class, 'driver_id');
    }

    /** How many tasks this driver has not confirmed yet (the badge in the top bar). */
    public function waitingCount(): int
    {
        return $this->tasks()->where('status', DeliveryTask::SENT)->count();
    }

    /** Feature 4: send the confirmation email with our own wording. */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new ConfirmEmail);
    }

    /** Feature 5: send the password reset email with our own wording. */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ChooseNewPassword($token));
    }
}
