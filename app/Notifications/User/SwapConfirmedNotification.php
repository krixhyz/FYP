<?php

namespace App\Notifications\User;

use App\Models\SwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class SwapConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private SwapRequest $swapRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $otherPartyName = $notifiable->id === $this->swapRequest->owner_id 
            ? $this->swapRequest->requester->name 
            : $this->swapRequest->owner->name;

        return new DatabaseMessage(
            data: [
                'title' => 'Swap Receipt Confirmed!',
                'message' => $otherPartyName . ' confirmed receipt of their item. Please confirm receipt of your item to complete the swap.',
                'swap_request_id' => $this->swapRequest->id,
                'action_url' => route('swap.confirmation', $this->swapRequest->id),
                'icon' => 'info',
            ]
        );
    }
}
