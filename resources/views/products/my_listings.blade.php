@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-8">
    @php
        $activeProducts = $products->where('status', '!=', 'sold');
        $listedUnits = $products->sum('quantity');
        $soldUnits = $soldProducts->sum(fn($p) => $p->orders->sum(fn($o) => $o->quantity ?? 1));
        $salesRevenue = $soldProducts->sum(fn($p) => $p->orders->sum(fn($o) => ($o->unit_price ?? $p->price ?? 0) * ($o->quantity ?? 1)));
        $pendingActionCount = $pendingRequests->count() + $swapRequests->count();
    @endphp

    <section class="surface-card-strong p-6 sm:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="section-kicker">Seller Workspace</p>
                <h1 class="section-title mt-1">My Listings Dashboard</h1>
                <p class="meta-text mt-2">Track inventory, incoming requests, live rentals, and sales from one clear workspace.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('products.create') }}" class="btn-pill btn-pill-dark">Add Listing</a>
                <a href="{{ route('dashboard') }}" class="btn-pill btn-pill-soft">Main Dashboard</a>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <article class="surface-card p-4"><p class="meta-text">Active Listings</p><p class="mt-2 text-3xl font-extrabold">{{ $activeProducts->count() }}</p></article>
        <article class="surface-card p-4"><p class="meta-text">Units Listed</p><p class="mt-2 text-3xl font-extrabold">{{ $listedUnits }}</p></article>
        <article class="surface-card p-4"><p class="meta-text">Units Sold</p><p class="mt-2 text-3xl font-extrabold">{{ $soldUnits }}</p></article>
        <article class="surface-card p-4"><p class="meta-text">Sales Revenue</p><p class="mt-2 text-3xl font-extrabold text-[var(--reloop-primary-dark)]">Rs. {{ number_format($salesRevenue, 2) }}</p></article>
        <article class="surface-card p-4"><p class="meta-text">Pending Actions</p><p class="mt-2 text-3xl font-extrabold">{{ $pendingActionCount }}</p></article>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <article class="surface-card p-5 overflow-x-auto">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-extrabold">Incoming Rental Requests</h2>
                <span class="status-chip status-warning">{{ $pendingRequests->count() }} Pending</span>
            </div>
            <table class="min-w-full text-sm">
                <thead><tr><th>Product</th><th>Renter</th><th>Duration</th><th>Amount</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse ($pendingRequests as $request)
                        <tr>
                            <td>{{ $request->product->title ?? 'N/A' }}</td>
                            <td>{{ $request->renter->name ?? 'N/A' }}</td>
                            <td>{{ $request->duration }} days</td>
                            <td>Rs. {{ number_format($request->total_amount, 2) }}</td>
                            <td><a href="{{ route('rental.review', $request->id) }}" class="btn-pill btn-pill-soft !px-3 !py-1 text-xs">Review</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-[var(--reloop-ink-soft)]">No pending rental requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </article>

        <article class="surface-card p-5 overflow-x-auto">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-extrabold">Incoming Swap Requests</h2>
                <span class="status-chip status-info">{{ $swapRequests->count() }} Pending</span>
            </div>
            <table class="min-w-full text-sm">
                <thead><tr><th>Your Item</th><th>Offered Item</th><th>Requester</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse ($swapRequests as $swap)
                        @php
                            $requesterName = $swap->ownerB?->name ?? $swap->ownerA?->name ?? 'N/A';
                        @endphp
                        <tr>
                            <td>{{ $swap->requestedProduct->title ?? 'N/A' }}</td>
                            <td>{{ $swap->offeredProduct->title ?? 'N/A' }}</td>
                            <td>{{ $requesterName }}</td>
                            <td>
                                <div class="flex gap-2">
                                    <form action="{{ route('swap.request.accept', $swap->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-pill btn-pill-dark !px-3 !py-1 text-xs">Accept</button>
                                    </form>
                                    <form action="{{ route('swap.request.reject', $swap->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-pill !px-3 !py-1 text-xs !border-[var(--reloop-danger)] !text-[var(--reloop-danger)] hover:!bg-[var(--reloop-danger)] hover:!text-white">Reject</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-[var(--reloop-ink-soft)]">No pending swap requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </article>
    </section>

    <section class="surface-card p-5 overflow-x-auto">
        <h2 class="text-lg font-extrabold mb-3">Listings Inventory</h2>
        <table class="min-w-full text-sm">
            <thead><tr><th>Image</th><th>Title</th><th>Price</th><th>Quantity</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse ($activeProducts as $product)
                    <tr>
                        <td>
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}" class="h-14 w-14 object-cover border border-[var(--reloop-border)]">
                        </td>
                        <td class="font-semibold">{{ $product->title }}</td>
                        <td>{{ $product->price ? 'Rs. ' . number_format($product->price, 2) : '-' }}</td>
                        <td>{{ $product->quantity }}</td>
                        <td>
                            <form action="{{ route('products.updateStatus', $product->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="input-field !py-1.5 text-sm">
                                    <option value="available" {{ $product->status == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="sold" {{ $product->status == 'sold' ? 'selected' : '' }}>Sold</option>
                                    <option value="rented" {{ $product->status == 'rented' ? 'selected' : '' }}>Rented</option>
                                    <option value="swapped" {{ $product->status == 'swapped' ? 'selected' : '' }}>Swapped</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="{{ route('products.edit', $product->id) }}" class="btn-pill btn-pill-soft !px-3 !py-1 text-xs">Edit</a>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-pill !px-3 !py-1 text-xs !border-[var(--reloop-danger)] !text-[var(--reloop-danger)] hover:!bg-[var(--reloop-danger)] hover:!text-white">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-[var(--reloop-ink-soft)]">No active products listed yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <article class="surface-card p-5 overflow-x-auto">
            <h2 class="text-lg font-extrabold mb-3">Active Rentals</h2>
            <table class="min-w-full text-sm">
                <thead><tr><th>Product</th><th>Renter</th><th>Period</th><th>Amount</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse ($activeRentals as $rental)
                        <tr>
                            <td>{{ $rental->product->title ?? 'N/A' }}</td>
                            <td>{{ $rental->renter->name ?? 'N/A' }}</td>
                            <td>{{ optional($rental->start_date)->format('Y-m-d') }} to {{ optional($rental->end_date)->format('Y-m-d') }}</td>
                            <td>Rs. {{ number_format($rental->total_amount, 2) }}</td>
                            <td>
                                @if($rental->status === 'active')
                                    <form action="{{ route('rental.return', $rental->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn-pill btn-pill-dark !px-3 !py-1 text-xs">Mark Returned</button>
                                    </form>
                                @else
                                    <span class="status-chip status-success">{{ ucfirst($rental->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-[var(--reloop-ink-soft)]">No active rentals.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </article>

        <article class="surface-card p-5 overflow-x-auto">
            <h2 class="text-lg font-extrabold mb-3">Recent Closed Deals</h2>
            <table class="min-w-full text-sm">
                <thead><tr><th>Product</th><th>Units Sold</th><th>Revenue</th><th>Last Sale</th></tr></thead>
                <tbody>
                    @forelse ($soldProducts as $sold)
                        @php
                            $units = $sold->orders->sum(fn($o) => $o->quantity ?? 1);
                            $revenue = $sold->orders->sum(fn($o) => ($o->unit_price ?? $sold->price ?? 0) * ($o->quantity ?? 1));
                            $lastSale = $sold->orders->max('created_at');
                        @endphp
                        <tr>
                            <td>{{ $sold->title }}</td>
                            <td>{{ $units }}</td>
                            <td>Rs. {{ number_format($revenue, 2) }}</td>
                            <td>{{ $lastSale ? \Illuminate\Support\Carbon::parse($lastSale)->format('Y-m-d') : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-[var(--reloop-ink-soft)]">No sales yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </article>
    </section>

    <section class="surface-card p-5 overflow-x-auto">
        <h2 class="text-lg font-extrabold mb-3">Active Swaps</h2>
        <table class="min-w-full text-sm">
            <thead><tr><th>Your Item</th><th>Other Item</th><th>Counterparty</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                @forelse ($activeSwaps as $swap)
                    @php
                        $myOwnsRequested = ($swap->requestedProduct->user_id ?? null) === auth()->id();
                        $myItem = $myOwnsRequested ? $swap->requestedProduct : $swap->offeredProduct;
                        $otherItem = $myOwnsRequested ? $swap->offeredProduct : $swap->requestedProduct;
                        $counterparty = $myOwnsRequested ? $swap->ownerB?->name : $swap->ownerA?->name;
                    @endphp
                    <tr>
                        <td>{{ $myItem?->title ?? 'N/A' }}</td>
                        <td>{{ $otherItem?->title ?? 'N/A' }}</td>
                        <td>{{ $counterparty ?? 'N/A' }}</td>
                        <td><span class="status-chip status-success">{{ ucfirst($swap->status) }}</span></td>
                        <td>{{ $swap->created_at->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-[var(--reloop-ink-soft)]">No active swaps yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
