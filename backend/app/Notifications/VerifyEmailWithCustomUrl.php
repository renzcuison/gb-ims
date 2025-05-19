<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailWithCustomUrl extends VerifyEmail
{
    protected function verificationUrl($notifiable)
    {
        
        $frontendUrl = config('app.frontend_url') . '/verify-email';

       
        $verificationUrl = url(route('verification.verify', [
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ], false));

        
        return $frontendUrl . '?url=' . urlencode($verificationUrl) . '&signature=' . sha1($verificationUrl);
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Verify Your Email Address or you will not gain access')
            ->line('*PLEASE LOGIN WITH USER ACCOUNT BEFORE CLICKING VERIFY*')
            ->action('Verify Email', $this->verificationUrl($notifiable))
            ->line('If you did not create an account, no further action is required.');
    }
}