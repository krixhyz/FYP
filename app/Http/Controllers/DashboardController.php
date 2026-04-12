<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RentedRentals;
use App\Models\Order;
use App\Models\RentalRequest;
use App\Models\Swap;
use App\Models\SwapRequest;
use App\Models\Dispute;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Seller metrics
        $sellerProducts = $user->products()->with(['orders' => function($q){
            $q->where('transaction_type','buy');
        }])->get();

        $totalUnitsListed = $sellerProducts->sum('quantity');
        $activeUnits = $sellerProducts->where('status','available')->sum('quantity');

        $sellerOrders = $sellerProducts->flatMap->orders;
        $completedSellerOrders = $sellerOrders->where('status', 'completed');
        $unitsSold = $completedSellerOrders->sum(fn($o) => $o->quantity ?? 1);
        $salesRevenue = $completedSellerOrders->sum(fn($o) => ($o->unit_price ?? $o->product->price ?? 0) * ($o->quantity ?? 1));

        $activeRentalsOwner = RentedRentals::where('owner_id',$user->id)->where('status','active')->count();
        $rentalRevenueOwner = RentedRentals::where('owner_id',$user->id)->where('payment_status','paid')
            ->sum('total_amount');
        $flaggedListings = $sellerProducts->where('flagged', true)->count();
        $pendingRentalRequestsIncoming = RentalRequest::where('owner_id', $user->id)
            ->where('status', 'requested')
            ->count();
        $pendingSwapRequestsIncoming = SwapRequest::where('owner_id', $user->id)
            ->whereIn('status', ['requested', 'countered'])
            ->count();

        $sellerMetrics = [
            'total_units_listed' => $totalUnitsListed,
            'active_units' => $activeUnits,
            'units_sold' => $unitsSold,
            'sales_revenue' => $salesRevenue,
            'active_rentals_owner' => $activeRentalsOwner,
            'rental_revenue_owner' => $rentalRevenueOwner,
            'published_listings' => $sellerProducts->count(),
            'flagged_listings' => $flaggedListings,
            'pending_rental_requests_incoming' => $pendingRentalRequestsIncoming,
            'pending_swap_requests_incoming' => $pendingSwapRequestsIncoming,
        ];

        // Buyer metrics
        $buyerOrders = Order::with('product')
            ->where('buyer_id',$user->id)
            ->where('transaction_type','buy')
            ->get();
        $completedBuyerOrders = $buyerOrders->where('status', 'completed');
        $pendingBuyerOrders = $buyerOrders->where('status', 'pending');

        $purchasesCount = $completedBuyerOrders->count();
        $purchasedUnits = $completedBuyerOrders->sum(fn($o)=> $o->quantity ?? 1);
        $totalSpent = $completedBuyerOrders->sum(fn($o)=> ($o->unit_price ?? $o->product->price ?? 0) * ($o->quantity ?? 1));

        $activeRentalsRenter = RentedRentals::where('renter_id',$user->id)->where('status','active')->count();
        $swapCount = Swap::where(function($q) use ($user){
            $q->where('owner_a_id',$user->id)->orWhere('owner_b_id',$user->id);
        })->where('status','completed')->count();
        $openDisputes = Dispute::where('reporter_id', $user->id)
            ->whereIn('status', ['open', 'in_review'])
            ->count();
        $unreadNotifications = $user->unreadNotifications()->count();

        $buyerMetrics = [
            'purchases_count' => $purchasesCount,
            'purchased_units' => $purchasedUnits,
            'total_spent' => $totalSpent,
            'active_rentals_renter' => $activeRentalsRenter,
            'completed_swaps' => $swapCount,
            'pending_orders' => $pendingBuyerOrders->count(),
            'open_disputes' => $openDisputes,
            'unread_notifications' => $unreadNotifications,
        ];

        $workspaceMetrics = [
            'inbox_items' => $pendingRentalRequestsIncoming + $pendingSwapRequestsIncoming + $unreadNotifications,
            'sell_through_rate' => $totalUnitsListed > 0 ? round(($unitsSold / $totalUnitsListed) * 100, 1) : 0,
            'total_earnings' => $salesRevenue + $rentalRevenueOwner,
        ];

        // Eco Score Metrics
        $ecoMetrics = [
            'total_eco_score' => $user->getTotalEcoScore(),
            'current_eco_level' => $user->getCurrentEcoLevel(),
            'eco_stats' => $user->getEcoStats(),
        ];

        return view('dashboard', compact('sellerMetrics','buyerMetrics','workspaceMetrics','ecoMetrics','user'));
    }
}
