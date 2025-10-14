<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
   public function index()
{
    $cartItems = CartItem::where('user_id', Auth::id())
        ->with('product')
        ->get()
        ->filter(function ($item) {
            // Keep only items still available
            return $item->product && $item->product->status !== 'sold';
        })
        ->values();

    // Clean up unavailable items automatically
    CartItem::where('user_id', Auth::id())
        ->whereHas('product', fn($q) => $q->where('status', 'sold'))
        ->delete();

    // Calculate total
    $total = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

    return view('cart.index', compact('cartItems', 'total'));
}

    public function store(Request $request, $productId)
    {
        $request->validate([
            'type' => 'nullable|string',
            'rent_duration' => 'nullable|integer|min:1',
            'quantity' => 'nullable|integer|min:1'
        ]);

        $product = Product::findOrFail($productId);

        CartItem::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $product->id,
            ],
            [
                'type' => $request->type ?? 'buy',
                'quantity' => $request->quantity ?? 1,
                'rent_duration' => $request->rent_duration,
            ]
        );

        return redirect()->route('cart.index')->with('success', 'Product added to cart!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = CartItem::where('user_id', Auth::id())->findOrFail($id);
        $cartItem->update(['quantity' => $request->quantity]);

        return back()->with('success', 'Cart updated.');
    }

    public function destroy($id)
    {
        $item = CartItem::where('user_id', Auth::id())->findOrFail($id);
        $item->delete();

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    public function checkout()
    {
        $cartItems = CartItem::where('user_id', Auth::id())->with('product')->get();
        $total = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

        return view('orders.checkout', compact('cartItems', 'total'));
    }


    public function placeFromCart()
{
    $cartItems = CartItem::where('user_id', Auth::id())->with('product')->get();

    foreach ($cartItems as $item) {
        // Skip sold/unavailable products
        if (!$item->product || $item->product->status === 'sold') continue;

        // Create order record (if you have an Order model)
        \App\Models\Order::create([
            'buyer_id' => Auth::id(),
            'product_id' => $item->product_id,
            'transaction_type' => 'buy',
            'status' => 'pending',
        ]);

        // Mark product as sold (if only 1 quantity available)
        $item->product->update(['status' => 'sold']);
    }

    // Clear the cart
    CartItem::where('user_id', Auth::id())->delete();

    return redirect()->route('products.myPurchases')->with('success', 'Order placed successfully!');
}
}
