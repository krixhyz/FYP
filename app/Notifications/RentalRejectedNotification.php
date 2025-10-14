<?php

namespace App\Notifications;

use App\Models\Rental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentalRejectedNotification extends Notification
{
    use Queueable;

    protected $rental;

    public function __construct(Rental $rental)
    {
        $this->rental = $rental;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // Send as email + save to DB
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Rental Request Has Been Rejected')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Unfortunately, your rental request for "' . $this->rental->product->name . '" has been declined by the owner.')
            ->line('The item is still available for other rentals or purchases.')
            ->action('Browse More Items', route('index'))
            ->line('Thank you for understanding.');
    }

    public function toArray($notifiable)
    {
        return [
            'rental_id' => $this->rental->id,
            'product_name' => $this->rental->product->name,
            'message' => 'Your rental request has been rejected by the owner.',
        ];
    }
}
