<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Listings Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-10">

        {{-- ==================== SECTION 1: My Products ==================== --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">My Products</h3>

            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
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
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-6 py-4">
                                <img src="{{ asset('storage/' . $product->image) }}" alt="Image"
                                     class="w-16 h-16 object-cover rounded">
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $product->title }}</td>
                            <td class="px-6 py-4">Rs. {{ $product->price }}</td>
                            <td class="px-6 py-4">
                                <form action="{{ route('products.updateStatus', $product->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="available" {{ $product->status == 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="sold" {{ $product->status == 'sold' ? 'selected' : '' }}>Sold</option>
                                        <option value="rented" {{ $product->status == 'rented' ? 'selected' : '' }}>Rented</option>
                                        <option value="swapped" {{ $product->status == 'swapped' ? 'selected' : '' }}>Swapped</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                      onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-800 text-sm font-semibold">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4">No active products listed yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ==================== SECTION 2: Rentals ==================== --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Rentals</h3>

            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Renter</th>
                        <th class="px-6 py-3">Duration</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rentals as $rental)
                        @if(in_array($rental->rental_status, ['requested', 'approved']))
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    {{ $rental->product?->title ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4">{{ $rental->renter?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4">{{ $rental->duration ?? 0 }} days</td>
                                <td class="px-6 py-4">Rs. {{ $rental->total_amount ?? 0 }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-medium
                                        {{ $rental->rental_status == 'requested' ? 'bg-yellow-100 text-yellow-700' :
                                           ($rental->rental_status == 'approved' ? 'bg-green-100 text-green-700' :
                                           'bg-blue-100 text-blue-700') }}">
                                        {{ ucfirst($rental->rental_status ?? 'Unknown') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($rental->rental_status != 'approved')
                                        <a href="{{ route('rental.review', $rental->id) }}"
                                           class="bg-gray-600 hover:bg-gray-700 text-white text-xs px-3 py-1 rounded">
                                            View Request
                                        </a>
                                    @else
                                        <span class="text-xs text-green-600 font-semibold">Approved</span>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-gray-500">No rental records available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ==================== SECTION 3: Sold Products ==================== --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Sold Products</h3>

            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">Date Sold</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products->where('status', 'sold') as $sold)
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $sold->title }}</td>
                            <td class="px-6 py-4">Rs. {{ $sold->price }}</td>
                            <td class="px-6 py-4">{{ $sold->updated_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-4">No sold products yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
