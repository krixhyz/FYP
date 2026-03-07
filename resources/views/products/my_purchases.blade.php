@extends('layouts.app')

@section('content')
<div class="py-10 max-w-6xl mx-auto px-6 space-y-10">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            My Purchases & Rentals
        </h2>
        <a href="{{ route('products.myListings') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Dashboard
        </a>
    </div>

    {{-- ==================== Rented Items ==================== --}}
    <div class="bg-white shadow-md rounded-2xl overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Rented Items</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Owner</th>
                        <th class="px-6 py-3">Duration</th>
                        <th class="px-6 py-3">Total Paid</th>
                        <th class="px-6 py-3">Rental Dates</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rentedRentals as $rental)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $rental->product?->title ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $rental->owner?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $rental->duration }} days</td>
                            <td class="px-6 py-4 font-semibold text-indigo-600">
                                Rs. {{ $rental->total_amount + $rental->rent_deposit }}
                            </td>
                            <td class="px-6 py-4">{{ $rental->start_date }} → {{ $rental->end_date }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <a href="{{ route('review.create', ['type' => 'rental', 'id' => $rental->id]) }}"
                                       class="text-xs text-yellow-600 hover:text-yellow-800 font-medium hover:underline">⭐ Review</a>
                                    <a href="{{ route('dispute.create', ['type' => 'rental', 'id' => $rental->rentalRequest?->id ?? $rental->id]) }}"
                                       class="text-xs text-red-500 hover:text-red-700 font-medium hover:underline">⚑ Dispute</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-gray-400">No rentals yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== Purchased Products ==================== --}}
    <div class="bg-white shadow-md rounded-2xl overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Purchased Products</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Unit Price</th>
                        <th class="px-6 py-3">Quantity</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        @php
                            $qty = $order->quantity ?? 1;
                            $unit = $order->unit_price ?? $order->product?->price ?? 0;
                            $total = $order->total_price ?? ($qty * $unit);
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $order->product?->title ?? 'N/A' }}</td>
                            <td class="px-6 py-4 font-semibold text-indigo-600">Rs. {{ number_format($unit,2) }}</td>
                            <td class="px-6 py-4">{{ $qty }}</td>
                            <td class="px-6 py-4 font-semibold text-green-600">Rs. {{ number_format($total,2) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 text-xs rounded-full font-medium
                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ $order->created_at->format('Y-m-d') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    @if($order->status === 'pending')
                                        <form method="POST" action="{{ route('order.cancel', $order->id) }}"
                                              onsubmit="return confirm('Cancel this order?')">
                                            @csrf
                                            <button type="submit"
                                                    class="text-xs text-red-600 hover:text-red-800 font-medium hover:underline">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif
                                    @if($order->status === 'completed')
                                        <a href="{{ route('review.create', ['type' => 'order', 'id' => $order->id]) }}"
                                           class="text-xs text-yellow-600 hover:text-yellow-800 font-medium hover:underline">⭐ Review</a>
                                    @endif
                                    @if(in_array($order->status, ['pending','completed']))
                                        <a href="{{ route('dispute.create', ['type' => 'order', 'id' => $order->id]) }}"
                                           class="text-xs text-red-500 hover:text-red-700 font-medium hover:underline">⚑ Dispute</a>
                                    @endif
                                    @if($order->status === 'cancelled')
                                        <span class="text-xs text-gray-400">&mdash;</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-gray-400">No purchases yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== Swapped Products ==================== --}}
    <div class="bg-white shadow-md rounded-2xl overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Swapped Products</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Your Product</th>
                        <th class="px-6 py-3">Swapped With</th>
                        <th class="px-6 py-3">Other User</th>
                        <th class="px-6 py-3">Extra Cash</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($swaps as $swap)
                        @php
                            $isOwnerA = $swap->owner_a_id === auth()->id();
                            $yourProduct = $isOwnerA ? $swap->requestedProduct : $swap->offeredProduct;
                            $otherProduct = $isOwnerA ? $swap->offeredProduct : $swap->requestedProduct;
                            $otherUser = $isOwnerA ? $swap->ownerB : $swap->ownerA;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $yourProduct->title ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $otherProduct->title ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $otherUser?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 font-semibold text-indigo-600">
                                {{ $swap->offered_amount > 0 ? '+Rs. '.$swap->offered_amount : 'None' }}
                            </td>
                            <td class="px-6 py-4">{{ $swap->updated_at?->format('Y-m-d') ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <a href="{{ route('review.create', ['type' => 'swap', 'id' => $swap->id]) }}"
                                       class="text-xs text-yellow-600 hover:text-yellow-800 font-medium hover:underline">⭐ Review</a>
                                    <a href="{{ route('dispute.create', ['type' => 'swap', 'id' => $swap->id]) }}"
                                       class="text-xs text-red-500 hover:text-red-700 font-medium hover:underline">⚑ Dispute</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-gray-400">No swaps yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== Pending Rental Requests ==================== --}}
    <div class="bg-white shadow-md rounded-2xl overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Pending Rental Requests</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Owner</th>
                        <th class="px-6 py-3">Duration</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Requested On</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pendingRentalRequests as $req)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $req->product?->title ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $req->owner?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $req->duration }} days</td>
                            <td class="px-6 py-4 font-semibold text-indigo-600">Rs. {{ number_format($req->total_amount + $req->rent_deposit, 2) }}</td>
                            <td class="px-6 py-4">{{ $req->created_at->format('Y-m-d') }}</td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('rental.cancel', $req->id) }}"
                                      onsubmit="return confirm('Cancel this rental request?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs text-red-600 hover:text-red-800 font-medium hover:underline">
                                        Cancel
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-gray-400">No pending rental requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== Approved Rentals Awaiting Payment ==================== --}}
    @if($approvedRentalRequests->count() > 0)
    <div class="bg-white shadow-md rounded-2xl overflow-hidden border-l-4 border-green-500">
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Approved Rentals &mdash; Awaiting Payment</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Owner</th>
                        <th class="px-6 py-3">Duration</th>
                        <th class="px-6 py-3">Total Due</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($approvedRentalRequests as $req)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $req->product?->title ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $req->owner?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $req->duration }} days</td>
                            <td class="px-6 py-4 font-semibold text-green-600">Rs. {{ number_format($req->total_amount + $req->rent_deposit, 2) }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('rental.payment', $req->id) }}"
                                   class="text-xs bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg font-medium">
                                    Pay Now
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ==================== Pending Swap Requests ==================== --}}
    <div class="bg-white shadow-md rounded-2xl overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Pending Swap Requests</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Requested Item</th>
                        <th class="px-6 py-3">Offered Item</th>
                        <th class="px-6 py-3">Owner</th>
                        <th class="px-6 py-3">Cash Offered</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pendingSwapRequests as $req)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $req->product?->title ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $req->offeredProduct?->title ?? 'Cash only' }}</td>
                            <td class="px-6 py-4">{{ $req->owner?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $req->offered_amount ? 'Rs. '.$req->offered_amount : '&mdash;' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 text-xs rounded-full font-medium
                                    {{ $req->status === 'countered' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 flex gap-2">
                                @if($req->status === 'countered')
                                    <a href="{{ route('swap.request.show', $req->id) }}"
                                       class="text-xs text-blue-600 hover:underline font-medium">View Counter</a>
                                @endif
                                <form method="POST" action="{{ route('swap.request.cancel', $req->id) }}"
                                      onsubmit="return confirm('Cancel this swap request?')">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs text-red-600 hover:text-red-800 font-medium hover:underline">
                                        Cancel
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-gray-400">No pending swap requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
