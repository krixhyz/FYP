<?php

namespace App\Notifications\User;

use App\Models\SwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class SwapConfirmationExpiredNotification extends Notification
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
                'title' => 'Swap Confirmation Expired',
                'message' => 'The confirmation period for your swap has expired. If you did not confirm receipt, funds have been released back to both parties.',
                'swap_request_id' => $this->swapRequest->id,
                'action_url' => route('swap.request.show', $this->swapRequest->id),
                'icon' => 'error',
            ]
        );
    }
}
