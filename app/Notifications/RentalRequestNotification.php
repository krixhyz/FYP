<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;
use App\Models\RentalRequest;

class RentalRequestNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $rentalRequest;

    public function __construct(RentalRequest $rentalRequest)
    {
        $this->rentalRequest = $rentalRequest;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type'              => 'rental',
            'rental_request_id' => $this->rentalRequest->id,
            'product_id'        => $this->rentalRequest->product_id,
            'renter_id'         => $this->rentalRequest->renter_id,
            'message'           => 'You have a new rental request for "' . optional($this->rentalRequest->product)->title . '" from ' . optional($this->rentalRequest->renter)->name . '.',
            'redirect_url'      => route('rental.review', $this->rentalRequest->id),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type'              => 'rental',
            'rental_request_id' => $this->rentalRequest->id,
            'product_title'     => optional($this->rentalRequest->product)->title,
            'renter_name'       => optional($this->rentalRequest->renter)->name,
            'message'           => 'You have a new rental request for "' . optional($this->rentalRequest->product)->title . '" from ' . optional($this->rentalRequest->renter)->name . '.',
            'redirect_url'      => route('rental.review', $this->rentalRequest->id),
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->rentalRequest->owner_id);
    }

    public function broadcastAs()
    {
        return 'rental.requested';
    }
}
