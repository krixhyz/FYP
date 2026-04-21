<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderNotification extends Notification
{
    use Queueable;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $buyerName = $this->order->buyer_name ?: 'A buyer';
        $productTitle = $this->order->product->title ?? 'Product';
        $quantity = $this->order->quantity > 1 ? "{$this->order->quantity} units" : '1 unit';
        
        return [
            'type' => 'order',
            'order_id' => $this->order->id,
            'product_id' => $this->order->product_id,
            'buyer_id' => $this->order->buyer_id,
            'buyer_name' => $this->order->buyer_name,
            'buyer_phone' => $this->order->buyer_phone,
            'buyer_email' => $this->order->buyer_email,
            'product_title' => $productTitle,
            'quantity' => $this->order->quantity,
            'message' => "New order! {$buyerName} ordered {$quantity} of {$productTitle}",
            'redirect_url' => '/my-orders',
        ];
    }
}
