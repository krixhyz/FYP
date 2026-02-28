<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rental;
use App\Models\RentedRentals;
use App\Services\LocationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\RentalRequest;



class ProductController extends Controller
{
    public function __construct(private readonly LocationService $locationService)
    {
    }

    public function index()
    {
        $city = trim((string) request('city', ''));
        $locationQuery = trim((string) request('location', ''));
        $radiusKm = (float) request('radius_km', 0);
        $lat = request()->filled('lat') ? (float) request('lat') : null;
        $lng = request()->filled('lng') ? (float) request('lng') : null;

        $productsQuery = Product::query()
            ->where('status', 'available');

        if (Auth::check()) {
            $productsQuery->where('user_id', '!=', Auth::id());
        }

        if ($city !== '') {
            $productsQuery->where('city', 'like', '%'.$city.'%');
        }

        if ($locationQuery !== '') {
            $productsQuery->where(function ($query) use ($locationQuery) {
                $query->where('location_text', 'like', '%'.$locationQuery.'%')
                    ->orWhere('city', 'like', '%'.$locationQuery.'%');
            });
        }

        if ($lat !== null && $lng !== null && $radiusKm > 0) {
            $latDelta = $radiusKm / 111.045;
            $lngDelta = $radiusKm / (111.045 * max(cos(deg2rad($lat)), 0.01));

            $productsQuery
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
                ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta])
                ->selectRaw(
                    'products.*, (6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))) AS distance_km',
                    [$lat, $lng, $lat]
                )
                ->having('distance_km', '<=', $radiusKm)
                ->orderBy('distance_km');
        } else {
            $productsQuery->latest();
        }

        $products = $productsQuery->paginate(20)->withQueryString();

        return view('products.index', compact('products', 'city', 'locationQuery', 'radiusKm', 'lat', 'lng'));
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
            'quantity' => 'required|integer|min:1', // NEW
            'location_text' => 'required|string|max:255',
            'city' => 'nullable|string|max:120',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'place_id' => 'nullable|string|max:100',
            'location_precision' => 'required|in:approx,exact',
        ]);

        $locationData = $this->resolveLocationData($request);

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
            'quantity' => $request->quantity, // NEW
            'type' => $request->listing_type,
            'image' => $imagePath,
            'status' => 'available',
            'location' => $locationData['location_text'],
            'location_text' => $locationData['location_text'],
            'city' => $locationData['city'],
            'latitude' => $locationData['latitude'],
            'longitude' => $locationData['longitude'],
            'place_id' => $locationData['place_id'],
            'location_precision' => $locationData['location_precision'],
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
        'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
        'rent_deposit' => 'required_if:listing_type.*,rent|nullable|numeric|min:0',
        'rent_fare' => 'required_if:listing_type.*,rent|nullable|numeric|min:0',
        'start_date' => 'required_if:listing_type.*,rent|nullable|date|after_or_equal:today',
        'end_date' => 'required_if:listing_type.*,rent|nullable|date|after_or_equal:start_date',
        'rent_duration' => 'required_if:listing_type.*,rent|nullable|integer|min:1',
        'quantity' => 'required|integer|min:1', // NEW
        'location_text' => 'required|string|max:255',
        'city' => 'nullable|string|max:120',
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
        'place_id' => 'nullable|string|max:100',
        'location_precision' => 'required|in:approx,exact',
    ]);

    $locationData = $this->resolveLocationData($request);

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
        'quantity' => $request->quantity, // NEW
        'type' => $request->listing_type,
        'location' => $locationData['location_text'],
        'location_text' => $locationData['location_text'],
        'city' => $locationData['city'],
        'latitude' => $locationData['latitude'],
        'longitude' => $locationData['longitude'],
        'place_id' => $locationData['place_id'],
        'location_precision' => $locationData['location_precision'],
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

     // Swaps involving this user
    $swaps = \App\Models\Swap::where(function($query) use ($user) {
            $query->where('owner_a_id', $user->id)
                  ->orWhere('owner_b_id', $user->id);
        })
        ->where('status', 'completed') // show only completed swaps
        ->with(['requestedProduct', 'offeredProduct'])
        ->latest()
        ->get();

    return view('products.my_purchases', compact('rentedRentals', 'orders', 'swaps'));
}

public function show($id)
{
    $product = Product::with(['user', 'rentals'])->findOrFail($id);
    return view('products.show', compact('product'));
}

private function resolveLocationData(Request $request): array
{
    $locationText = trim((string) $request->input('location_text', ''));
    $city = trim((string) $request->input('city', ''));
    $latitude = (float) $request->input('latitude');
    $longitude = (float) $request->input('longitude');
    $placeId = trim((string) $request->input('place_id', ''));

    if ($city === '' || $locationText === '') {
        $resolved = $this->locationService->reverse($latitude, $longitude);
        if (is_array($resolved)) {
            if ($locationText === '' && ! empty($resolved['location_text'])) {
                $locationText = (string) $resolved['location_text'];
            }
            if ($city === '' && ! empty($resolved['city'])) {
                $city = (string) $resolved['city'];
            }
            if ($placeId === '' && ! empty($resolved['place_id'])) {
                $placeId = (string) $resolved['place_id'];
            }
        }
    }

    if ($city === '' && $locationText !== '') {
        $city = trim((string) explode(',', $locationText)[0]);
    }

    return [
        'location_text' => $locationText,
        'city' => $city,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'place_id' => $placeId !== '' ? $placeId : null,
        'location_precision' => $request->input('location_precision', 'approx'),
    ];
}

}
