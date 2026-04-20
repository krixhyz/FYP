<?php

namespace App\Notifications\User;

use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;

class DisputeFiledNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public Dispute $dispute,
        public string $transactionType,
        public int $transactionId,
        public string $actorName,
        public bool $isNew = true,
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        $verb = $this->isNew ? 'filed' : 'updated';

        return [
            'type' => 'dispute',
            'dispute_id' => $this->dispute->id,
            'transaction_type' => $this->transactionType,
            'transaction_id' => $this->transactionId,
            'message' => "{$this->actorName} has {$verb} a dispute for this {$this->transactionType} transaction. You can submit your own proof.",
            'redirect_url' => route('dispute.create', [
                'type' => $this->transactionType,
                'id' => $this->transactionId,
            ]),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function broadcastAs(): string
    {
        return 'dispute.filed';
    }
}
