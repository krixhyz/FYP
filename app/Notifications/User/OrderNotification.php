<?php

namespace App\Notifications\User;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        $buyerName    = $this->order->buyer_name ?: 'A buyer';
        $productTitle = $this->order->product->title ?? 'Product';
        $quantity     = $this->order->quantity > 1
            ? "{$this->order->quantity} units"
            : '1 unit';

        return [
            'type'          => 'order',
            'order_id'      => $this->order->id,
            'product_id'    => $this->order->product_id,
            'buyer_id'      => $this->order->buyer_id,
            'buyer_name'    => $this->order->buyer_name,
            'buyer_phone'   => $this->order->buyer_phone,
            'buyer_email'   => $this->order->buyer_email,
            'product_title' => $productTitle,
            'quantity'      => $this->order->quantity,
            'message'       => "New order! {$buyerName} ordered {$quantity} of \"{$productTitle}\".",
            'redirect_url'  => '/my-orders',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function broadcastAs(): string
    {
        return 'order.received';
    }
}
