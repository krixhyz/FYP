<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProfileVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $isAutomatic;

    public function __construct($isAutomatic = true)
    {
        $this->isAutomatic = $isAutomatic;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $verificationMethod = $this->isAutomatic ? 'automatically verified' : 'verified';

        return (new MailMessage)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Congratulations! Your seller profile has been ' . $verificationMethod . '.')
            ->line('As a verified seller, your new product listings will be approved instantly!')
            ->action('View Your Profile', route('dashboard'))
            ->line('Thank you for being a trusted member of our community!');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Your profile has been verified.',
            'verification_method' => $this->isAutomatic ? 'automatic' : 'manual',
        ];
    }
}
