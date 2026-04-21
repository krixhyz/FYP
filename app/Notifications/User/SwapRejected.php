<?php

namespace App\Notifications\User;

use App\Models\SwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;


class SwapRejected extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $swapRequest;

    public function __construct(SwapRequest $swapRequest)
    {
        $this->swapRequest = $swapRequest;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type'            => 'swapReject',
            'swap_request_id' => $this->swapRequest->id,
            'product_id'      => $this->swapRequest->product_id,
            'message'         => 'Your swap request for "' . $this->swapRequest->product->title . '" has been rejected.',
            'redirect_url'    => route('dashboard'),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type'            => 'swapReject',
            'swap_request_id' => $this->swapRequest->id,
            'product_title'   => $this->swapRequest->product->title,
            'message'         => 'Your swap request for "' . $this->swapRequest->product->title . '" has been rejected.',
            'redirect_url'    => route('dashboard'),
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->swapRequest->requester_id);
    }

    public function broadcastAs()
    {
        return 'swap.rejected';
    }
}
