<?php

namespace App\Notifications\Admin;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $product;
    protected $reason;

    public function __construct(Product $product, $reason = '')
    {
        $this->product = $product;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Unfortunately, your product listing has been rejected.')
            ->line('Product: ' . $this->product->title)
            ->when($this->reason, function ($msg) {
                return $msg->line('Reason: ' . $this->reason);
            })
            ->line('Please review our guidelines and try again.')
            ->action('View Guidelines', route('home'))
            ->line('Thank you for your understanding!');
    }

    public function toArray($notifiable)
    {
        return [
            'product_id' => $this->product->id,
            'product_title' => $this->product->title,
            'approval_status' => 'REJECTED',
            'reason' => $this->reason,
            'message' => 'Your product listing has been rejected.',
        ];
    }
}
