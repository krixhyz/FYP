<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('My Purchases & Rentals') }}
            </h2>
            <a href="{{ route('products.myListings') }}" 
               class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm">
               Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-10">

        {{-- ==================== Rented Items ==================== --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Rented Items</h3>

            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Owner</th>
                        <th class="px-6 py-3">Duration</th>
                        <th class="px-6 py-3">Total Paid</th>
                        <th class="px-6 py-3">Rental Dates</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rentedRentals as $rental)
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $rental->product?->title ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4">{{ $rental->owner?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $rental->duration }} days</td>
                            <td class="px-6 py-4">Rs. {{ $rental->total_amount + $rental->rent_deposit }}</td>
                            <td class="px-6 py-4">{{ $rental->start_date }} to {{ $rental->end_date }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-500">No rentals yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ==================== Purchased Products ==================== --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Purchased Products</h3>

            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">Date Purchased</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $order->product?->title ?? 'N/A' }}</td>
                            <td class="px-6 py-4">Rs. {{ $order->product?->price ?? '0.00' }}</td>
                            <td class="px-6 py-4">{{ $order->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-500">No purchases yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
