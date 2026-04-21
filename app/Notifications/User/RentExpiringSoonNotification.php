<?php

namespace App\Notifications\User;

use App\Models\RentedRentals;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;

class RentExpiringSoonNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected $rental;

    public function __construct(RentedRentals $rental)
    {
        $this->rental = $rental;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Rental is Expiring Tomorrow!')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your rental for "' . $this->rental->product->title . '" will expire tomorrow (' . $this->rental->end_date->format('M d, Y') . ').')
            ->line('Please make sure to return the item by the specified date to avoid any issues.')
            ->action('View Rental Details', route('rental.show', $this->rental->id))
            ->line('Thank you for renting with us!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'rentExpiringSoon',
            'rental_id' => $this->rental->id,
            'product_title' => $this->rental->product->title,
            'end_date' => $this->rental->end_date->toDateString(),
            'message' => 'Your rental "' . $this->rental->product->title . '" expires tomorrow.',
            'redirect_url' => route('rental.show', $this->rental->id),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'rentExpiringSoon',
            'rental_id' => $this->rental->id,
            'product_title' => optional($this->rental->product)->title,
            'end_date' => $this->rental->end_date->toDateString(),
            'message' => 'Your rental "' . optional($this->rental->product)->title . '" expires tomorrow.',
            'redirect_url' => route('rental.show', $this->rental->id),
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->rental->renter_id);
    }

    /**
     * Get the name for the broadcast event.
     */
    public function broadcastAs()
    {
        return 'rental.expiring_soon';
    }
}
