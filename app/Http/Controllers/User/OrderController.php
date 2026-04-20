<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Product;
use App\Services\InventoryReservationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
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

            $checkoutUrl = URL::temporarySignedRoute(
                'order.checkout.product',
                now()->addMinutes(15),
                [
                    'product' => $product->id,
                    'quantity' => $requestedQty,
                    'buyer' => Auth::id(),
                ]
            );

            return redirect($checkoutUrl)
                ->with('success', 'Review and complete payment to place this order.');
        });
    }

    public function checkoutProduct(Request $request, Product $product, InventoryReservationService $inventory)
    {
        if (!$request->hasValidSignature() || (int) $request->query('buyer') !== (int) Auth::id()) {
            return redirect()->route('products.index')
                ->with('error', 'This checkout link is invalid, expired, or belongs to another user.');
        }

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

        $quantity = (int) $validated['quantity'];

        try {
            $order = DB::transaction(function () use ($product, $quantity, $inventory) {
                $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();
                $inventory->ensurePurchasableQuantity($lockedProduct, $quantity, now());

                $reservationMinutes = (int) config('esewa.reservation_minutes');
                $reservedUntil = now()->addMinutes(max($reservationMinutes, 5));
                $unitPrice = (float) ($lockedProduct->price ?? 0);
                $subtotal = $unitPrice * $quantity;
                $serviceFee = round($subtotal * 0.03, 2);
                $totalAmount = $subtotal + $serviceFee;

                $pendingOrder = Order::where('buyer_id', Auth::id())
                    ->where('product_id', $lockedProduct->id)
                    ->where('status', 'pending')
                    ->where('reserved_until', '>', now())
                    ->lockForUpdate()
                    ->latest('id')
                    ->first();

                if ($pendingOrder) {
                    $pendingOrder->fill([
                        'seller_id' => $lockedProduct->user_id,
                        'transaction_type' => 'buy',
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $subtotal,
                        'subtotal' => $subtotal,
                        'service_fee' => $serviceFee,
                        'total_amount' => $totalAmount,
                        'payment_status' => 'pending',
                        'reserved_until' => $reservedUntil,
                    ]);
                    $pendingOrder->save();

                    return $pendingOrder;
                }

                return Order::create([
                    'buyer_id' => Auth::id(),
                    'seller_id' => $lockedProduct->user_id,
                    'product_id' => $lockedProduct->id,
                    'transaction_type' => 'buy',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $subtotal,
                    'subtotal' => $subtotal,
                    'service_fee' => $serviceFee,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'reserved_until' => $reservedUntil,
                ]);
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('products.show', $product->id)->with('error', $e->getMessage());
        }

        return view('orders.checkout', compact('order'));
    }

    public function checkout($orderId)
    {
        $order = Order::with('product')->findOrFail($orderId);
        $this->authorize('buyerAccess', $order);

        if ($order->status !== 'pending') {
            return redirect()->route('products.myPurchases')->with('info', 'This order is no longer awaiting checkout.');
        }

        return view('orders.checkout', compact('order'));
    }

    public function cancel(Order $order)
    {
        $this->authorize('buyerAccess', $order);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }

        $order->status = 'cancelled';
        $order->save();

        return redirect()->route('products.myPurchases')->with('success', 'Order cancelled successfully.');
    }

    public function cancelFromCheckout(Order $order)
    {
        $this->authorize('buyerAccess', $order);

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
        $this->authorize('sellerAccess', $order);

        $order->load(['buyer', 'product', 'payment']);

        return view('orders.seller-detail', compact('order'));
    }
}
