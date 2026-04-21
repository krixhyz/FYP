<?php

namespace App\Notifications\User;

use App\Models\RentalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;

class RentalRejectedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected $rentalRequest;

    public function __construct(RentalRequest $rentalRequest)
    {
        $this->rentalRequest = $rentalRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Rental Request Has Been Rejected')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Unfortunately, your rental request for "' . $this->rentalRequest->product->title . '" has been declined by the owner.')
            ->line('The item is still available for other rentals or purchases.')
            ->action('Browse More Items', route('products.index'))
            ->line('Thank you for understanding.');
    }

    public function toArray($notifiable)
    {
        return [
            'type'              => 'rentalReject',
            'rental_request_id' => $this->rentalRequest->id,
            'product_title'     => $this->rentalRequest->product->title,
            'message'           => 'Your rental request has been rejected by the owner.',
            'redirect_url'      => route('products.index'),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type'              => 'rentalReject',
            'rental_request_id' => $this->rentalRequest->id,
            'product_title'     => optional($this->rentalRequest->product)->title,
            'message'           => 'Your rental request has been rejected.',
            'redirect_url'      => route('products.index'),
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->rentalRequest->renter_id);
    }

    public function broadcastAs()
    {
        return 'rental.rejected';
    }
}
