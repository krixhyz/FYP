<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProfileVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly bool $autoVerified = false)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->autoVerified
            ? 'Your profile has been auto-verified'
            : 'Your profile has been verified';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hi ' . ($notifiable->name ?? 'there') . ',')
            ->line('Your profile is now verified. You can publish listings with direct approval.')
            ->line('Thank you for contributing responsibly to the platform.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'profile_verified',
            'auto_verified' => $this->autoVerified,
            'message' => $this->autoVerified
                ? 'Your profile was auto-verified based on your activity.'
                : 'Your profile was verified by an admin.',
        ];
    }
}