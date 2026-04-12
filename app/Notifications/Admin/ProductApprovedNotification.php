<?php

namespace App\Notifications\Admin;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your product listing has been approved.')
            ->line('Product: ' . $this->product->title)
            ->action('View Listing', route('products.show', $this->product->id))
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable)
    {
        return [
            'product_id' => $this->product->id,
            'product_title' => $this->product->title,
            'approval_status' => 'APPROVED',
            'message' => 'Your product listing has been approved.',
        ];
    }
}
