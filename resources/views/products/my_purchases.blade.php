@extends('layouts.app')

@section('content')
<div class="px-8 md:px-16 py-12 space-y-8">
    @php
        $completedOrders = $orders->where('status', 'completed');
        $pendingOrders = $orders->where('status', 'pending');
        $totalSpent = $completedOrders->sum(fn($o) => $o->total_price ?? (($o->unit_price ?? $o->product?->price ?? 0) * ($o->quantity ?? 1)));
    @endphp

    <!-- Hero Section -->
    <section class="bg-[#f3f3f3] px-8 md:px-12 py-8 border-t border-b border-[rgba(189,202,189,0.3)]">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Buyer Workspace</p>
                <h1 class="font-space font-bold text-3xl text-[#1a1c1c] mb-1">My Purchases and Transactions</h1>
                <p class="font-manrope text-base text-[#444746]">See exactly what you bought, rented, swapped, and what actions are still pending.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('dashboard') }}" class="bg-transparent border-2 border-[#006a38] text-[#006a38] px-6 py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] transition-all">Main Dashboard</a>
                <a href="{{ route('products.myListings') }}" class="bg-transparent border-2 border-[#006a38] text-[#006a38] px-6 py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] transition-all">Seller View</a>
            </div>
        </div>
    </section>

    <!-- Stats Strip -->
    <section class="bg-[#f3f3f3] border-b border-[rgba(189,202,189,0.3)]">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-0">
            <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Completed Purchases</p>
                <p class="font-space font-bold text-2xl text-[#006a38] mt-1">{{ $completedOrders->count() }}</p>
            </div>
            <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Pending Purchases</p>
                <p class="font-space font-bold text-2xl text-[#006a38] mt-1">{{ $pendingOrders->count() }}</p>
            </div>
            <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Total Spent</p>
                <p class="font-space font-bold text-2xl text-[#006a38] mt-1">Rs. {{ number_format($totalSpent, 2) }}</p>
            </div>
            <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Active Rentals</p>
                <p class="font-space font-bold text-2xl text-[#006a38] mt-1">{{ $rentedRentals->count() }}</p>
            </div>
            <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Completed Swaps</p>
                <p class="font-space font-bold text-2xl text-[#006a38] mt-1">{{ $swaps->count() }}</p>
            </div>
        </div>
    </section>

    <!-- Purchased Products Panel -->
    <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
        <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.2)] flex items-center justify-between">
            <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Purchased Products</h2>
            <span class="bg-[#e2e2e2] text-[#1a1c1c] text-[10px] font-space font-bold px-3 py-1.5">{{ $orders->count() }} Total Orders</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm font-manrope">
                <thead>
                    <tr class="bg-[#f3f3f3] border-b border-[rgba(189,202,189,0.2)]">
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Product</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Unit Price</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Quantity</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Total</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Status</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Date</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php
                            $qty = $order->quantity ?? 1;
                            $unit = $order->unit_price ?? $order->product?->price ?? 0;
                            $total = $order->total_price ?? ($qty * $unit);
                        @endphp
                        <tr class="border-b border-[rgba(189,202,189,0.2)] hover:bg-[#f9f9f9]">
                            <td class="px-4 py-3">{{ $order->product?->title ?? 'N/A' }}</td>
                            <td class="px-4 py-3">Rs. {{ number_format($unit,2) }}</td>
                            <td class="px-4 py-3">{{ $qty }}</td>
                            <td class="px-4 py-3">Rs. {{ number_format($total,2) }}</td>
                            <td class="px-4 py-3">
                                <span class="text-[10px] font-space font-bold px-3 py-1.5 {{ $order->status === 'pending' ? 'bg-[#ffd580] text-[#664d03]' : '' }} {{ $order->status === 'completed' ? 'bg-[#d4edda] text-[#155724]' : '' }} {{ $order->status === 'cancelled' ? 'bg-[#f8d7da] text-[#721c24]' : '' }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $order->created_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    @if($order->status === 'pending')
                                        <a href="{{ route('order.checkout', $order->id) }}" class="text-[11px] text-[#006a38] font-space font-bold uppercase hover:text-[#004a29]">Checkout</a>
                                        <form method="POST" action="{{ route('order.cancel', $order->id) }}" onsubmit="return confirm('Cancel this order?')">
                                            @csrf
                                            <button type="submit" class="text-[11px] text-[#ba1a1a] font-space font-bold uppercase hover:text-[#8a1313]">Cancel</button>
                                        </form>
                                    @endif
                                    @if($order->status === 'completed')
                                        <a href="{{ route('review.create', ['type' => 'order', 'id' => $order->id]) }}" class="text-[11px] text-[#d97706] font-space font-bold uppercase hover:text-[#92400e]">Review</a>
                                    @endif
                                    @if(in_array($order->status, ['pending','completed']))
                                        <a href="{{ route('dispute.create', ['type' => 'order', 'id' => $order->id]) }}" class="text-[11px] text-[#ba1a1a] font-space font-bold uppercase hover:text-[#8a1313]">Dispute</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-3 text-center text-[#444746]">No purchases yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Rented Items Panel -->
    <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
        <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.2)]">
            <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Rented Items</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm font-manrope">
                <thead>
                    <tr class="bg-[#f3f3f3] border-b border-[rgba(189,202,189,0.2)]">
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Product</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Owner</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Duration</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Total Paid</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Rental Dates</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rentedRentals as $rental)
                        <tr class="border-b border-[rgba(189,202,189,0.2)] hover:bg-[#f9f9f9]">
                            <td class="px-4 py-3">{{ $rental->product?->title ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $rental->owner?->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $rental->duration }} days</td>
                            <td class="px-4 py-3">Rs. {{ $rental->total_amount + $rental->rent_deposit }}</td>
                            <td class="px-4 py-3">{{ optional($rental->start_date)->format('Y-m-d') }} to {{ optional($rental->end_date)->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <a href="{{ route('review.create', ['type' => 'rental', 'id' => $rental->id]) }}" class="text-[11px] text-[#d97706] font-space font-bold uppercase hover:text-[#92400e]">Review</a>
                                    <a href="{{ route('dispute.create', ['type' => 'rental', 'id' => $rental->rentalRequest?->id ?? $rental->id]) }}" class="text-[11px] text-[#ba1a1a] font-space font-bold uppercase hover:text-[#8a1313]">Dispute</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-3 text-center text-[#444746]">No rentals yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Swapped Products Panel -->
    <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
        <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.2)]">
            <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Swapped Products</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm font-manrope">
                <thead>
                    <tr class="bg-[#f3f3f3] border-b border-[rgba(189,202,189,0.2)]">
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Your Product</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Swapped With</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Other User</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Extra Cash</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Date</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($swaps as $swap)
                        @php
                            $isOwnerA = $swap->owner_a_id === auth()->id();
                            $yourProduct = $isOwnerA ? $swap->requestedProduct : $swap->offeredProduct;
                            $otherProduct = $isOwnerA ? $swap->offeredProduct : $swap->requestedProduct;
                            $otherUser = $isOwnerA ? $swap->ownerB : $swap->ownerA;
                        @endphp
                        <tr class="border-b border-[rgba(189,202,189,0.2)] hover:bg-[#f9f9f9]">
                            <td class="px-4 py-3">{{ $yourProduct->title ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $otherProduct->title ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $otherUser?->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $swap->offered_amount > 0 ? '+Rs. '.$swap->offered_amount : 'None' }}</td>
                            <td class="px-4 py-3">{{ $swap->updated_at?->format('Y-m-d') ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <a href="{{ route('review.create', ['type' => 'swap', 'id' => $swap->id]) }}" class="text-[11px] text-[#d97706] font-space font-bold uppercase hover:text-[#92400e]">Review</a>
                                    <a href="{{ route('dispute.create', ['type' => 'swap', 'id' => $swap->id]) }}" class="text-[11px] text-[#ba1a1a] font-space font-bold uppercase hover:text-[#8a1313]">Dispute</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-3 text-center text-[#444746]">No swaps yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pending Rental Requests Panel -->
    <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
        <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.2)]">
            <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Pending Rental Requests</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm font-manrope">
                <thead>
                    <tr class="bg-[#f3f3f3] border-b border-[rgba(189,202,189,0.2)]">
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Product</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Owner</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Duration</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Total</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Requested On</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingRentalRequests as $req)
                        <tr class="border-b border-[rgba(189,202,189,0.2)] hover:bg-[#f9f9f9]">
                            <td class="px-4 py-3">{{ $req->product?->title ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $req->owner?->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $req->duration }} days</td>
                            <td class="px-4 py-3">Rs. {{ number_format($req->total_amount + $req->rent_deposit, 2) }}</td>
                            <td class="px-4 py-3">{{ $req->created_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3"><form method="POST" action="{{ route('rental.cancel', $req->id) }}" onsubmit="return confirm('Cancel this rental request?')">@csrf @method('DELETE')<button type="submit" class="text-[11px] text-[#ba1a1a] font-space font-bold uppercase hover:text-[#8a1313]">Cancel</button></form></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-3 text-center text-[#444746]">No pending rental requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($approvedRentalRequests->count() > 0)
    <div class="bg-[#f3f3f3] shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
        <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.2)]">
            <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Approved Rentals Awaiting Payment</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm font-manrope">
                <thead>
                    <tr class="bg-white border-b border-[rgba(189,202,189,0.2)]">
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Product</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Owner</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Duration</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Total Due</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($approvedRentalRequests as $req)
                        <tr class="border-b border-[rgba(189,202,189,0.2)] hover:bg-white">
                            <td class="px-4 py-3">{{ $req->product?->title ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $req->owner?->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $req->duration }} days</td>
                            <td class="px-4 py-3">Rs. {{ number_format($req->total_amount + $req->rent_deposit, 2) }}</td>
                            <td class="px-4 py-3"><a href="{{ route('rental.payment', $req->id) }}" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-3 py-1.5 font-space text-[11px] font-bold uppercase hover:brightness-110 inline-block">Pay Now</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(($awaitingSwapPaymentRequests ?? collect())->count() > 0)
    <div class="bg-[#f3f3f3] shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
        <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.2)]">
            <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Swap Requests Awaiting Payment</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm font-manrope">
                <thead>
                    <tr class="bg-white border-b border-[rgba(189,202,189,0.2)]">
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Requested Item</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Offered Item</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Owner</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Cash Top-up</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($awaitingSwapPaymentRequests as $req)
                        <tr class="border-b border-[rgba(189,202,189,0.2)] hover:bg-white">
                            <td class="px-4 py-3">{{ $req->product?->title ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $req->offeredProduct?->title ?? 'Cash only' }}</td>
                            <td class="px-4 py-3">{{ $req->owner?->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $req->offered_amount ? 'Rs. '.$req->offered_amount : '-' }}</td>
                            <td class="px-4 py-3"><a href="{{ route('swap.checkout', $req->id) }}" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-3 py-1.5 font-space text-[11px] font-bold uppercase hover:brightness-110 inline-block">Pay Now</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Pending Swap Requests Panel -->
    <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
        <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.2)]">
            <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Pending Swap Requests</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm font-manrope">
                <thead>
                    <tr class="bg-[#f3f3f3] border-b border-[rgba(189,202,189,0.2)]">
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Requested Item</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Offered Item</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Owner</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Cash Offered</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Status</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingSwapRequests as $req)
                        <tr class="border-b border-[rgba(189,202,189,0.2)] hover:bg-[#f9f9f9]">
                            <td class="px-4 py-3">{{ $req->product?->title ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $req->offeredProduct?->title ?? 'Cash only' }}</td>
                            <td class="px-4 py-3">{{ $req->owner?->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $req->offered_amount ? 'Rs. '.$req->offered_amount : '-' }}</td>
                            <td class="px-4 py-3"><span class="text-[10px] font-space font-bold px-3 py-1.5 {{ $req->status === 'countered' ? 'bg-[#ffd580] text-[#664d03]' : 'bg-[#d1ecf1] text-[#0c5460]' }}">{{ ucfirst($req->status) }}</span></td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    @if($req->status === 'countered')
                                        <a href="{{ route('swap.request.show', $req->id) }}" class="text-[11px] text-[#006a38] font-space font-bold uppercase hover:text-[#004a29]">View</a>
                                    @endif
                                    <form method="POST" action="{{ route('swap.request.cancel', $req->id) }}" onsubmit="return confirm('Cancel this swap request?')">@csrf<button type="submit" class="text-[11px] text-[#ba1a1a] font-space font-bold uppercase hover:text-[#8a1313]">Cancel</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-3 text-center text-[#444746]">No pending swap requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
