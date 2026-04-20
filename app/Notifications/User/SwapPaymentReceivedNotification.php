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
        $redirectUrl = route('swap.mySwaps', [
            'tab' => 'pending',
            'swap_request_id' => $this->swapRequest->id,
        ]);

        return new DatabaseMessage(
            data: [
                'type' => 'swapPaymentReceived',
                'title' => 'Swap Payment Received',
                'message' => 'Payment is complete for your swap request. Open My Swaps to continue with dispatch and confirmation.',
                'swap_request_id' => $this->swapRequest->id,
                'redirect_url' => $redirectUrl,
                'action_url' => $redirectUrl,
                'icon' => 'success',
            ]
        );
    }
}
