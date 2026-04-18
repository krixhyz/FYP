<?php

namespace App\Http\Controllers\User;

use App\Models\User\CartItem;
use App\Models\Product;
use App\Models\Order;
use App\Services\InventoryReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Auth::user()->cartItems()->with('product')->get();
        return view('cart.index', compact('cartItems'));
    }

    public function getCount()
    {
        $count = Auth::user()->cartItems()->sum('quantity');
        return response()->json(['count' => $count]);
    }

    public function store(Request $request, $productId, InventoryReservationService $inventory)
    {
        $product = Product::findOrFail($productId);
        $maxQty = max(1, (int) $product->quantity);

        $validated = $request->validate([
            'quantity' => 'nullable|integer|min:1|max:' . $maxQty,
        ], [
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => "Quantity cannot exceed available stock ({$maxQty}).",
        ]);

        $quantity = intval($validated['quantity'] ?? 1);

        if ($product->user_id === Auth::id()) {
            $message = 'Cannot add your own product to cart.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return back()->with('error', $message);
        }

        if (!in_array('sell', $product->type ?? [])) {
            $message = 'This item is not available for purchase.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        try {
            $inventory->ensurePurchasableQuantity($product, $quantity, now());
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return back()->with('error', $e->getMessage());
        }

        // Check if already in cart
        $existing = Auth::user()->cartItems()->where('product_id', $productId)->first();
        if ($existing) {
            $newQty = $existing->quantity + $quantity;
            try {
                $inventory->ensurePurchasableQuantity($product, (int) $newQty, now());
            } catch (\RuntimeException $e) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
                }
                return back()->with('error', $e->getMessage());
            }
            $existing->quantity = $newQty;
            $existing->save();
        } else {
            Auth::user()->cartItems()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'type' => 'buy',
            ]);
        }

        $message = "Added {$quantity}x {$product->title} to cart";
        $cartCount = (int) Auth::user()->cartItems()->sum('quantity');
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'cartCount' => $cartCount]);
        }
        return back()->with('success', $message);
    }

    public function update(Request $request, $id, InventoryReservationService $inventory)
    {
        $cartItem = Auth::user()->cartItems()->findOrFail($id);

        $product = $cartItem->product;
        $maxQty = max(1, (int) ($product->quantity ?? 1));

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $maxQty,
        ], [
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => "Quantity cannot exceed available stock ({$maxQty}).",
        ]);

        try {
            $inventory->ensurePurchasableQuantity($product, (int) $validated['quantity'], now());
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return back()->with('error', $e->getMessage());
        }

        $cartItem->quantity = $validated['quantity'];
        $cartItem->save();

        $message = "Updated {$product->title} quantity to {$validated['quantity']}";
        $cartCount = (int) Auth::user()->cartItems()->sum('quantity');
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'cartCount' => $cartCount]);
        }
        return back()->with('success', $message);
    }

    public function destroy(Request $request, $id)
    {
        $cartItem = Auth::user()->cartItems()->findOrFail($id);
        $productTitle = $cartItem->product->title;
        $cartItem->delete();

        $message = "Removed {$productTitle} from cart";
        $cartCount = (int) Auth::user()->cartItems()->sum('quantity');
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'cartCount' => $cartCount]);
        }
        return back()->with('success', $message);
    }

    public function checkout()
    {
        $cartItems = Auth::user()->cartItems()->with('product')->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty.');
        }
        return view('cart.checkout', compact('cartItems'));
    }

    public function placeFromCart(Request $request, InventoryReservationService $inventory)
    {
        $cartItems = Auth::user()->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty.');
        }

        try {
            DB::transaction(function () use ($cartItems, $inventory) {
                foreach ($cartItems as $item) {
                    $product = Product::lockForUpdate()->find($item->product_id);

                    if (!$product) {
                        throw new \RuntimeException('Product no longer exists.');
                    }

                    try {
                        $inventory->ensurePurchasableQuantity($product, (int) $item->quantity, now());
                    } catch (\RuntimeException $e) {
                        throw new \RuntimeException(($product->title ?? 'Product') . ': ' . $e->getMessage());
                    }

                    $unitPrice = $product->price ?? 0;
                    $totalPrice = $unitPrice * $item->quantity;

                    Order::create([
                        'buyer_id' => Auth::id(),
                        'product_id' => $product->id,
                        'transaction_type' => 'buy',
                        'quantity' => $item->quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'status' => 'completed',
                    ]);

                    $inventory->consumeProductQuantity($product, (int) $item->quantity, 'sold');

                    $item->delete();
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.checkout')->with('error', $e->getMessage());
        }

        return redirect()->route('products.myPurchases')->with('success', 'Orders placed successfully.');
    }
}
