@extends('layouts.app')

@section('content')
<div class="px-8 md:px-16 py-12 space-y-8">
    @php
        $activeProducts = $products->where('status', '!=', 'sold');
        $listedUnits = $products->sum('quantity');
        $soldUnits = $soldProducts->sum(fn($p) => $p->orders->sum(fn($o) => $o->quantity ?? 1));
        $salesRevenue = $soldProducts->sum(fn($p) => $p->orders->sum(fn($o) => ($o->unit_price ?? $p->price ?? 0) * ($o->quantity ?? 1)));
        $pendingActionCount = $pendingRequests->count() + $swapRequests->count();
    @endphp

    <!-- Hero Section -->
    <section class="bg-[#f3f3f3] px-8 md:px-12 py-8 border-t border-b border-[rgba(189,202,189,0.3)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Seller Workspace</p>
                <h1 class="font-space font-bold text-3xl text-[#1a1c1c] mb-1">My Listings Dashboard</h1>
                <p class="font-manrope text-base text-[#444746]">Track inventory, incoming requests, live rentals, and sales from one clear workspace.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('products.create') }}" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-6 py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all">Add Listing</a>
                <a href="{{ route('dashboard') }}" class="bg-transparent border-2 border-[#006a38] text-[#006a38] px-6 py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] transition-all">Main Dashboard</a>
            </div>
        </div>
    </section>

    <!-- Stats Strip -->
    <section class="bg-[#f3f3f3] border-b border-[rgba(189,202,189,0.3)]">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-0">
            <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Active Listings</p>
                <p class="font-space font-bold text-2xl text-[#006a38] mt-1">{{ $activeProducts->count() }}</p>
            </div>
            <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Units Listed</p>
                <p class="font-space font-bold text-2xl text-[#006a38] mt-1">{{ $listedUnits }}</p>
            </div>
            <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Units Sold</p>
                <p class="font-space font-bold text-2xl text-[#006a38] mt-1">{{ $soldUnits }}</p>
            </div>
            <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Sales Revenue</p>
                <p class="font-space font-bold text-2xl text-[#006a38] mt-1">Rs. {{ number_format($salesRevenue, 2) }}</p>
            </div>
            <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Pending Actions</p>
                <p class="font-space font-bold text-2xl text-[#006a38] mt-1">{{ $pendingActionCount }}</p>
            </div>
        </div>
    </section>


    <!-- Incoming Requests Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
        <!-- Rental Requests Panel -->
        <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
            <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.2)] flex items-center justify-between">
                <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Incoming Rental Requests</h2>
                <span class="bg-[#e2e2e2] text-[#1a1c1c] text-[10px] font-space font-bold px-3 py-1.5">{{ $pendingRequests->count() }} Pending</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm font-manrope">
                    <thead>
                        <tr class="bg-[#f3f3f3] border-b border-[rgba(189,202,189,0.2)]">
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Product</th>
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Renter</th>
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Duration</th>
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Amount</th>
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingRequests as $request)
                            <tr class="border-b border-[rgba(189,202,189,0.2)] hover:bg-[#f9f9f9]">
                                <td class="px-4 py-3">{{ $request->product->title ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $request->renter->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $request->duration }} days</td>
                                <td class="px-4 py-3">Rs. {{ number_format($request->total_amount, 2) }}</td>
                                <td class="px-4 py-3"><a href="{{ route('rental.review', $request->id) }}" class="bg-transparent border-2 border-[#006a38] text-[#006a38] px-3 py-1.5 font-space text-[11px] font-bold uppercase hover:bg-[rgba(0,106,56,0.06)] inline-block">Review</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-3 text-center text-[#444746]">No pending rental requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Swap Requests Panel -->
        <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
            <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.2)] flex items-center justify-between">
                <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Incoming Swap Requests</h2>
                <span class="bg-[#e2e2e2] text-[#1a1c1c] text-[10px] font-space font-bold px-3 py-1.5">{{ $swapRequests->count() }} Pending</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm font-manrope">
                    <thead>
                        <tr class="bg-[#f3f3f3] border-b border-[rgba(189,202,189,0.2)]">
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Your Item</th>
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Offered Item</th>
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Requester</th>
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($swapRequests as $swap)
                            @php
                                $requesterName = $swap->ownerB?->name ?? $swap->ownerA?->name ?? 'N/A';
                            @endphp
                            <tr class="border-b border-[rgba(189,202,189,0.2)] hover:bg-[#f9f9f9]">
                                <td class="px-4 py-3">{{ $swap->requestedProduct->title ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $swap->offeredProduct->title ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $requesterName }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <form action="{{ route('swap.request.accept', $swap->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-3 py-1.5 font-space text-[11px] font-bold uppercase hover:brightness-110 inline-block">Accept</button>
                                        </form>
                                        <form action="{{ route('swap.request.reject', $swap->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-transparent border-2 border-[#ba1a1a] text-[#ba1a1a] px-3 py-1.5 font-space text-[11px] font-bold uppercase hover:bg-[rgba(186,26,26,0.06)] inline-block">Reject</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-3 text-center text-[#444746]">No pending swap requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Listings Inventory Panel -->
    <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
        <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.2)]">
            <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Listings Inventory</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm font-manrope">
                <thead>
                    <tr class="bg-[#f3f3f3] border-b border-[rgba(189,202,189,0.2)]">
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Image</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Title</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Price</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Quantity</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Status</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activeProducts as $product)
                        <tr class="border-b border-[rgba(189,202,189,0.2)] hover:bg-[#f9f9f9]">
                            <td class="px-4 py-3"><img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}" class="h-14 w-14 object-cover"></td>
                            <td class="px-4 py-3">{{ $product->title }}</td>
                            <td class="px-4 py-3">{{ $product->price ? 'Rs. ' . number_format($product->price, 2) : '-' }}</td>
                            <td class="px-4 py-3">{{ $product->quantity }}</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('products.updateStatus', $product->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-2 py-1.5 text-sm font-manrope text-[#1a1c1c] focus:border-[#006a38] focus:outline-none">
                                        <option value="available" {{ $product->status == 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="sold" {{ $product->status == 'sold' ? 'selected' : '' }}>Sold</option>
                                        <option value="rented" {{ $product->status == 'rented' ? 'selected' : '' }}>Rented</option>
                                        <option value="swapped" {{ $product->status == 'swapped' ? 'selected' : '' }}>Swapped</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('products.edit', $product->id) }}" class="bg-transparent border-2 border-[#006a38] text-[#006a38] px-3 py-1.5 font-space text-[11px] font-bold uppercase hover:bg-[rgba(0,106,56,0.06)] inline-block">Edit</a>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-transparent border-2 border-[#ba1a1a] text-[#ba1a1a] px-3 py-1.5 font-space text-[11px] font-bold uppercase hover:bg-[rgba(186,26,26,0.06)] inline-block">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-3 text-center text-[#444746]">No active products listed yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Active Rentals & Recent Deals Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
        <!-- Active Rentals Panel -->
        <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
            <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.2)]">
                <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Active Rentals</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm font-manrope">
                    <thead>
                        <tr class="bg-[#f3f3f3] border-b border-[rgba(189,202,189,0.2)]">
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Product</th>
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Renter</th>
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Period</th>
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Amount</th>
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activeRentals as $rental)
                            <tr class="border-b border-[rgba(189,202,189,0.2)] hover:bg-[#f9f9f9]">
                                <td class="px-4 py-3">{{ $rental->product->title ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $rental->renter->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ optional($rental->start_date)->format('Y-m-d') }} to {{ optional($rental->end_date)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">Rs. {{ number_format($rental->total_amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    @if($rental->status === 'active')
                                        <form action="{{ route('rental.return', $rental->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-3 py-1.5 font-space text-[11px] font-bold uppercase hover:brightness-110 inline-block">Mark Returned</button>
                                        </form>
                                    @else
                                        <span class="bg-[#e2e2e2] text-[#006a38] text-[10px] font-space font-bold px-3 py-1.5">{{ ucfirst($rental->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-3 text-center text-[#444746]">No active rentals.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Closed Deals Panel -->
        <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
            <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.2)]">
                <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Recent Closed Deals</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm font-manrope">
                    <thead>
                        <tr class="bg-[#f3f3f3] border-b border-[rgba(189,202,189,0.2)]">
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Product</th>
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Units Sold</th>
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Revenue</th>
                            <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Last Sale</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($soldProducts as $sold)
                            @php
                                $units = $sold->orders->sum(fn($o) => $o->quantity ?? 1);
                                $revenue = $sold->orders->sum(fn($o) => ($o->unit_price ?? $sold->price ?? 0) * ($o->quantity ?? 1));
                                $lastSale = $sold->orders->max('created_at');
                            @endphp
                            <tr class="border-b border-[rgba(189,202,189,0.2)] hover:bg-[#f9f9f9]">
                                <td class="px-4 py-3">{{ $sold->title }}</td>
                                <td class="px-4 py-3">{{ $units }}</td>
                                <td class="px-4 py-3">Rs. {{ number_format($revenue, 2) }}</td>
                                <td class="px-4 py-3">{{ $lastSale ? \Illuminate\Support\Carbon::parse($lastSale)->format('Y-m-d') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-3 text-center text-[#444746]">No sales yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Active Swaps Panel -->
    <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
        <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.2)]">
            <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Active Swaps</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm font-manrope">
                <thead>
                    <tr class="bg-[#f3f3f3] border-b border-[rgba(189,202,189,0.2)]">
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Your Item</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Other Item</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Counterparty</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Status</th>
                        <th class="px-4 py-3 text-left font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activeSwaps as $swap)
                        @php
                            $myOwnsRequested = ($swap->requestedProduct->user_id ?? null) === auth()->id();
                            $myItem = $myOwnsRequested ? $swap->requestedProduct : $swap->offeredProduct;
                            $otherItem = $myOwnsRequested ? $swap->offeredProduct : $swap->requestedProduct;
                            $counterparty = $myOwnsRequested ? $swap->ownerB?->name : $swap->ownerA?->name;
                        @endphp
                        <tr class="border-b border-[rgba(189,202,189,0.2)] hover:bg-[#f9f9f9]">
                            <td class="px-4 py-3">{{ $myItem?->title ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $otherItem?->title ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $counterparty ?? 'N/A' }}</td>
                            <td class="px-4 py-3"><span class="bg-[#e2e2e2] text-[#006a38] text-[10px] font-space font-bold px-3 py-1.5">{{ ucfirst($swap->status) }}</span></td>
                            <td class="px-4 py-3">{{ $swap->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-3 text-center text-[#444746]">No active swaps yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
