<?php

namespace App\Notifications;

use App\Models\Rental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentalApprovedNotification extends Notification
{
    use Queueable;

    protected $rental;

    public function __construct(Rental $rental)
    {
        $this->rental = $rental;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // send email + save to DB
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Rental Request Has Been Approved!')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your rental request for "' . $this->rental->product->name . '" has been approved by the owner.')
            ->action('View Rental Details', route('rental.checkout', $this->rental->id))
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable)
    {
        return [
            'rental_id' => $this->rental->id,
            'product_name' => $this->rental->product->name,
            'message' => 'Your rental request has been approved by the owner.',
        ];
    }
}
