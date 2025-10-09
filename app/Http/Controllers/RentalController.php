<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalController extends Controller
{
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        // Prevent renting your own product
        if ($product->user_id == Auth::id()) {
            return back()->with('error', 'You cannot rent your own item.');
        }

        $request->validate([
            'rent_type' => 'required|in:hourly,daily',
            'duration' => 'required|integer|min:1',
        ]);

        $fare = $product->rent_fare;
        $total = $fare * $request->duration;

        Rental::create([
            'product_id' => $product->id,
            'owner_id' => $product->user_id,
            'renter_id' => Auth::id(),
            'rent_fare' => $fare,
            'rent_deposit' => $product->rent_deposit,
            'rent_type' => $request->rent_type,
            'duration' => $request->duration,
            'total_amount' => $total,
            'rental_status' => 'requested',
        ]);

        return back()->with('success', 'Rental request submitted successfully!');
    }
}

