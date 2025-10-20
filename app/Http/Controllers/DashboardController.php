<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RentedRentals;

class DashboardController extends Controller
{
    public function index()
    {

        $user = Auth::user();

        // Active Listings
        $activeListings = $user->products()->where('status', 'available')->count();

        // Revenue this month
        $thisMonthRevenue = RentedRentals::where('owner_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->sum('rent_fare');

        // Total Rentals
        $totalRentals = RentedRentals::where('owner_id', $user->id)->count();

        // Rating
        $rating = $user->rating ?? 4.9;

        return view('dashboard', compact('activeListings', 'thisMonthRevenue', 'totalRentals', 'rating'));
    }
}
