<?php

namespace App\Notifications\User;

use App\Models\SwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;

class SwapRequested extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $swapRequest;

    public function __construct(SwapRequest $swapRequest)
    {
        $this->swapRequest = $swapRequest;
    }

    // Channels: database + broadcast
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type'             => 'swap',
            'swap_request_id'  => $this->swapRequest->id,
            'requester_id'     => $this->swapRequest->requester_id,
            'product_id'       => $this->swapRequest->product_id,
            'offered_product_id' => $this->swapRequest->offered_product_id,
            'offered_amount'   => $this->swapRequest->offered_amount,
            'message'          => 'You have a new swap request for "' . $this->swapRequest->product->title . '" from ' . $this->swapRequest->requester->name . '.',
            'redirect_url'     => route('swap.request.show', $this->swapRequest->id),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type'                  => 'swap',
            'swap_request_id'       => $this->swapRequest->id,
            'requester_name'        => $this->swapRequest->requester->name,
            'product_title'         => $this->swapRequest->product->title,
            'offered_product_title' => $this->swapRequest->offeredProduct?->title,
            'offered_amount'        => $this->swapRequest->offered_amount,
            'message'               => 'You have a new swap request for "' . $this->swapRequest->product->title . '" from ' . $this->swapRequest->requester->name . '.',
            'redirect_url'          => route('swap.request.show', $this->swapRequest->id),
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->swapRequest->owner_id);
    }

    public function broadcastAs()
    {
        return 'swap.requested';
    }
}
