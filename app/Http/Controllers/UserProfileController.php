<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\RentedRentals;
use App\Models\Review;
use App\Models\Swap;
use App\Models\User;

class UserProfileController extends Controller
{
    public function show(User $user)
    {
        $activeListingsQuery = Product::where('user_id', $user->id)
            ->where('status', 'available')
            ->latest();

        $activeListingsCount = (clone $activeListingsQuery)->count();
        $activeListings = (clone $activeListingsQuery)->take(6)->get();

        $completedSales = Order::whereHas('product', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('transaction_type', 'buy')
            ->where('status', 'completed')
            ->count();

        $completedRentals = RentedRentals::where('owner_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $completedSwaps = Swap::where(function ($query) use ($user) {
            $query->where('owner_a_id', $user->id)
                ->orWhere('owner_b_id', $user->id);
        })
            ->where('status', 'completed')
            ->count();

        $completedDeals = $completedSales + $completedRentals + $completedSwaps;

        $reviewsBaseQuery = Review::where('reviewee_id', $user->id);
        $reviewsCount = (clone $reviewsBaseQuery)->count();
        $avgRating = (clone $reviewsBaseQuery)->avg('rating');

        $recentReviews = Review::where('reviewee_id', $user->id)
            ->with('reviewer')
            ->latest()
            ->take(5)
            ->get();

        $location = Product::where('user_id', $user->id)
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->latest('updated_at')
            ->value('location');

        return view('users.show', compact(
            'user',
            'activeListings',
            'activeListingsCount',
            'completedDeals',
            'completedSales',
            'completedRentals',
            'completedSwaps',
            'reviewsCount',
            'avgRating',
            'recentReviews',
            'location'
        ));
    }
}