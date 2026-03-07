<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rental;
use App\Models\RentedRentals;
use App\Models\Wishlist;
use App\Models\RecentlyViewed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\RentalRequest;



class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->get();

        $wishlistedIds = Auth::check()
            ? Wishlist::where('user_id', Auth::id())->pluck('product_id')->toArray()
            : [];

        $recentlyViewed = collect();
        if (Auth::check()) {
            $recentlyViewed = RecentlyViewed::where('user_id', Auth::id())
                ->with('product.user')
                ->orderByDesc('viewed_at')
                ->limit(6)
                ->get()
                ->filter(fn($r) => $r->product && $r->product->status === 'available' && $r->product->user_id !== Auth::id())
                ->values();
        }

        return view('products.index', compact('products', 'wishlistedIds', 'recentlyViewed'));
    }

    public function create()
{
    $action = route('products.store'); // form submission URL
    $method = 'POST';                  // HTTP method

    return view('products.create', compact('action', 'method'));
}


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|in:electronics,clothing,furniture,general',
            'price' => 'nullable|numeric|min:0',
            'listing_type' => 'required|array|min:1',
            'listing_type.*' => 'in:sell,rent,swap',
            'images' => 'nullable|array|max:6',
            'images.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:4096',
            'rent_deposit' => 'required_if:listing_type.*,rent|nullable|numeric|min:0',
            'rent_fare' => 'required_if:listing_type.*,rent|nullable|numeric|min:0',
            'rent_type' => 'required_if:listing_type.*,rent|nullable|in:hourly,daily',
            'start_date' => 'required_if:listing_type.*,rent|nullable|date|after_or_equal:today',
            'end_date' => 'required_if:listing_type.*,rent|nullable|date|after_or_equal:start_date',
            'rent_duration' => 'required_if:listing_type.*,rent|nullable|integer|min:1',
            'quantity' => 'required|integer|min:1', // NEW
        ]);

        // Handle multiple image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                try {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $imagePaths[] = $file->storeAs('uploads/products', $filename, 'public');
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors(['images' => 'Failed to upload image: ' . $e->getMessage()]);
                }
            }
        }
        $coverImage = $imagePaths[0] ?? null;

        // Create product entry
        $product = Product::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'price' => $request->price,
            'quantity' => $request->quantity, // NEW
            'type' => $request->listing_type,
            'image' => $coverImage,
            'images' => $imagePaths ?: null,
            'status' => 'available',
        ]);

        // If rent selected, create a rental record
        if (in_array('rent', $request->listing_type)) {
            $rental = Rental::create([
    'product_id'=>$product->id,
    'owner_id'=>Auth::id(),
    'rent_fare'=>$request->rent_fare,
    'rent_deposit'=>$request->rent_deposit,
    'available_from'=>$request->available_from,
    
    'available_duration'=>$request->rent_duration,
    'status'=>'available'
]);
        }
        

        return redirect()->route('dashboard')->with('success', 'Listing added successfully!'); // ENSURE route
    }




  public function edit($id)
{
    $product = Product::where('user_id', Auth::id())->with('rentals')->findOrFail($id);
    $action = route('products.update', $product->id); // form submission URL
    $method = 'PUT';                                 // HTTP method

    return view('products.edit', compact('product', 'action', 'method'));
}


public function update(Request $request, $id)
{
    $product = Product::where('user_id', Auth::id())->findOrFail($id);

    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'category' => 'required|string|in:electronics,clothing,furniture,general',
        'price' => 'nullable|numeric|min:0',
        'listing_type' => 'required|array|min:1',
        'listing_type.*' => 'in:sell,rent,swap',
        'images' => 'nullable|array|max:6',
        'images.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:4096',
        'remove_images' => 'nullable|array',
        'remove_images.*' => 'string',
        'rent_deposit' => 'required_if:listing_type.*,rent|nullable|numeric|min:0',
        'rent_fare' => 'required_if:listing_type.*,rent|nullable|numeric|min:0',
        'start_date' => 'required_if:listing_type.*,rent|nullable|date|after_or_equal:today',
        'end_date' => 'required_if:listing_type.*,rent|nullable|date|after_or_equal:start_date',
        'rent_duration' => 'required_if:listing_type.*,rent|nullable|integer|min:1',
        'quantity' => 'required|integer|min:1', // NEW
    ]);

    // Build existing images list, removing any checked for deletion
    $existingImages = $product->images ?? [];
    $removeImages = $request->input('remove_images', []);
    foreach ($removeImages as $removePath) {
        Storage::disk('public')->delete($removePath);
        $existingImages = array_values(array_filter($existingImages, fn($p) => $p !== $removePath));
    }

    // Handle new image uploads
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $file) {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $existingImages[] = $file->storeAs('uploads/products', $filename, 'public');
        }
    }
    $coverImage = $existingImages[0] ?? $product->image;

    // Update product
    $product->update([
        'title' => $request->title,
        'description' => $request->description,
        'category' => $request->category,
        'price' => $request->price,
        'quantity' => $request->quantity, // NEW
        'type' => $request->listing_type,
        'image' => $coverImage,
        'images' => $existingImages ?: null,
    ]);

    // Handle rent details
    if (in_array('rent', $request->listing_type)) {
        $rentals = Rental::updateOrCreate(
            ['product_id' => $product->id],
            [
                'owner_id' => Auth::id(),
                'rent_fare' => $request->rent_fare,
                'rent_deposit' => $request->rent_deposit,
                'available_duration' => $request->rent_duration,
                'status' => 'available',
            ]
        );
    } else {
        // If rent was removed, delete its rental record if it exists
        Rental::where('product_id', $product->id)->delete();
    }

    return redirect()->route('products.myListings')->with('success', 'Listing updated successfully!');
}


   public function myListings()
{
    $user = Auth::user();

    // Eager-load orders for accurate sold calculations
    $products = $user->products()->with('orders')->get(); // UPDATED
    $pendingRequests = RentalRequest::with(['product', 'renter'])
        ->where('owner_id', $user->id)
        ->where('status', 'requested')
        ->latest()
        ->get();

    $activeRentals = RentedRentals::with(['product', 'renter'])
        ->where('owner_id', $user->id)
        ->where('status', 'active')
        ->latest()
        ->get();

    // Sold products with orders
    $soldProducts = $user->products() // CHANGED: include partially sold (has buy orders)
        ->whereHas('orders', function ($q) {
            $q->where('transaction_type', 'buy');
        })
        ->with(['orders' => function ($q) {
            $q->where('transaction_type', 'buy');
        }])
        ->get();

    $swapRequests = \App\Models\Swap::whereHas('requestedProduct', function ($query) use ($user) {
        $query->where('user_id', $user->id);
    })->where('status', 'pending')->get();

    $activeSwaps = \App\Models\Swap::where(function ($query) use ($user) {
        $query->whereHas('requestedProduct', fn($q) => $q->where('user_id', $user->id))
              ->orWhereHas('offeredProduct', fn($q) => $q->where('user_id', $user->id));
    })->where('status', 'accepted')->get();

    return view('products.my_listings', compact(
        'products',
        'pendingRequests',
        'activeRentals',
        'soldProducts',
        'swapRequests',
        'activeSwaps'
    ));
}



    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:available,sold,rented,swapped',
        ]);

        $product = Product::where('user_id', Auth::id())->findOrFail($id);

        // If attempting to mark sold but still has units, block it
        if ($request->status === 'sold' && $product->quantity > 0) {
            return redirect()->back()
                ->with('error', 'Cannot mark as sold while quantity > 0. Quantity must reach 0 after purchases.');
        }

        // Only allow sold when quantity == 0
        if ($request->status === 'sold' && $product->quantity === 0) {
            $product->status = 'sold';
        } else {
            // For other statuses just set directly
            $product->status = $request->status;
        }

        $product->save();

        return redirect()->back()->with('success', 'Product status updated successfully!');
    }

    public function destroy($id)
    {
        $product = Product::where('user_id', Auth::id())->findOrFail($id);

        // Delete all associated images from storage
        $allImages = array_filter(array_merge(
            $product->images ?? [],
            $product->image ? [$product->image] : []
        ));
        foreach (array_unique($allImages) as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $product->delete();

        return redirect()->route('products.myListings')->with('success', 'Product deleted successfully!');
    }

public function myPurchases()
{
    $user = Auth::user();

    // Pending rental requests made by this user (not yet approved/paid)
    $pendingRentalRequests = RentalRequest::with(['product', 'owner'])
        ->where('renter_id', $user->id)
        ->where('status', 'requested')
        ->latest()
        ->get();

    // Approved rental requests awaiting payment
    $approvedRentalRequests = RentalRequest::with(['product', 'owner'])
        ->where('renter_id', $user->id)
        ->where('status', 'approved')
        ->latest()
        ->get();

    // Rented items (approved rentals)
    $rentedRentals = RentedRentals::with('product', 'owner')
        ->where('renter_id', $user->id)
        ->where('status', 'active')
        ->orderByDesc('created_at')
        ->get();

    // Purchased products (if you have a purchases/orders table, adjust accordingly)
    $orders = $user->orders()->with('product')->orderByDesc('created_at')->get();

     // Swaps involving this user
    $swaps = \App\Models\Swap::where(function($query) use ($user) {
            $query->where('owner_a_id', $user->id)
                  ->orWhere('owner_b_id', $user->id);
        })
        ->where('status', 'completed') // show only completed swaps
        ->with(['requestedProduct', 'offeredProduct'])
        ->latest()
        ->get();

    // Active (pending/countered) swap requests made by this user
    $pendingSwapRequests = \App\Models\SwapRequest::with(['product', 'offeredProduct', 'owner'])
        ->where('requester_id', $user->id)
        ->whereIn('status', ['requested', 'countered'])
        ->latest()
        ->get();

    return view('products.my_purchases', compact(
        'rentedRentals',
        'pendingRentalRequests',
        'approvedRentalRequests',
        'orders',
        'swaps',
        'pendingSwapRequests'
    ));
}

public function show($id)
{
    $product = Product::with(['user', 'rentals'])->findOrFail($id);

    // Track recently viewed for authenticated users (not the owner)
    if (Auth::check() && $product->user_id !== Auth::id()) {
        RecentlyViewed::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $product->id],
            ['viewed_at' => now()]
        );

        // Keep only the 10 most recent per user
        $idsToKeep = RecentlyViewed::where('user_id', Auth::id())
            ->orderByDesc('viewed_at')
            ->limit(10)
            ->pluck('id');
        RecentlyViewed::where('user_id', Auth::id())
            ->whereNotIn('id', $idsToKeep)
            ->delete();
    }

    $isWishlisted = Auth::check()
        ? Wishlist::where('user_id', Auth::id())->where('product_id', $product->id)->exists()
        : false;

    return view('products.show', compact('product', 'isWishlisted'));
}

}