<?php

namespace App\Observers;

use App\Models\Dispute;
use App\Services\UserVerificationService;

class DisputeObserver
{
    protected $verificationService;

    public function __construct(UserVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Handle the Dispute "created" event.
     */
    public function created(Dispute $dispute): void
    {
        $this->evaluateSellerFromDispute($dispute);
    }

    /**
     * Handle the Dispute "updated" event.
     */
    public function updated(Dispute $dispute): void
    {
        $this->evaluateSellerFromDispute($dispute);
    }

    /**
     * Evaluate the seller after a dispute is created/updated.
     */
    protected function evaluateSellerFromDispute(Dispute $dispute): void
    {
        // If seller_id is set, use that
        if ($dispute->seller_id) {
            $seller = $dispute->seller()->first();
        } else {
            // Fallback: determine seller from the transaction
            $seller = null;

            if ($dispute->transaction_type === 'order' && $dispute->order) {
                // Seller is the product owner
                $seller = $dispute->order->product->user;
            } elseif ($dispute->transaction_type === 'rental' && $dispute->rentalRequest) {
                // Seller is the rental owner
                $seller = $dispute->rentalRequest->owner;
            } elseif ($dispute->transaction_type === 'swap' && $dispute->swap) {
                // Seller is the swap owner
                $seller = $dispute->swap->owner;
            }
        }

        if (!$seller) {
            return;
        }

        // Trigger auto-evaluation
        $this->verificationService->evaluateUser($seller);
    }
}
