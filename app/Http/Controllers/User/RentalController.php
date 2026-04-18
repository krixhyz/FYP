<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\RentalRequest;
use App\Models\RentedRentals;
use App\Services\InventoryReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Notifications\User\RentalRequestNotification;
use App\Notifications\User\RentalRejectedNotification;
use App\Notifications\User\RentalApprovedNotification;
use App\Http\Controllers\Controller;

class RentalController extends Controller
{
    /**
     * Show rental form for a product.
     */
    public function create(Product $product)
    {
        if ($product->user_id == Auth::id()) {
            return back()->with('error', 'You cannot rent your own item.');
        }

        return view('rental.create', compact('product'));
    }

    public function store(Request $request, Product $product)
    {
        $rentalConfig = $product->rentals()->first();

        // 1. Validate inputs
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'duration' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
        ]);

        if (!$rentalConfig || !$rentalConfig->available_from || !$rentalConfig->available_duration) {
            return back()->withInput()->withErrors([
                'start_date' => 'This item does not have a valid rental availability window.',
            ]);
        }

        $ownerStartDate = Carbon::parse($rentalConfig->available_from)->startOfDay();
        $ownerEndDate = $ownerStartDate->copy()->addDays(max(((int) $rentalConfig->available_duration) - 1, 0));
        $requestedStartDate = Carbon::parse($request->start_date)->startOfDay();
        $requestedEndDate = Carbon::parse($request->end_date)->startOfDay();

        if ($requestedStartDate->lt($ownerStartDate) || $requestedStartDate->gt($ownerEndDate)) {
            return back()->withInput()->withErrors([
                'start_date' => 'Start date must be within the owner\'s available rental range.',
            ]);
        }

        if ($requestedEndDate->lt($requestedStartDate) || $requestedEndDate->gt($ownerEndDate)) {
            return back()->withInput()->withErrors([
                'end_date' => 'End date must be on or after start date and within the owner\'s available rental range.',
            ]);
        }

        $calculatedDuration = $requestedStartDate->diffInDays($requestedEndDate) + 1;

        // 2. Prevent self-renting
        if ($product->user_id == Auth::id()) {
            return back()->with('error', 'You cannot rent your own item.');
        }

        // 3. Prevent duplicate pending requests
        $conflict = RentalRequest::where('product_id', $product->id)
            ->where('renter_id', Auth::id())
            ->whereIn('status', ['requested', 'approved'])
            ->exists();

        if ($conflict) {
            return back()->with('error', 'You already have a pending request for this item.');
        }

        // Prevent renting when there is only 1 unit and it's already rented out
        if ($product->quantity <= 1) {
            $hasActive = RentedRentals::where('product_id', $product->id)
                ->where('status', 'active')
                ->exists();
            if ($hasActive) {
                return back()->with('error', 'This item is currently rented out.');
            }
        }

        // Block if no stock (single-unit logic)
        if ($product->quantity < 1) {
            return back()->with('error', 'No available stock to rent.');
        }

        // 4. Create rental request
        $rentalRequest = RentalRequest::create([
            'rental_id' => $rentalConfig->id,
            'product_id' => $product->id,
            'owner_id' => $product->user_id,
            'renter_id' => Auth::id(),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration' => $calculatedDuration,
            'total_amount' => $request->total_amount,
            'rent_deposit' => $request->rent_deposit ?? 0,
            'status' => 'requested',
        ]);

        // 5. Notify the owner
        $owner = $product->owner ?? $product->user;
        if ($owner) {
            $owner->notify(new RentalRequestNotification($rentalRequest));
        }

        // 6. Redirect with confirmation
        return redirect()->route('products.index')->with('success', 'Rental request submitted! The owner will review your request soon.');
    }

    /**
     * Checkout screen for renter.
     */
    public function checkout(RentalRequest $rentalRequest, InventoryReservationService $inventory)
    {
        if ($rentalRequest->renter_id != Auth::id()) {
            abort(403);
        }

        if ($rentalRequest->status !== 'approved') {
            return redirect()->route('products.index')->with('error', 'Rental request is not approved yet.');
        }

        if ($rentalRequest->reserved_until && $rentalRequest->reserved_until->isPast()) {
            $inventory->releaseRentalReservation($rentalRequest);
            return redirect()->route('products.index')->with('error', 'Rental reservation expired. Please request again.');
        }

        return view('rental.checkout', compact('rentalRequest'));
    }

    /**
     * Payment page for approved rental.
     */
    public function payment(RentalRequest $rentalRequest, InventoryReservationService $inventory)
    {
        if ($rentalRequest->renter_id != Auth::id()) {
            abort(403);
        }

        if ($rentalRequest->status !== 'approved') {
            return redirect()->route('products.index')->with('error', 'Rental request is not approved yet.');
        }

        if ($rentalRequest->reserved_until && $rentalRequest->reserved_until->isPast()) {
            $inventory->releaseRentalReservation($rentalRequest);
            return redirect()->route('products.index')->with('error', 'Rental reservation expired. Please request again.');
        }

        return view('rental.payment', compact('rentalRequest'));
    }

    /**
     * Review rental request for owner.
     */
    public function review($requestId)
    {
        $rental = RentalRequest::with(['product', 'renter'])
            ->findOrFail($requestId);

        // Ensure only the owner can view this request
        if ($rental->owner_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Optional: mark related notification as read
        $user = Auth::user();
        $user->unreadNotifications()
            ->where('data->rental_request_id', $rental->id)
            ->update(['read_at' => now()]);

        // Return the view with a variable name matching your Blade file
        $rentalRequest = $rental;
        return view('rental.review', compact('rentalRequest'));
    }


    /**
     * Approve rental request → move to rented_rentals.
     */

    public function approveRequest(RentalRequest $rentalRequest, InventoryReservationService $inventory)
    {
        // Ensure only owner can approve
        if ($rentalRequest->owner_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $inventory->reserveRentalRequest($rentalRequest, (int) config('esewa.reservation_minutes'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $rentalRequest->renter->notify(new RentalApprovedNotification($rentalRequest));

        return redirect()->route('dashboard')
            ->with('success', 'Rental approved. The renter can now proceed to payment.');
    }

    /**
     * Reject a rental request.
     */
    public function reject(RentalRequest $rentalRequest)
    {
        if ($rentalRequest->owner_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if ($rentalRequest->status !== 'requested') {
            return back()->with('error', 'This request has already been processed.');
        }

        // Notify renter
        $rentalRequest->renter->notify(new RentalRejectedNotification($rentalRequest));

        // Keep record for history / notification resolution
        $rentalRequest->status = 'rejected';
        $rentalRequest->save();

        return redirect()->route('dashboard') // ENSURE route
                         ->with('info', 'Rental request rejected.');
    }

    /**
     * Renter cancels their own pending rental request.
     */
    public function cancelRequest(RentalRequest $rentalRequest, InventoryReservationService $inventory)
    {
        if ($rentalRequest->renter_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($rentalRequest->status, ['requested', 'approved'], true)) {
            return back()->with('error', 'Only pending or approved rental requests can be cancelled.');
        }

        if ($rentalRequest->status === 'approved') {
            $inventory->releaseRentalReservation($rentalRequest);
        } else {
            $rentalRequest->status = 'cancelled';
            $rentalRequest->save();
        }

        return redirect()->route('products.myPurchases')->with('success', 'Rental request cancelled.');
    }

    public function returnRental(RentedRentals $rentedRental)
    {
        // Only owner can mark returned
        if ($rentedRental->owner_id !== Auth::id()) {
            abort(403);
        }
        if ($rentedRental->status !== 'active') {
            return back()->with('error', 'Rental already processed.');
        }

        DB::transaction(function () use ($rentedRental) {
            $rentedRental->status = 'completed';
            $rentedRental->returned_at = now();
            $rentedRental->save();

            $product = $rentedRental->product;
            if ($product) {
                $product->quantity += 1;
                // Restore availability if was rented and now has stock
                if ($product->status === 'rented' && $product->quantity > 0) {
                    $product->status = 'available';
                }
                $product->save();
            }

            // Re-open rental window so the same item can be rented again.
            $rentalConfig = $product?->rentals()->first();
            if ($rentalConfig) {
                $rentalConfig->status = 'available';
                $rentalConfig->available_from = now()->toDateString();
                $rentalConfig->save();
            }
        });

        return back()->with('success', 'Rental marked as returned and stock updated.');
    }

    /**
     * Renter requests that a rental be marked as returned.
     */
    public function requestReturn(RentedRentals $rentedRental)
    {
        if ($rentedRental->renter_id !== Auth::id()) {
            abort(403);
        }

        if ($rentedRental->status !== 'active') {
            return back()->with('error', 'This rental is no longer active.');
        }

        if ($rentedRental->return_requested_at) {
            return back()->with('info', 'Return has already been requested.');
        }

        $rentedRental->return_requested_at = now();
        $rentedRental->save();

        return back()->with('success', 'Return requested. The owner will confirm after inspection.');
    }

    /**
     * View user's rentals (as a renter).
     */
    public function myRentals()
    {
        $rentals = RentedRentals::with(['product', 'owner', 'deposit'])
            ->where('renter_id', Auth::id())
            ->latest()
            ->get();

        $rentedItems = RentedRentals::with(['product', 'renter', 'deposit'])
            ->where('owner_id', Auth::id())
            ->where('status', 'active')
            ->latest()
            ->get();

        $ownerCompletedItems = RentedRentals::with(['product', 'renter', 'deposit'])
            ->where('owner_id', Auth::id())
            ->whereIn('status', ['completed', 'returned'])
            ->latest()
            ->get();

        $incomingRequests = RentalRequest::where('owner_id', Auth::id())
            ->where('status', 'requested')
            ->with(['product', 'renter'])
            ->latest()
            ->get();

        return view('rental.my_rentals', compact('rentals', 'rentedItems', 'ownerCompletedItems', 'incomingRequests'));
    }

    /**
     * Show rental details (for renter to view their active/past rentals).
     */
    public function show(RentedRentals $rental)
    {
        // Ensure user is either the renter or owner
        if ($rental->renter_id !== Auth::id() && $rental->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $rental->load(['product', 'renter', 'owner']);

        return view('rental.show', compact('rental'));
    }
}
