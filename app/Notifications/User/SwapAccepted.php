<?php

namespace App\Notifications\User;

use App\Models\SwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;

class SwapAccepted extends Notification implements ShouldBroadcastNow
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
        $redirectUrl = $this->resolveRedirectUrl($notifiable);

        return [
            'type'               => 'swapAccept',
            'swap_request_id'    => $this->swapRequest->id,
            'product_id'         => $this->swapRequest->product_id,
            'offered_product_id' => $this->swapRequest->offered_product_id,
            'message'            => 'Your swap request for "' . $this->swapRequest->product->title . '" has been accepted!',
            'redirect_url'       => $redirectUrl,
        ];
    }

    public function toBroadcast($notifiable)
    {
        $redirectUrl = $this->resolveRedirectUrl($notifiable);

        return new BroadcastMessage([
            'type'                  => 'swapAccept',
            'swap_request_id'       => $this->swapRequest->id,
            'product_title'         => $this->swapRequest->product->title,
            'offered_product_title' => $this->swapRequest->offeredProduct?->title,
            'message'               => 'Your swap request for "' . $this->swapRequest->product->title . '" has been accepted!',
            'redirect_url'          => $redirectUrl,
        ]);
    }

    private function resolveRedirectUrl($notifiable): string
    {
        $payerId = match ($this->swapRequest->money_direction) {
            'requester_offers_cash' => $this->swapRequest->requester_id,
            'owner_asks_cash' => $this->swapRequest->owner_id,
            default => null,
        };

        if ($payerId && (int) $notifiable->id === (int) $payerId && $this->swapRequest->status === 'awaiting_payment') {
            return route('swap.checkout', $this->swapRequest->id);
        }

        return route('swap.request.show', $this->swapRequest->id);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->swapRequest->requester_id);
    }

    public function broadcastAs()
    {
        return 'swap.accepted';
    }
}
