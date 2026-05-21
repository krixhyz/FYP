<?php

namespace App\Notifications\Admin;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductApprovedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(protected Product $product) {}

    public function via($notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your product listing has been approved.')
            ->line('Product: ' . $this->product->title)
            ->action('View Listing', route('products.show', $this->product->id))
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type'            => 'productApproved',
            'product_id'      => $this->product->id,
            'product_title'   => $this->product->title,
            'approval_status' => 'APPROVED',
            'message'         => "Your listing \"{$this->product->title}\" has been approved and is now live.",
            'redirect_url'    => route('products.show', $this->product->id),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function broadcastAs(): string
    {
        return 'product.approved';
    }
}
