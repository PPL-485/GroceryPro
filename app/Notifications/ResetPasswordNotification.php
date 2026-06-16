<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $broker = config('auth.defaults.passwords');
        $expiresIn = config("auth.passwords.{$broker}.expire", 60);

        return (new MailMessage)
            ->subject('Reset Your GroceryPro Password')
            ->view('emails.reset-password', [
                'user' => $notifiable,
                'resetUrl' => $resetUrl,
                'expiresIn' => $expiresIn,
            ]);
    }
}
