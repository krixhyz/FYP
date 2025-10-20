<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rental;
use App\Models\RentedRentals;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\RentalRequest;



class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->get();
    
        return view('products.index', compact('products'));
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
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
            'rent_deposit' => 'required_if:listing_type.*,rent|nullable|numeric|min:0',
            'rent_fare' => 'required_if:listing_type.*,rent|nullable|numeric|min:0',
            'rent_type' => 'required_if:listing_type.*,rent|nullable|in:hourly,daily',
            'start_date' => 'required_if:listing_type.*,rent|nullable|date|after_or_equal:today',
            'end_date' => 'required_if:listing_type.*,rent|nullable|date|after_or_equal:start_date',
            'rent_duration' => 'required_if:listing_type.*,rent|nullable|integer|min:1',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            try {
                $filename = time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
                $imagePath = $request->file('image')->storeAs('uploads/products', $filename, 'public');
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['image' => 'Failed to upload image: ' . $e->getMessage()]);
            }
        }

        // Create product entry
        $product = Product::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'price' => $request->price,
            'type' => $request->listing_type,
            'image' => $imagePath,
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

        return redirect()->route('dashboard')->with('success', 'Listing added successfully!');
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
        'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
        'rent_deposit' => 'required_if:listing_type.*,rent|nullable|numeric|min:0',
        'rent_fare' => 'required_if:listing_type.*,rent|nullable|numeric|min:0',
        'start_date' => 'required_if:listing_type.*,rent|nullable|date|after_or_equal:today',
        'end_date' => 'required_if:listing_type.*,rent|nullable|date|after_or_equal:start_date',
        'rent_duration' => 'required_if:listing_type.*,rent|nullable|integer|min:1',
    ]);

    // Handle image replacement (optional)
    if ($request->hasFile('image')) {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
        $filename = time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
        $imagePath = $request->file('image')->storeAs('uploads/products', $filename, 'public');
        $product->image = $imagePath;
    }

    // Update product
    $product->update([
        'title' => $request->title,
        'description' => $request->description,
        'category' => $request->category,
        'price' => $request->price,
        'type' => $request->listing_type,
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

    // All products owned by this user
    $products = $user->products()->get();

    // Fetch pending rental requests from rental_requests table where the logged-in user is the owner
    $pendingRequests = RentalRequest::with(['product', 'renter'])
        ->where('owner_id', $user->id)
        ->where('status', 'requested')
        ->latest()
        ->get();

    // Active rentals (from rented_rentals table)
    $activeRentals = RentedRentals::with(['product', 'renter'])
        ->where('owner_id', $user->id)
        ->where('status', 'active')
        ->latest()
        ->get();

    // Sold products
    $soldProducts = $user->products()->where('status', 'sold')->get();

    return view('products.my_listings', compact(
        'products',
        'pendingRequests',
        'activeRentals',
        'soldProducts'
    ));
}



    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:available,sold,rented,swapped',
        ]);

        $product = Product::where('user_id', Auth::id())->findOrFail($id);
        $product->status = $request->status;
        $product->save();

        return redirect()->back()->with('success', 'Product status updated successfully!');
    }

    public function destroy($id)
    {
        $product = Product::where('user_id', Auth::id())->findOrFail($id);

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.myListings')->with('success', 'Product deleted successfully!');
    }

public function myPurchases()
{
    $user = Auth::user();

    // Rented items (approved rentals)
    $rentedRentals = RentedRentals::with('product', 'owner')
        ->where('renter_id', $user->id)
        ->where('status', 'active')
        ->orderByDesc('created_at')
        ->get();

    // Purchased products (if you have a purchases/orders table, adjust accordingly)
    $orders = $user->orders()->with('product')->orderByDesc('created_at')->get();

    return view('products.my_purchases', compact('rentedRentals', 'orders'));
}



}