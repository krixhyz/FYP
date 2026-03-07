<?php

namespace App\Notifications;

use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;

class DisputeStatusUpdated extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(public Dispute $dispute) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        $statusLabel = ucfirst(str_replace('_', ' ', $this->dispute->status));

        return [
            'type'         => 'dispute',
            'message'      => "Your dispute #{$this->dispute->id} has been updated to: {$statusLabel}.",
            'dispute_id'   => $this->dispute->id,
            'status'       => $this->dispute->status,
            'admin_notes'  => $this->dispute->admin_notes,
            'redirect_url' => route('dispute.my'),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        $statusLabel = ucfirst(str_replace('_', ' ', $this->dispute->status));

        return new BroadcastMessage([
            'type'         => 'dispute',
            'dispute_id'   => $this->dispute->id,
            'status'       => $this->dispute->status,
            'message'      => "Your dispute #{$this->dispute->id} has been updated to: {$statusLabel}.",
            'redirect_url' => route('dispute.my'),
        ]);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('App.Models.User.' . $this->dispute->reporter_id);
    }

    public function broadcastAs(): string
    {
        return 'dispute.updated';
    }
}
