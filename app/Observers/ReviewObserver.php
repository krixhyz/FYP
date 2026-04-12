<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\Product;
use App\Services\UserVerificationService;

class ReviewObserver
{
    protected $verificationService;

    public function __construct(UserVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        $this->evaluateSellerFromReview($review);
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        $this->evaluateSellerFromReview($review);
    }

    /**
     * Evaluate the seller (the reviewee) after a review is created/updated.
     */
    protected function evaluateSellerFromReview(Review $review): void
    {
        // The reviewee is the person being reviewed (the seller)
        $seller = $review->reviewee;
        if (!$seller) {
            return;
        }

        // Trigger auto-evaluation
        $this->verificationService->evaluateUser($seller);
    }
}
