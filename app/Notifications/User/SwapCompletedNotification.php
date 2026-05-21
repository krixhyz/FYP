<?php

namespace App\Notifications\User;

use App\Models\SwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class SwapCompletedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(private SwapRequest $swapRequest) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'swapCompleted',
            'swap_request_id' => $this->swapRequest->id,
            'message'         => 'Your swap has been completed. Funds have been transferred to your wallet.',
            'redirect_url'    => route('swap.request.show', $this->swapRequest->id),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function broadcastAs(): string
    {
        return 'swap.completed';
    }
}
