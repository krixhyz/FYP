<?php

namespace App\Http\Controllers\User;

use App\Models\User\Wishlist;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlistItems = Wishlist::where('user_id', Auth::id())
            ->with(['product.user'])
            ->latest()
            ->get()
            ->filter(fn($w) => $w->product !== null);

        return view('wishlist.index', compact('wishlistItems'));
    }

    public function toggle(Product $product)
    {
        $existing = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = 'Removed from wishlist.';
            $saved = false;
        } else {
            Wishlist::create([
                'user_id'    => Auth::id(),
                'product_id' => $product->id,
            ]);
            $message = 'Added to wishlist!';
            $saved = true;
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'saved' => $saved,
                'product_id' => $product->id,
            ]);
        }

        return back()->with('success', $message);
    }
}
