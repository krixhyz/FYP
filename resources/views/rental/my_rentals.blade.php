@extends('layouts.dashboard')

@section('content')
<!-- Header Section -->
<section class="px-0 md:px-8 py-8">
    <div>
        <p class="font-space text-[12px] font-bold uppercase tracking-widest text-[#888] mb-2">Buyer Workspace</p>
        <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-2">My Rentals</h1>
        <p class="font-manrope text-base text-[#444746]">View your active and past rentals.</p>
    </div>
</section>

<!-- Quick Stats -->
<section class="px-0 md:px-8 py-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
    @php
        $activeCount = $rentals->where('status', 'active')->count();
        $completedCount = $rentals->whereIn('status', ['completed', 'returned'])->count();
        $totalSpent = $rentals->sum(fn($r) => $r->total_amount + $r->rent_deposit);
        $rentedItemsCount = $rentedItems->count();
        $ownerCompletedCount = $ownerCompletedItems->count();
        $incomingCount = $incomingRequests->count();
    @endphp
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Active Rentals</p>
        <p class="font-space font-bold text-3xl text-[#006a38]">{{ $activeCount }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Completed</p>
        <p class="font-space font-bold text-3xl text-[#006a38]">{{ $completedCount }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Total Spent</p>
        <p class="font-space font-bold text-2xl text-[#006a38]">Rs. {{ number_format($totalSpent, 0) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Incoming Requests</p>
        <p class="font-space font-bold text-3xl text-[#d97706]">{{ $incomingCount }}</p>
    </div>
</section>

<!-- Tab Navigation -->
<div id="tabs" class="px-0 md:px-8 py-6 border-b border-[rgba(189,202,189,0.1)] flex gap-8">
    <button class="tab-button active font-space font-bold text-sm uppercase tracking-widest text-[#1a1c1c] pb-3 border-b-2 border-[#006a38] cursor-pointer" data-tab="active">
        Active ({{ $activeCount }})
    </button>
    <button class="tab-button font-space font-bold text-sm uppercase tracking-widest text-[#888] pb-3 border-b-2 border-transparent hover:text-[#1a1c1c] cursor-pointer transition-colors" data-tab="completed">
        Completed ({{ $completedCount }})
    </button>
    <button class="tab-button font-space font-bold text-sm uppercase tracking-widest text-[#888] pb-3 border-b-2 border-transparent hover:text-[#1a1c1c] cursor-pointer transition-colors" data-tab="incoming">
        Incoming ({{ $incomingCount }})
    </button>
    <button class="tab-button font-space font-bold text-sm uppercase tracking-widest text-[#888] pb-3 border-b-2 border-transparent hover:text-[#1a1c1c] cursor-pointer transition-colors" data-tab="rented-items">
        Rented Items ({{ $rentedItemsCount }})
    </button>
    <button class="tab-button font-space font-bold text-sm uppercase tracking-widest text-[#888] pb-3 border-b-2 border-transparent hover:text-[#1a1c1c] cursor-pointer transition-colors" data-tab="owner-completed-items">
        Owner Completed ({{ $ownerCompletedCount }})
    </button>
</div>

<!-- Active Rentals Tab -->
<section id="active" class="tab-content px-0 md:px-8 py-6">
    @php
        $activeRentals = $rentals->where('status', 'active');
    @endphp
    
    @if($activeRentals->count() > 0)
        <div class="space-y-4">
            @foreach($activeRentals as $rental)
                <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all cursor-pointer group">
                    <a href="{{ route('rental.show', $rental) }}" class="flex items-start gap-4">
                        <!-- Product Image -->
                        <div class="w-24 h-24 bg-[#e2e2e2] rounded-lg overflow-hidden flex-shrink-0">
                            @php
                                $productImage = collect($rental->product?->images ?? [])->first();
                                $productImagePath = data_get($productImage, 'path', is_string($productImage) ? $productImage : null) ?? $rental->product?->image;
                            @endphp
                            @if($productImagePath)
                                <img src="{{ asset('storage/' . $productImagePath) }}" alt="{{ $rental->product->title ?? 'Product' }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-[#888]">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Rental Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <div>
                                    <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-1 group-hover:text-[#006a38] transition-colors">{{ $rental->product?->title ?? 'Product' }}</h3>
                                    <p class="text-sm text-[#888]">Owner: {{ $rental->owner?->name ?? 'N/A' }}</p>
                                </div>
                                <span class="text-[11px] font-space font-bold px-3 py-1 rounded flex-shrink-0 whitespace-nowrap bg-[#d4edda] text-[#155724]">
                                    Active
                                </span>
                            </div>

                            <div class="grid grid-cols-4 gap-4 mb-4 pb-4 border-b border-[rgba(189,202,189,0.1)]">
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Duration</p>
                                    <p class="font-space font-bold text-sm text-[#006a38]">{{ $rental->duration ?? 'N/A' }} days</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">{{ ($rental->rent_type ?? 'daily') === 'hourly' ? 'Hourly Rate' : 'Daily Rate' }}</p>
                                    <p class="font-space font-bold text-sm text-[#006a38]">{{ (($rental->rent_fare ?? $rental->rental?->rent_fare) !== null) ? 'Rs. ' . number_format((float) ($rental->rent_fare ?? $rental->rental?->rent_fare), 0) : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Rental Period</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ optional($rental->start_date)->format('M d') }} - {{ optional($rental->end_date)->format('M d') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Deposit</p>
                                    <p class="font-space font-bold text-sm text-[#006a38]">Rs. {{ number_format((float) ($rental->deposit?->amount ?? $rental->rent_deposit ?? 0), 0) }}</p>
                                    <p class="text-[11px] text-[#888] mt-1">Refund: <span class="capitalize text-[#1a1c1c]">{{ $rental->deposit?->refund_status ?? 'pending' }}</span></p>
                                </div>
                            </div>

                            <p class="text-[11px] text-[#006a38] font-space font-bold uppercase tracking-widest group-hover:underline">View Details</p>
                        </div>
                    </a>

                    <!-- Actions (Outside Link) -->
                    <div class="flex gap-2 flex-wrap mt-4 pt-4 border-t border-[rgba(189,202,189,0.1)]">
                                @if(!$rental->return_requested_at)
                                    <form action="{{ route('rental.requestReturn', $rental) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-[#006a38] text-white px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#004a29] transition-all">Mark Returned</button>
                                    </form>
                                @else
                                    <span class="inline-flex items-center bg-[#fff3cd] text-[#856404] px-4 py-2 font-space text-[10px] font-bold uppercase rounded">Return Requested</span>
                                @endif
                        <a href="{{ route('review.create', ['type' => 'rental', 'id' => $rental->id]) }}" class="bg-[#f0f8f5] border border-[#d97706] text-[#d97706] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(217,119,6,0.06)] transition-all">Leave Review</a>
                        <a href="{{ route('dispute.create', ['type' => 'rental', 'id' => $rental->id]) }}" class="bg-[#f9f9f9] border border-[#ba1a1a] text-[#ba1a1a] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(186,26,26,0.06)] transition-all">Report Issue</a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-12 text-center">
            <svg class="w-16 h-16 text-[#ccc] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
            </svg>
            <p class="font-manrope text-base text-[#888]">No active rentals</p>
        </div>
    @endif
</section>

<!-- Completed Rentals Tab -->
<section id="completed" class="tab-content hidden px-0 md:px-8 py-6">
    @php
        $completedRentals = $rentals->whereIn('status', ['completed', 'returned']);
    @endphp
    
    @if($completedRentals->count() > 0)
        <div class="space-y-4">
            @foreach($completedRentals as $rental)
                <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all cursor-pointer group">
                    <a href="{{ route('rental.show', $rental) }}" class="flex items-start gap-4">
                        <!-- Product Image -->
                        <div class="w-24 h-24 bg-[#e2e2e2] rounded-lg overflow-hidden flex-shrink-0">
                            @php
                                $productImage = collect($rental->product?->images ?? [])->first();
                                $productImagePath = data_get($productImage, 'path', is_string($productImage) ? $productImage : null) ?? $rental->product?->image;
                            @endphp
                            @if($productImagePath)
                                <img src="{{ asset('storage/' . $productImagePath) }}" alt="{{ $rental->product->title ?? 'Product' }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-[#888]">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Rental Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <div>
                                    <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-1 group-hover:text-[#006a38] transition-colors">{{ $rental->product?->title ?? 'Product' }}</h3>
                                    <p class="text-sm text-[#888]">Owner: {{ $rental->owner?->name ?? 'N/A' }}</p>
                                </div>
                                <span class="text-[11px] font-space font-bold px-3 py-1 rounded flex-shrink-0 whitespace-nowrap bg-[#e2e2e2] text-[#666]">
                                    Completed
                                </span>
                            </div>

                            <div class="grid grid-cols-4 gap-4 mb-4 pb-4 border-b border-[rgba(189,202,189,0.1)]">
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Duration</p>
                                    <p class="font-space font-bold text-sm text-[#006a38]">{{ $rental->duration ?? 'N/A' }} days</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">{{ ($rental->rent_type ?? 'daily') === 'hourly' ? 'Hourly Rate' : 'Daily Rate' }}</p>
                                    <p class="font-space font-bold text-sm text-[#006a38]">{{ (($rental->rent_fare ?? $rental->rental?->rent_fare) !== null) ? 'Rs. ' . number_format((float) ($rental->rent_fare ?? $rental->rental?->rent_fare), 0) : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Returned</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ optional($rental->returned_at)->format('M d, Y') ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Deposit Outcome</p>
                                    <p class="font-space font-bold text-sm text-[#006a38]">Rs. {{ number_format((float) ($rental->deposit?->refund_amount ?? 0), 0) }}</p>
                                    <p class="text-[11px] text-[#888] mt-1">Refund: <span class="capitalize text-[#1a1c1c]">{{ $rental->deposit?->refund_status ?? 'pending' }}</span></p>
                                </div>
                            </div>

                            <p class="text-[11px] text-[#006a38] font-space font-bold uppercase tracking-widest group-hover:underline">View Details</p>
                        </div>
                    </a>

                    <!-- Actions (Outside Link) -->
                    <div class="flex gap-2 flex-wrap mt-4 pt-4 border-t border-[rgba(189,202,189,0.1)]">
                        <a href="{{ route('review.create', ['type' => 'rental', 'id' => $rental->id]) }}" class="bg-[#f0f8f5] border border-[#d97706] text-[#d97706] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(217,119,6,0.06)] transition-all">Leave Review</a>
                        <a href="{{ route('dispute.create', ['type' => 'rental', 'id' => $rental->id]) }}" class="bg-[#f9f9f9] border border-[#ba1a1a] text-[#ba1a1a] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(186,26,26,0.06)] transition-all">Report Issue</a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-12 text-center">
            <svg class="w-16 h-16 text-[#ccc] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
            </svg>
            <p class="font-manrope text-base text-[#888]">No completed rentals</p>
        </div>
    @endif
</section>

<!-- Incoming Rental Requests Tab -->
<section id="incoming" class="tab-content hidden px-0 md:px-8 py-6">
    @if($incomingRequests->count() > 0)
        <div class="space-y-4">
            @foreach($incomingRequests as $req)
                <div id="incoming-request-{{ $req->id }}" class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Product Details -->
                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9]">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-3">Product Being Rented</p>
                            <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-3">{{ $req->product->title }}</h3>
                            @php
                                $requestProductImage = collect($req->product->images ?? [])->first();
                                $requestProductImagePath = data_get($requestProductImage, 'path', is_string($requestProductImage) ? $requestProductImage : null) ?? $req->product->image;
                            @endphp
                            @if($requestProductImagePath)
                                <img src="{{ asset('storage/' . $requestProductImagePath) }}" alt="{{ $req->product->title }}" class="w-full h-40 object-cover rounded-lg mb-3">
                            @else
                                <div class="w-full h-40 bg-[#e2e2e2] rounded-lg flex items-center justify-center text-[#888] mb-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                </div>
                            @endif
                            <p class="font-manrope text-sm text-[#666] line-clamp-2">{{ Str::limit($req->product->description, 100) }}</p>
                        </div>

                        <!-- Rental Details -->
                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9]">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-3">Rental Period</p>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Start Date</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ \Carbon\Carbon::parse($req->start_date)->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">End Date</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ \Carbon\Carbon::parse($req->end_date)->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Duration</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ $req->duration }} days</p>
                                </div>
                                <div class="pt-2 border-t border-[rgba(189,202,189,0.1)]">
                                    <p class="text-xs text-[#888] mb-1">Total Amount</p>
                                    <p class="font-space font-bold text-lg text-[#006a38]">Rs. {{ number_format($req->total_amount, 2) }}</p>
                                </div>
                                @if($req->rent_deposit > 0)
                                    <div>
                                        <p class="text-xs text-[#888] mb-1">Security Deposit</p>
                                        <p class="font-space font-bold text-sm text-[#d97706]">Rs. {{ number_format($req->rent_deposit, 2) }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Renter Info & Actions -->
                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9] flex flex-col">
                            <div class="mb-6">
                                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-2">Requested By</p>
                                <p class="font-space font-bold text-lg text-[#1a1c1c] mb-3">
                                    <a href="{{ route('users.show', $req->renter) }}" class="text-[#006a38] hover:underline">{{ $req->renter->name }}</a>
                                </p>
                                <div class="text-xs text-[#888] space-y-1">
                                    <p><strong>Email:</strong> {{ $req->renter->email }}</p>
                                    @if($req->renter->phone)
                                        <p><strong>Phone:</strong> {{ $req->renter->phone }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-auto space-y-2">
                                <a href="{{ route('rental.myRentals', ['tab' => 'incoming', 'request' => $req->id]) }}" class="block w-full bg-[#006a38] text-white px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#004a29] transition-all text-center">Open In Incoming Tab</a>
                                <form action="{{ route('rental.approve', $req->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full bg-[#d4edda] text-[#155724] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#c3e6cb] transition-all">Approve</button>
                                </form>
                                <form action="{{ route('rental.reject', $req->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full bg-[#f8d7da] text-[#721c24] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#f5c6cb] transition-all">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-12 text-center">
            <svg class="w-16 h-16 text-[#ccc] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="font-manrope text-base text-[#888]">No incoming rental requests</p>
        </div>
    @endif
</section>

<!-- Rented Items Tab -->
<section id="rented-items" class="tab-content hidden px-0 md:px-8 py-6">
    @if($rentedItems->count() > 0)
        <div class="space-y-4">
            @foreach($rentedItems as $rental)
                <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9]">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-3">Rented Item</p>
                            <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-2">{{ $rental->product?->title ?? 'Product' }}</h3>
                            <p class="font-manrope text-sm text-[#666] line-clamp-3">{{ Str::limit($rental->product?->description, 100) }}</p>
                        </div>

                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9]">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-3">Rental Period</p>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Start Date</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ optional($rental->start_date)->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">End Date</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ optional($rental->end_date)->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Duration</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ $rental->duration }} days</p>
                                </div>
                            </div>
                        </div>

                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9] flex flex-col">
                            <div class="mb-6">
                                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-2">Rented By</p>
                                <p class="font-space font-bold text-lg text-[#1a1c1c] mb-3">
                                    <a href="{{ route('users.show', $rental->renter) }}" class="text-[#006a38] hover:underline">{{ $rental->renter?->name ?? 'N/A' }}</a>
                                </p>
                                <div class="text-xs text-[#888] space-y-1">
                                    <p><strong>Email:</strong> {{ $rental->renter?->email ?? 'N/A' }}</p>
                                    @if($rental->renter?->phone)
                                        <p><strong>Phone:</strong> {{ $rental->renter->phone }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-auto space-y-2">
                                <a href="{{ route('rental.show', $rental) }}" class="block w-full bg-[#006a38] text-white px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#004a29] transition-all text-center">View Details</a>
                                @if($rental->return_requested_at && $rental->status === 'active')
                                    <a href="{{ route('dispute.create', ['type' => 'rental', 'id' => $rental->id]) }}" class="block w-full bg-[#f8d7da] text-[#721c24] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(186,26,26,0.06)] transition-all text-center">Report Damage to Admin</a>
                                    <form action="{{ route('rental.return', $rental) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-full bg-[#f9f9f9] border border-[#006a38] text-[#006a38] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(0,106,56,0.06)] transition-all">Confirm Returned</button>
                                    </form>
                                @elseif($rental->status === 'active')
                                    <p class="text-xs text-[#888]">Waiting for the renter to request return.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-12 text-center">
            <svg class="w-16 h-16 text-[#ccc] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
            </svg>
            <p class="font-manrope text-base text-[#888]">No rented items yet</p>
        </div>
    @endif
</section>

<!-- Owner Completed Items Tab -->
<section id="owner-completed-items" class="tab-content hidden px-0 md:px-8 py-6">
    @if($ownerCompletedItems->count() > 0)
        <div class="space-y-4">
            @foreach($ownerCompletedItems as $rental)
                <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9]">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-3">Rented Item</p>
                            <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-2">{{ $rental->product?->title ?? 'Product' }}</h3>
                            <p class="font-manrope text-sm text-[#666] line-clamp-3">{{ Str::limit($rental->product?->description, 100) }}</p>
                        </div>

                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9]">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-3">Completion Summary</p>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Returned At</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ optional($rental->returned_at)->format('M d, Y') ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Deposit Refund</p>
                                    <p class="font-space font-bold text-sm text-[#006a38]">Rs. {{ number_format((float) ($rental->deposit?->refund_amount ?? 0), 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Refund Status</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c] capitalize">{{ $rental->deposit?->refund_status ?? 'pending' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9] flex flex-col">
                            <div class="mb-6">
                                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-2">Renter</p>
                                <p class="font-space font-bold text-lg text-[#1a1c1c] mb-3">
                                    <a href="{{ route('users.show', $rental->renter) }}" class="text-[#006a38] hover:underline">{{ $rental->renter?->name ?? 'N/A' }}</a>
                                </p>
                                <div class="text-xs text-[#888] space-y-1">
                                    <p><strong>Email:</strong> {{ $rental->renter?->email ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="mt-auto space-y-2">
                                <a href="{{ route('rental.show', $rental) }}" class="block w-full bg-[#006a38] text-white px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#004a29] transition-all text-center">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-12 text-center">
            <svg class="w-16 h-16 text-[#ccc] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
            </svg>
            <p class="font-manrope text-base text-[#888]">No completed rentals under owner account yet</p>
        </div>
    @endif
</section>

<div class="h-8"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const initialTab = params.get('tab');
    const requestId = params.get('request');

    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    const activateTab = (tabId) => {
        tabContents.forEach(content => content.classList.add('hidden'));
        tabButtons.forEach(btn => {
            btn.classList.remove('text-[#1a1c1c]', 'border-[#006a38]');
            btn.classList.add('text-[#888]', 'border-transparent');
        });

        const selectedContent = document.getElementById(tabId);
        const selectedButton = document.querySelector(`.tab-button[data-tab="${tabId}"]`);
        if (selectedContent) {
            selectedContent.classList.remove('hidden');
        }
        if (selectedButton) {
            selectedButton.classList.remove('text-[#888]', 'border-transparent');
            selectedButton.classList.add('text-[#1a1c1c]', 'border-[#006a38]');
        }
    };

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            activateTab(tabId);
        });
    });

    if (initialTab && document.querySelector(`.tab-button[data-tab="${initialTab}"]`)) {
        activateTab(initialTab);
    }

    if (initialTab === 'incoming' && requestId) {
        const target = document.getElementById(`incoming-request-${requestId}`);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            target.classList.add('ring-2', 'ring-[#006a38]');
            setTimeout(() => target.classList.remove('ring-2', 'ring-[#006a38]'), 2000);
        }
    }
});
</script>
@endsection
