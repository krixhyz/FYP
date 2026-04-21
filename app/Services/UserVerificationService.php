<?php

namespace App\Services;

use App\Models\User\User;
use Illuminate\Support\Facades\DB;

class UserVerificationService
{
    /**
     * Evaluate a user's profile and update verification status based on performance metrics.
     * 
     * @param User $user The user to evaluate
     * @return string The new profile status (VERIFIED or UNVERIFIED)
     */
    public function evaluateUser(User $user): string
    {
        // Only auto-evaluate users with at least 5 listed products
        $productCount = $user->products()->count();
        if ($productCount < 5) {
            return $user->profile_status;
        }

        $averageRating = $this->calculateAverageRating($user);
        $disputeCount = $this->countDisputes($user);

        // Auto-verify: average rating >= 4 AND disputes < 2
        if ($averageRating >= 4 && $disputeCount < 2) {
            $user->update(['profile_status' => 'VERIFIED']);
            
            // Send notification about auto-verification
            $user->notify(new \App\Notifications\ProfileVerifiedNotification(true));
            
            return 'VERIFIED';
        }

        // Keep unverified: average rating < 3 OR disputes >= 2
        if ($averageRating < 3 || $disputeCount >= 2) {
            $user->update(['profile_status' => 'UNVERIFIED']);
            return 'UNVERIFIED';
        }

        // Middle band (3 <= rating < 4, disputes < 2): keep current status
        return $user->profile_status;
    }

    /**
     * Calculate the average rating for a user based on reviews of their products.
     * 
     * @param User $user
     * @return float The average rating (0-5 scale)
     */
    public function calculateAverageRating(User $user): float
    {
        $averageRating = DB::table('reviews')
            ->join('products', 'reviews.product_id', '=', 'products.id')
            ->where('products.user_id', $user->id)
            ->avg('reviews.rating');

        return $averageRating ?? 0;
    }

    /**
     * Count valid disputes for a user (open, in_review, resolved).
     * Dismissed disputes are excluded from the count.
     * 
     * @param User $user
     * @return int The count of valid disputes
     */
    public function countDisputes(User $user): int
    {
        return DB::table('disputes')
            ->where('seller_id', $user->id)
            ->whereIn('status', ['open', 'in_review', 'resolved'])
            ->count();
    }

    /**
     * Check if a user is verified.
     * 
     * @param User $user
     * @return bool
     */
    public function isVerified(User $user): bool
    {
        return $user->profile_status === 'VERIFIED';
    }

    /**
     * Manually verify a user (admin action).
     * 
     * @param User $user
     * @return bool
     */
    public function manuallyVerify(User $user): bool
    {
        $wasVerified = $user->profile_status === 'VERIFIED';
        $result = $user->update(['profile_status' => 'VERIFIED']);
        
        // Send notification only if user was not already verified
        if (!$wasVerified && $result) {
            $user->notify(new \App\Notifications\ProfileVerifiedNotification(false));
        }
        
        return $result;
    }

    /**
     * Manually revoke verification from a user (admin action).
     * 
     * @param User $user
     * @return bool
     */
    public function revokeVerification(User $user): bool
    {
        return $user->update(['profile_status' => 'UNVERIFIED']);
    }
}
