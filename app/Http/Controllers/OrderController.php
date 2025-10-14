<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Product;

class OrderController extends Controller
{
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        if ($product->user_id === Auth::id()) {
            return back()->with('error', 'You cannot buy your own product.');
        }

        // Prevent double purchase
        if ($product->status === 'sold') {
            return back()->with('error', 'This product is already sold.');
        }

        // Create order
        $order = Order::create([
            'buyer_id' => Auth::id(),
            'product_id' => $product->id,
            'transaction_type' => 'buy',
            'status' => 'pending',
        ]);

        // Update product status
        $product->update(['status' => 'sold']);

        // Redirect to checkout
        return redirect()->route('order.checkout', $order->id)
                         ->with('success', 'Order placed successfully! Proceed to checkout.');
    }

    public function checkout($orderId)
    {
        $order = Order::with('product')->findOrFail($orderId);

        if ($order->buyer_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('orders.checkout', compact('order'));
    }
}
