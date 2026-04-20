<?php

namespace App\Mail;

use App\Models\SwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SwapOrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $swapRequest;
    public $role; // 'owner' or 'requester'

    public function __construct(SwapRequest $swapRequest, string $role = 'owner')
    {
        $this->swapRequest = $swapRequest;
        $this->role = $role;
    }

    public function envelope(): Envelope
    {
        $subject = $this->role === 'owner' 
            ? 'New Swap Offer: ' . ($this->swapRequest->offeredProduct->title ?? 'Item')
            : 'Your Swap Request: ' . $this->swapRequest->product->title;
            
        return new Envelope(
            subject: $subject,
            from: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.swap-order-created',
            with: [
                'swapRequest' => $this->swapRequest,
                'role' => $this->role,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
