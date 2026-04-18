<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Product;
use App\Services\InventoryReservationService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function store(Request $request, $productId, InventoryReservationService $inventory)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ], [
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity must be at least 1.',
        ]);

        $requestedQty = (int) $validated['quantity'];

        return DB::transaction(function () use ($productId, $requestedQty, $inventory) {
            // Lock row for update to avoid race conditions
            $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();
            $availableQty = (int) $product->quantity;

            if ($availableQty < 1) {
                return back()->with('error', 'This item is out of stock.');
            }

            if ($requestedQty > $availableQty) {
                return back()->with('error', 'Requested quantity exceeds available stock.');
            }

            if ($product->user_id === Auth::id()) {
                return back()->with('error', 'You cannot buy your own product.');
            }

            if (!in_array('sell', $product->type ?? [])) {
                return back()->with('error', 'This item is not available for purchase.');
            }

            try {
                $inventory->ensurePurchasableQuantity($product, $requestedQty, now());
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }

            return redirect()
                ->route('order.checkout.product', ['product' => $product->id, 'quantity' => $requestedQty])
                ->with('success', 'Review and complete payment to place this order.');
        });
    }

    public function checkoutProduct(Request $request, Product $product, InventoryReservationService $inventory)
    {
        if ($product->user_id === Auth::id()) {
            return redirect()->route('products.show', $product->id)->with('error', 'You cannot buy your own product.');
        }

        if (!in_array('sell', $product->type ?? [])) {
            return redirect()->route('products.show', $product->id)->with('error', 'This item is not available for purchase.');
        }

        $availableQty = (int) $product->quantity;
        if ($availableQty < 1) {
            return redirect()->route('products.show', $product->id)->with('error', 'This item is out of stock.');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $availableQty,
        ], [
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => "Quantity cannot exceed available stock ({$availableQty}).",
        ]);

        try {
            $inventory->ensurePurchasableQuantity($product, (int) $validated['quantity'], now());
        } catch (\RuntimeException $e) {
            return redirect()->route('products.show', $product->id)->with('error', $e->getMessage());
        }

        $quantity = (int) $validated['quantity'];

        return view('orders.checkout', compact('product', 'quantity'));
    }

    public function checkout($orderId)
    {
        $order = Order::with('product')->findOrFail($orderId);

        if ($order->buyer_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if ($order->status !== 'pending') {
            return redirect()->route('products.myPurchases')->with('info', 'This order is no longer awaiting checkout.');
        }

        return view('orders.checkout', compact('order'));
    }

    public function cancel(Order $order)
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }

        $order->status = 'cancelled';
        $order->save();

        return redirect()->route('products.myPurchases')->with('success', 'Order cancelled successfully.');
    }

    public function cancelFromCheckout(Order $order)
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status === 'pending') {
            $order->status = 'cancelled';
            $order->save();
        }

        return redirect()->route('products.index')->with('info', 'Unpaid checkout was cancelled.');
    }

    public function confirm(Request $request, $orderId)
    {
        $order = Order::with('product')->where('id', $orderId)->where('buyer_id', Auth::id())->firstOrFail();

        if ($order->status !== 'pending') {
            return redirect()->route('products.myPurchases')->with('info', 'Order already processed.');
        }

        // Recalculate in case product price changed (keep original if stored)
        $unit = $order->unit_price ?? ($order->product->price ?? 0);
        $qty  = $order->quantity ?? 1;
        $total = $unit * $qty;

        // Persist if columns exist
        $order->unit_price = $unit;
        $order->total_price = $total;
        $order->status = 'completed';
        $order->save();

        return redirect()->route('products.myPurchases')
            ->with('success', 'Purchase completed successfully.');
    }

    // Seller views - incoming orders
    public function sellerIncoming()
    {
        $orders = Order::where('seller_id', Auth::id())
            ->with(['buyer', 'product'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('orders.seller-incoming', compact('orders'));
    }

    public function sellerOrderDetail(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $order->load(['buyer', 'product', 'payment']);

        return view('orders.seller-detail', compact('order'));
    }
}
