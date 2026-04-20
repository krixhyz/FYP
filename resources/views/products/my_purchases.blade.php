@extends('layouts.dashboard')

@section('content')
@php
    $orderReviewedIds = $orderReviewedIds ?? [];
    $rentalReviewedIds = $rentalReviewedIds ?? [];
    $swapReviewedIds = $swapReviewedIds ?? [];

    $completedOrders = $orders->where('status', 'completed');
    $pendingOrders = $orders->where('status', 'pending');
    $totalSpent = $completedOrders->sum(fn($o) => $o->total_price ?? (($o->unit_price ?? $o->product?->price ?? 0) * ($o->quantity ?? 1)));

    $resolveImagePath = function ($product) {
        if (!$product) {
            return null;
        }

        $images = $product->images ?? null;

        // Case: relation collection with image models (expects ->path)
        if ($images instanceof \Illuminate\Support\Collection) {
            $first = $images->first();
            if (is_object($first)) {
                return $first->path ?? null;
            }
            if (is_string($first)) {
                return $first;
            }
        }

        // Case: JSON-cast array of image paths or objects
        if (is_array($images) && !empty($images)) {
            $first = $images[0] ?? null;
            if (is_array($first)) {
                return $first['path'] ?? null;
            }
            if (is_object($first)) {
                return $first->path ?? null;
            }
            if (is_string($first)) {
                return $first;
            }
        }

        // Case: single image fallback
        if (!empty($product->image) && is_string($product->image)) {
            return $product->image;
        }

        return null;
    };
@endphp

<!-- Header Section -->
<section class="px-0 md:px-8 py-8">
    <div>
        <p class="font-space text-[12px] font-bold uppercase tracking-widest text-[#888] mb-2">Buyer Workspace</p>
        <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-2">My Purchases</h1>
        <p class="font-manrope text-base text-[#444746]">Track your orders, rentals, and swaps in one place.</p>
    </div>
</section>

<!-- Quick Stats -->
<section class="px-0 md:px-8 py-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Completed Orders</p>
        <p class="font-space font-bold text-3xl text-[#006a38]">{{ $completedOrders->count() }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Pending Orders</p>
        <p class="font-space font-bold text-3xl text-[#006a38]">{{ $pendingOrders->count() }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Active Rentals</p>
        <p class="font-space font-bold text-3xl text-[#006a38]">{{ $rentedRentals->count() }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Total Spent</p>
        <p class="font-space font-bold text-3xl text-[#006a38]">Rs. {{ number_format($totalSpent, 0) }}</p>
    </div>
</section>

<!-- Tabbed Navigation -->
<div id="tabs" class="px-0 md:px-8 py-6 border-b border-[rgba(189,202,189,0.1)] flex gap-8">
    <button class="tab-button active font-space font-bold text-sm uppercase tracking-widest text-[#1a1c1c] pb-3 border-b-2 border-[#006a38] cursor-pointer" data-tab="orders">
        All Orders ({{ $orders->count() }})
    </button>
    <button class="tab-button font-space font-bold text-sm uppercase tracking-widest text-[#888] pb-3 border-b-2 border-transparent hover:text-[#1a1c1c] cursor-pointer transition-colors" data-tab="rentals">
        Rentals ({{ $rentedRentals->count() }})
    </button>
    <button class="tab-button font-space font-bold text-sm uppercase tracking-widest text-[#888] pb-3 border-b-2 border-transparent hover:text-[#1a1c1c] cursor-pointer transition-colors" data-tab="swaps">
        Swaps ({{ $swaps->count() }})
    </button>
</div>

<!-- Orders Tab -->
<section id="orders" class="tab-content px-0 md:px-8 py-6">
    @if($orders->count() > 0)
        <div class="space-y-4">
            @foreach($orders as $order)
                @php
                    $qty = $order->quantity ?? 1;
                    $unit = $order->unit_price ?? $order->product?->price ?? 0;
                    $total = $order->total_price ?? ($qty * $unit);
                    $orderImagePath = $resolveImagePath($order->product);
                @endphp
                <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                    <div class="flex items-start gap-4">
                        <!-- Product Image -->
                        <div class="w-24 h-24 bg-[#e2e2e2] rounded-lg overflow-hidden flex-shrink-0">
                            @if($orderImagePath)
                                <img src="{{ asset('storage/' . $orderImagePath) }}" alt="{{ $order->product->title ?? 'Product' }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-[#888]">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Order Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <div>
                                    <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-1">{{ $order->product?->title ?? 'Product' }}</h3>
                                    <p class="text-sm text-[#888]">Order ID: #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
                                </div>
                                <span class="text-[11px] font-space font-bold px-3 py-1 rounded flex-shrink-0 whitespace-nowrap {{ $order->status === 'pending' ? 'bg-[#ffd580] text-[#664d03]' : ($order->status === 'completed' ? 'bg-[#d4edda] text-[#155724]' : 'bg-[#f8d7da] text-[#721c24]') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-4 gap-4 mb-4 pb-4 border-b border-[rgba(189,202,189,0.1)]">
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Unit Price</p>
                                    <p class="font-space font-bold text-sm text-[#006a38]">Rs. {{ number_format($unit, 0) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Quantity</p>
                                    <p class="font-space font-bold text-sm text-[#006a38]">{{ $qty }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Total</p>
                                    <p class="font-space font-bold text-sm text-[#006a38]">Rs. {{ number_format($total, 0) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Date</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ $order->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 flex-wrap">
                                @if($order->status === 'pending')
                                    <a href="{{ route('order.checkout', $order->id) }}" class="bg-[#006a38] text-white px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#004a29] transition-all">Checkout</a>
                                    <form method="POST" action="{{ route('order.cancel', $order->id) }}" onsubmit="return confirm('Cancel this order?')" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-[#f9f9f9] border border-[#ba1a1a] text-[#ba1a1a] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(186,26,26,0.06)] transition-all">Cancel</button>
                                    </form>
                                @endif
                                @if($order->status === 'completed')
                                    @if(in_array((int) $order->id, $orderReviewedIds, true))
                                        <button type="button" disabled class="cursor-not-allowed bg-[#f3f3f3] border border-[#cfcfcf] text-[#888888] px-4 py-2 font-space text-[10px] font-bold uppercase rounded">Reviewed</button>
                                    @else
                                        <a href="{{ route('review.create', ['type' => 'order', 'id' => $order->id]) }}" class="bg-[#f0f8f5] border border-[#d97706] text-[#d97706] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(217,119,6,0.06)] transition-all">Leave Review</a>
                                    @endif
                                @endif
                                @if(in_array($order->status, ['pending','completed']))
                                    <a href="{{ route('dispute.create', ['type' => 'order', 'id' => $order->id]) }}" class="bg-[#f9f9f9] border border-[#ba1a1a] text-[#ba1a1a] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(186,26,26,0.06)] transition-all">Report Issue</a>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            <p class="font-manrope text-base text-[#888]">No orders yet</p>
        </div>
    @endif
</section>

<!-- Rentals Tab -->
<section id="rentals" class="tab-content hidden px-0 md:px-8 py-6">
    @if($rentedRentals->count() > 0)
        <div class="space-y-4">
            @foreach($rentedRentals as $rental)
                @php $rentalImagePath = $resolveImagePath($rental->product); @endphp
                <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                    <div class="flex items-start gap-4">
                        <!-- Product Image -->
                        <div class="w-24 h-24 bg-[#e2e2e2] rounded-lg overflow-hidden flex-shrink-0">
                            @if($rentalImagePath)
                                <img src="{{ asset('storage/' . $rentalImagePath) }}" alt="{{ $rental->product->title ?? 'Product' }}" class="w-full h-full object-cover">
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
                                    <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-1">{{ $rental->product?->title ?? 'Product' }}</h3>
                                    <p class="text-sm text-[#888]">Owner: {{ $rental->owner?->name ?? 'N/A' }}</p>
                                </div>
                                <span class="text-[11px] font-space font-bold px-3 py-1 rounded flex-shrink-0 whitespace-nowrap bg-[#d4edda] text-[#155724]">
                                    Active
                                </span>
                            </div>

                            <div class="grid grid-cols-3 gap-4 mb-4 pb-4 border-b border-[rgba(189,202,189,0.1)]">
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Duration</p>
                                    <p class="font-space font-bold text-sm text-[#006a38]">{{ $rental->duration ?? 'N/A' }} days</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Total Paid</p>
                                    <p class="font-space font-bold text-sm text-[#006a38]">Rs. {{ number_format($rental->total_amount + $rental->rent_deposit, 0) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Period</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ optional($rental->start_date)->format('M d') }} - {{ optional($rental->end_date)->format('M d') }}</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 flex-wrap">
                                @if(in_array((int) $rental->id, $rentalReviewedIds, true))
                                    <button type="button" disabled class="cursor-not-allowed bg-[#f3f3f3] border border-[#cfcfcf] text-[#888888] px-4 py-2 font-space text-[10px] font-bold uppercase rounded">Reviewed</button>
                                @else
                                    <a href="{{ route('review.create', ['type' => 'rental', 'id' => $rental->id]) }}" class="bg-[#f0f8f5] border border-[#d97706] text-[#d97706] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(217,119,6,0.06)] transition-all">Leave Review</a>
                                @endif
                                <a href="{{ route('dispute.create', ['type' => 'rental', 'id' => $rental->id]) }}" class="bg-[#f9f9f9] border border-[#ba1a1a] text-[#ba1a1a] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(186,26,26,0.06)] transition-all">Report Issue</a>
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
            <p class="font-manrope text-base text-[#888]">No active rentals</p>
        </div>
    @endif
</section>

<!-- Swaps Tab -->
<section id="swaps" class="tab-content hidden px-0 md:px-8 py-6">
    @if($swaps->count() > 0)
        <div class="space-y-4">
            @foreach($swaps as $swap)
                @php
                    $offeredImagePath = $resolveImagePath($swap->offeredProduct);
                    $requestedImagePath = $resolveImagePath($swap->requestedProduct);
                @endphp
                <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                    <div class="flex items-start gap-4">
                        <!-- Swap Items Images -->
                        <div class="flex gap-2 flex-shrink-0">
                            <div class="w-24 h-24 bg-[#e2e2e2] rounded-lg overflow-hidden">
                                @if($offeredImagePath)
                                    <img src="{{ asset('storage/' . $offeredImagePath) }}" alt="{{ $swap->offeredProduct->title ?? 'Item' }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[#888]">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center justify-center px-2 text-[#888]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4"></path>
                                </svg>
                            </div>
                            <div class="w-24 h-24 bg-[#e2e2e2] rounded-lg overflow-hidden">
                                @if($requestedImagePath)
                                    <img src="{{ asset('storage/' . $requestedImagePath) }}" alt="{{ $swap->requestedProduct->title ?? 'Item' }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[#888]">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Swap Details -->
                        <div class="flex-1 min-w-0">
                            <div class="mb-3">
                                <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-1">{{ $swap->offeredProduct?->title ?? 'Item' }} ↔ {{ $swap->requestedProduct?->title ?? 'Item' }}</h3>
                                <p class="text-sm text-[#888]">With: {{ $swap->ownerB?->name ?? $swap->ownerA?->name ?? 'N/A' }}</p>
                            </div>

                            <div class="mb-4 pb-4 border-b border-[rgba(189,202,189,0.1)]">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-space font-bold px-3 py-1 rounded bg-[#d4edda] text-[#155724]">
                                        Completed
                                    </span>
                                    <p class="text-xs text-[#888]">{{ $swap->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 flex-wrap">
                                @if(in_array((int) $swap->id, $swapReviewedIds, true))
                                    <button type="button" disabled class="cursor-not-allowed bg-[#f3f3f3] border border-[#cfcfcf] text-[#888888] px-4 py-2 font-space text-[10px] font-bold uppercase rounded">Reviewed</button>
                                @else
                                    <a href="{{ route('review.create', ['type' => 'swap', 'id' => $swap->id]) }}" class="bg-[#f0f8f5] border border-[#d97706] text-[#d97706] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(217,119,6,0.06)] transition-all">Leave Review</a>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4"></path>
            </svg>
            <p class="font-manrope text-base text-[#888]">No completed swaps</p>
        </div>
    @endif
</section>

<div class="h-8"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Hide all tabs
            tabContents.forEach(content => content.classList.add('hidden'));
            
            // Remove active state from all buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('text-[#1a1c1c]', 'border-[#006a38]');
                btn.classList.add('text-[#888]', 'border-transparent');
            });

            // Show selected tab
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.remove('hidden');

            // Add active state to clicked button
            this.classList.remove('text-[#888]', 'border-transparent');
            this.classList.add('text-[#1a1c1c]', 'border-[#006a38]');
        });
    });
});
</script>
@endsection
