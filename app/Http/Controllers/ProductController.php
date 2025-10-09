<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rental;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $products = \App\Models\Product::latest()->get();
    return view('index', compact('products'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
{
    return view('products.create');
}

    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'price' => 'nullable|numeric',
        'listing_type' => 'required|array', // sell, rent, swap
        'listing_type.*' => 'in:sell,rent,swap',
        'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        'rent_deposit' => 'required_if:listing_type.*,rent|nullable|numeric',
        'rent_fare' => 'required_if:listing_type.*,rent|nullable|numeric',
        'rent_type' => 'required_if:listing_type.*,rent|nullable|in:hourly,daily',
        'rent_duration' => 'required_if:listing_type.*,rent|nullable|integer|min:1',
    ]);

    // Handle image upload
    $imagePath = $request->file('image') ? $request->file('image')->store('products', 'public') : null;

    // 1️⃣ Create product
    $product = Product::create([
        'user_id' => Auth::id(),
        'title' => $request->title,
        'description' => $request->description,
        'price' => $request->price,
        'type' => $request->listing_type,
        'image' => $imagePath,
        'category' => $request->category ?? 'general',
        'status' => 'pending',
    ]);

    // 2️⃣ If rent is selected, create rental entry
    if (in_array('rent', $request->listing_type)) {
        Rental::create([
            'product_id' => $product->id,
            'owner_id' => Auth::id(),
            'rent_deposit' => $request->rent_deposit,
            'rent_fare' => $request->rent_fare,
            'rent_type' => $request->rent_type,
            'rent_duration' => $request->rent_duration,
            'status' => 'available',
        ]);
    }

    return redirect()->route('dashboard')->with('success', 'Listing added successfully!');
}

public function myListings()
{
    $products = Product::where('user_id', Auth::id())->latest()->get();
    return view('products.my_listings', compact('products'));
}
    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
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


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    $product = Product::where('user_id', Auth::id())->findOrFail($id);

    // Delete product image if it exists
    // if ($product->image && \Storage::exists('public/' . $product->image)) {
    //     \Storage::delete('public/' . $product->image);
    // }

    $product->delete();

    return redirect()->route('products.myListings')->with('success', 'Product deleted successfully!');
}

}
