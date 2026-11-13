<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Feature 4: the email that holds the confirmation link for a new account.
 * It is sent by User::sendEmailVerificationNotification(), which Laravel calls
 * when the Registered event fires (and when the person presses "Send the link again").
 */
class ConfirmEmail extends Notification
{
    /** Email only. */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $minutes = (int) config('auth.verification.expire', 60);

        // A temporary SIGNED link. The route "verification.verify" needs the account id and a hash
        // of the email; Laravel rejects the link if the signature is wrong or the time has run out.
        $link = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes($minutes),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        return (new MailMessage)
            ->subject('Confirm your RunSheet email')
            ->greeting('Welcome to RunSheet!')
            ->line('Tap the button below to confirm this email address and unlock your account.')
            ->action('Confirm my email', $link)
            ->line("The link works for {$minutes} minutes. If you didn’t sign up, you can ignore this message.")
            ->salutation('The RunSheet dispatch team');
    }
}
