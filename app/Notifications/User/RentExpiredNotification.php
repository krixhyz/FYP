<?php

namespace App\Notifications\User;

use App\Models\RentedRentals;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;

class RentExpiredNotification extends Notification implements ShouldBroadcastNow
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
            ->subject('Your Rental Has Expired')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your rental for "' . $this->rental->product->title . '" has now expired (ended on ' . $this->rental->end_date->format('M d, Y') . ').')
            ->line('If you haven\'t already, please return the item to the owner as soon as possible.')
            ->action('View Rental Details', route('rental.show', $this->rental->id))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'rentExpired',
            'rental_id' => $this->rental->id,
            'product_title' => $this->rental->product->title,
            'end_date' => $this->rental->end_date->toDateString(),
            'message' => 'Your rental "' . $this->rental->product->title . '" has expired.',
            'redirect_url' => route('rental.show', $this->rental->id),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'rentExpired',
            'rental_id' => $this->rental->id,
            'product_title' => optional($this->rental->product)->title,
            'end_date' => $this->rental->end_date->toDateString(),
            'message' => 'Your rental "' . optional($this->rental->product)->title . '" has expired.',
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
        return 'rental.expired';
    }
}
