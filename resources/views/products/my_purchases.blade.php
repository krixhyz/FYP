@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-8">
    @php
        $completedOrders = $orders->where('status', 'completed');
        $pendingOrders = $orders->where('status', 'pending');
        $totalSpent = $completedOrders->sum(fn($o) => $o->total_price ?? (($o->unit_price ?? $o->product?->price ?? 0) * ($o->quantity ?? 1)));
    @endphp

    <section class="surface-card-strong p-6 sm:p-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="section-kicker">Buyer Workspace</p>
                <h1 class="section-title mt-1">My Purchases and Transactions</h1>
                <p class="meta-text mt-2">See exactly what you bought, rented, swapped, and what actions are still pending.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('dashboard') }}" class="btn-pill btn-pill-soft">Main Dashboard</a>
                <a href="{{ route('products.myListings') }}" class="btn-pill btn-pill-soft">Seller View</a>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <article class="surface-card p-4"><p class="meta-text">Completed Purchases</p><p class="mt-2 text-3xl font-extrabold">{{ $completedOrders->count() }}</p></article>
        <article class="surface-card p-4"><p class="meta-text">Pending Purchases</p><p class="mt-2 text-3xl font-extrabold">{{ $pendingOrders->count() }}</p></article>
        <article class="surface-card p-4"><p class="meta-text">Total Spent</p><p class="mt-2 text-3xl font-extrabold text-[var(--reloop-primary-dark)]">Rs. {{ number_format($totalSpent, 2) }}</p></article>
        <article class="surface-card p-4"><p class="meta-text">Active Rentals</p><p class="mt-2 text-3xl font-extrabold">{{ $rentedRentals->count() }}</p></article>
        <article class="surface-card p-4"><p class="meta-text">Completed Swaps</p><p class="mt-2 text-3xl font-extrabold">{{ $swaps->count() }}</p></article>
    </section>

    <section class="surface-card p-5 overflow-x-auto">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-lg font-extrabold">Purchased Products</h2>
            <span class="status-chip status-info">{{ $orders->count() }} Total Orders</span>
        </div>
        <table class="min-w-full text-sm">
            <thead><tr><th>Product</th><th>Unit Price</th><th>Quantity</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse ($orders as $order)
                    @php
                        $qty = $order->quantity ?? 1;
                        $unit = $order->unit_price ?? $order->product?->price ?? 0;
                        $total = $order->total_price ?? ($qty * $unit);
                    @endphp
                    <tr>
                        <td>{{ $order->product?->title ?? 'N/A' }}</td>
                        <td>Rs. {{ number_format($unit,2) }}</td>
                        <td>{{ $qty }}</td>
                        <td>Rs. {{ number_format($total,2) }}</td>
                        <td>
                            <span class="status-chip {{ $order->status === 'pending' ? 'status-warning' : '' }} {{ $order->status === 'completed' ? 'status-success' : '' }} {{ $order->status === 'cancelled' ? 'status-error' : '' }}">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td>{{ $order->created_at->format('Y-m-d') }}</td>
                        <td>
                            <div class="flex flex-col gap-1">
                                @if($order->status === 'pending')
                                    <form method="POST" action="{{ route('order.cancel', $order->id) }}" onsubmit="return confirm('Cancel this order?')">
                                        @csrf
                                        <button type="submit" class="text-xs text-red-700 hover:underline">Cancel</button>
                                    </form>
                                @endif
                                @if($order->status === 'completed')
                                    <a href="{{ route('review.create', ['type' => 'order', 'id' => $order->id]) }}" class="text-xs text-amber-700 hover:underline">Write Review</a>
                                @endif
                                @if(in_array($order->status, ['pending','completed']))
                                    <a href="{{ route('dispute.create', ['type' => 'order', 'id' => $order->id]) }}" class="text-xs text-red-700 hover:underline">Open Dispute</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-neutral-500">No purchases yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="surface-card p-5 overflow-x-auto">
        <h2 class="text-lg font-extrabold mb-3">Rented Items</h2>
        <table class="min-w-full text-sm">
            <thead><tr><th>Product</th><th>Owner</th><th>Duration</th><th>Total Paid</th><th>Rental Dates</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse ($rentedRentals as $rental)
                    <tr>
                        <td>{{ $rental->product?->title ?? 'N/A' }}</td>
                        <td>{{ $rental->owner?->name ?? 'N/A' }}</td>
                        <td>{{ $rental->duration }} days</td>
                        <td>Rs. {{ $rental->total_amount + $rental->rent_deposit }}</td>
                        <td>{{ optional($rental->start_date)->format('Y-m-d') }} to {{ optional($rental->end_date)->format('Y-m-d') }}</td>
                        <td>
                            <div class="flex flex-col gap-1">
                                <a href="{{ route('review.create', ['type' => 'rental', 'id' => $rental->id]) }}" class="text-xs text-amber-700 hover:underline">Write Review</a>
                                <a href="{{ route('dispute.create', ['type' => 'rental', 'id' => $rental->rentalRequest?->id ?? $rental->id]) }}" class="text-xs text-red-700 hover:underline">Open Dispute</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-neutral-500">No rentals yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="surface-card p-5 overflow-x-auto">
        <h2 class="text-lg font-extrabold mb-3">Swapped Products</h2>
        <table class="min-w-full text-sm">
            <thead><tr><th>Your Product</th><th>Swapped With</th><th>Other User</th><th>Extra Cash</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse ($swaps as $swap)
                    @php
                        $isOwnerA = $swap->owner_a_id === auth()->id();
                        $yourProduct = $isOwnerA ? $swap->requestedProduct : $swap->offeredProduct;
                        $otherProduct = $isOwnerA ? $swap->offeredProduct : $swap->requestedProduct;
                        $otherUser = $isOwnerA ? $swap->ownerB : $swap->ownerA;
                    @endphp
                    <tr>
                        <td>{{ $yourProduct->title ?? 'N/A' }}</td>
                        <td>{{ $otherProduct->title ?? 'N/A' }}</td>
                        <td>{{ $otherUser?->name ?? 'N/A' }}</td>
                        <td>{{ $swap->offered_amount > 0 ? '+Rs. '.$swap->offered_amount : 'None' }}</td>
                        <td>{{ $swap->updated_at?->format('Y-m-d') ?? 'N/A' }}</td>
                        <td>
                            <div class="flex flex-col gap-1">
                                <a href="{{ route('review.create', ['type' => 'swap', 'id' => $swap->id]) }}" class="text-xs text-amber-700 hover:underline">Write Review</a>
                                <a href="{{ route('dispute.create', ['type' => 'swap', 'id' => $swap->id]) }}" class="text-xs text-red-700 hover:underline">Open Dispute</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-neutral-500">No swaps yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="surface-card p-5 overflow-x-auto">
        <h2 class="text-lg font-extrabold mb-3">Pending Rental Requests (Awaiting Owner Decision)</h2>
        <table class="min-w-full text-sm">
            <thead><tr><th>Product</th><th>Owner</th><th>Duration</th><th>Total</th><th>Requested On</th><th>Action</th></tr></thead>
            <tbody>
                @forelse ($pendingRentalRequests as $req)
                    <tr>
                        <td>{{ $req->product?->title ?? 'N/A' }}</td>
                        <td>{{ $req->owner?->name ?? 'N/A' }}</td>
                        <td>{{ $req->duration }} days</td>
                        <td>Rs. {{ number_format($req->total_amount + $req->rent_deposit, 2) }}</td>
                        <td>{{ $req->created_at->format('Y-m-d') }}</td>
                        <td><form method="POST" action="{{ route('rental.cancel', $req->id) }}" onsubmit="return confirm('Cancel this rental request?')">@csrf @method('DELETE')<button type="submit" class="text-xs text-red-700 hover:underline">Cancel</button></form></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-neutral-500">No pending rental requests.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if($approvedRentalRequests->count() > 0)
    <section class="surface-card p-5 overflow-x-auto border-l-4 border-[var(--reloop-primary)]">
        <h2 class="text-lg font-extrabold mb-3">Approved Rentals Awaiting Payment</h2>
        <table class="min-w-full text-sm">
            <thead><tr><th>Product</th><th>Owner</th><th>Duration</th><th>Total Due</th><th>Action</th></tr></thead>
            <tbody>
                @foreach ($approvedRentalRequests as $req)
                    <tr>
                        <td>{{ $req->product?->title ?? 'N/A' }}</td>
                        <td>{{ $req->owner?->name ?? 'N/A' }}</td>
                        <td>{{ $req->duration }} days</td>
                        <td>Rs. {{ number_format($req->total_amount + $req->rent_deposit, 2) }}</td>
                        <td><a href="{{ route('rental.payment', $req->id) }}" class="btn-pill btn-pill-dark !px-3 !py-1 text-xs">Pay Now</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
    @endif

    <section class="surface-card p-5 overflow-x-auto">
        <h2 class="text-lg font-extrabold mb-3">Pending Swap Requests</h2>
        <table class="min-w-full text-sm">
            <thead><tr><th>Requested Item</th><th>Offered Item</th><th>Owner</th><th>Cash Offered</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                @forelse ($pendingSwapRequests as $req)
                    <tr>
                        <td>{{ $req->product?->title ?? 'N/A' }}</td>
                        <td>{{ $req->offeredProduct?->title ?? 'Cash only' }}</td>
                        <td>{{ $req->owner?->name ?? 'N/A' }}</td>
                        <td>{{ $req->offered_amount ? 'Rs. '.$req->offered_amount : '-' }}</td>
                        <td><span class="status-chip {{ $req->status === 'countered' ? 'status-warning' : 'status-info' }}">{{ ucfirst($req->status) }}</span></td>
                        <td class="flex gap-2">
                            @if($req->status === 'countered')
                                <a href="{{ route('swap.request.show', $req->id) }}" class="text-xs text-[var(--reloop-primary)] hover:underline">View Counter</a>
                            @endif
                            <form method="POST" action="{{ route('swap.request.cancel', $req->id) }}" onsubmit="return confirm('Cancel this swap request?')">@csrf<button type="submit" class="text-xs text-red-700 hover:underline">Cancel</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-neutral-500">No pending swap requests.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
