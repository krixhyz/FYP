<?php

namespace App\Notifications\User;

use App\Models\SwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class SwapConfirmationRequestNotification extends Notification
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
        return new DatabaseMessage(
            data: [
                'title' => 'Action Required: Confirm Swap Receipt',
                'message' => 'Please confirm that you have received the items from your swap with ' . 
                    ($notifiable->id === $this->swapRequest->owner_id 
                        ? $this->swapRequest->requester->name 
                        : $this->swapRequest->owner->name) . 
                    '. Both parties must confirm for completion.',
                'swap_request_id' => $this->swapRequest->id,
                'action_url' => route('swap.confirmation', $this->swapRequest->id),
                'icon' => 'warning',
            ]
        );
    }
}
