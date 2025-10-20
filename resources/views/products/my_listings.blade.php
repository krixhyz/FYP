<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 text-center"> My Listings Dashboard</h2>
    </x-slot>

    <div class="py-10 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-12">

        {{-- ==================== SECTION 1: My Products ==================== --}}
        <div class="bg-white shadow-md rounded-2xl p-6 border border-gray-100">
            <h3 class="text-lg font-semibold mb-5 text-gray-800 flex items-center gap-2">
                <span> My Products</span>
            </h3>

            <table class="w-full text-sm text-left text-gray-600 border-collapse">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th class="px-6 py-3">Image</th>
                        <th class="px-6 py-3">Title</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products->where('status', '!=', 'sold') as $product)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <img src="{{ asset('storage/' . $product->image) }}" alt="Image"
                                     class="w-16 h-16 object-cover rounded-md shadow-sm border">
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $product->title }}</td>
                            <td class="px-6 py-4">
                                @if($product->price)
                                    <span class="font-semibold text-gray-800">Rs. {{ $product->price }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('products.updateStatus', $product->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                            class="border-gray-300 rounded-md text-sm p-1.5 focus:ring-2 focus:ring-blue-400">
                                        <option value="available" {{ $product->status == 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="sold" {{ $product->status == 'sold' ? 'selected' : '' }}>Sold</option>
                                        <option value="rented" {{ $product->status == 'rented' ? 'selected' : '' }}>Rented</option>
                                        <option value="swapped" {{ $product->status == 'swapped' ? 'selected' : '' }}>Swapped</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 space-x-2 flex flex-col sm:flex-row gap-2">
                                <a href="{{ route('products.edit', $product->id) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 rounded-md font-medium transition">
                                     Edit Product
                                </a>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded-md font-medium transition">
                                         Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-500">No active products listed yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        
        {{-- ==================== SECTION 2: Pending Requests ==================== --}}
<div class="bg-white shadow-md rounded-2xl p-6 mb-8 border border-gray-100">
    <h3 class="text-lg font-semibold mb-5 text-gray-800">Pending Rental Requests</h3>

    <table class="w-full text-sm text-left text-gray-600 border-collapse">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-6 py-3">Product</th>
                <th class="px-6 py-3">Renter</th>
                <th class="px-6 py-3">Duration</th>
                <th class="px-6 py-3">Amount</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pendingRequests as $request)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $request->product->title ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $request->renter->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $request->duration }} days</td>
                    <td class="px-6 py-4">Rs. {{ $request->total_amount }}</td>
                    <td class="px-6 py-4">
                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 text-xs font-semibold rounded">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('rental.review', $request->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded">
                            Review
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-6 text-gray-500">No pending requests.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ==================== SECTION 3: Active Rentals ==================== --}}
<div class="bg-white shadow-md rounded-2xl p-6 border border-gray-100">
    <h3 class="text-lg font-semibold mb-5 text-gray-800">Active Rentals</h3>

    <table class="w-full text-sm text-left text-gray-600 border-collapse">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-6 py-3">Product</th>
                <th class="px-6 py-3">Renter</th>
                <th class="px-6 py-3">From - To</th>
                <th class="px-6 py-3">Amount</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($activeRentals as $rental)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $rental->product->title ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $rental->renter->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $rental->start_date }} → {{ $rental->end_date }}</td>
                    <td class="px-6 py-4">Rs. {{ $rental->total_amount }}</td>
                    <td class="px-6 py-4">
                        <span class="bg-green-100 text-green-700 px-2 py-1 text-xs font-semibold rounded">
                            {{ ucfirst($rental->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="#" class="bg-gray-700 hover:bg-gray-800 text-white text-xs px-3 py-1 rounded">
                            View Details
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-6 text-gray-500">No active rentals.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>


        {{-- ==================== SECTION 4: Sold Products ==================== --}}
        <div class="bg-white shadow-md rounded-2xl p-6 border border-gray-100">
            <h3 class="text-lg font-semibold mb-5 text-gray-800"> Sold Products</h3>

            <table class="w-full text-sm text-left text-gray-600 border-collapse">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">Date Sold</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products->where('status', 'sold') as $sold)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $sold->title }}</td>
                            <td class="px-6 py-4">Rs. {{ $sold->price }}</td>
                            <td class="px-6 py-4">{{ $sold->updated_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-6 text-gray-500">No sold products yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
