<?php

namespace App\Notifications\User;

use App\Models\SwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class SwapPaymentReceivedNotification extends Notification
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
                'title' => 'Payment Received for Your Swap',
                'message' => 'Payment received for your swap with ' . $this->swapRequest->requester->name . '. Awaiting confirmation from both parties.',
                'swap_request_id' => $this->swapRequest->id,
                'action_url' => route('swap.confirmation', $this->swapRequest->id),
                'icon' => 'success',
            ]
        );
    }
}
