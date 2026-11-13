<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Feature 5: the email that holds the one-time "choose a new password" link.
 * It is sent by User::sendPasswordResetNotification(), which Laravel's password broker calls
 * after it has stored a hashed token for the account.
 */
class ChooseNewPassword extends Notification
{
    public function __construct(public string $token)
    {
    }

    /** Email only. */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        // The route "password.reset" takes the token in the path and the email in the query string.
        $link = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Choose a new RunSheet password')
            ->greeting('Password reset')
            ->line('We got a request to reset the password for your RunSheet account.')
            ->action('Choose a new password', $link)
            ->line("This link works once and expires in {$minutes} minutes.")
            ->line('If this wasn’t you, nothing changes. Just ignore this email.')
            ->salutation('The RunSheet dispatch team');
    }
}
