<?php

namespace App\Mail;

use App\Models\RentalRequest;
use App\Models\RentedRentals;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RentalOrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public RentalRequest $rentalRequest,
        public RentedRentals $rentedRental,
        public string $role = 'owner'
    ) {
    }

    public function envelope(): Envelope
    {
        $productTitle = $this->rentalRequest->product->title ?? 'Rental Item';
        $subject = $this->role === 'owner'
            ? 'New Rental Order: ' . $productTitle
            : 'Your Rental Order: ' . $productTitle;

        return new Envelope(
            subject: $subject,
            from: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rental-order-created',
            with: [
                'rentalRequest' => $this->rentalRequest,
                'rentedRental' => $this->rentedRental,
                'role' => $this->role,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
