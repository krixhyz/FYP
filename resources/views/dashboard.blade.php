@extends('layouts.dashboard')

@section('content')
<!-- Welcome Section -->
<section class="px-0 md:px-8 py-8">
    <div class="mb-2">
        <p class="font-space text-[12px] font-bold uppercase tracking-widest text-[#888]">Welcome back</p>
    </div>
    <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-2">{{ $user->name ?? 'User' }}</h1>
    <p class="font-manrope text-base text-[#444746]">Your workspace dashboard with real-time metrics from your listings, orders, and rentals.</p>
</section>

<!-- Overview Cards -->
<section class="px-0 md:px-8 py-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Active Listings -->
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Active Listings</p>
                <p class="font-space font-bold text-3xl text-[#006a38]">{{ $sellerMetrics['active_units'] ?? 0 }}</p>
            </div>
            <div class="bg-[#f0f8f5] rounded-lg p-3">
                <svg class="w-6 h-6 text-[#006a38]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Total Orders</p>
                <p class="font-space font-bold text-3xl text-[#006a38]">{{ $buyerMetrics['purchases_count'] ?? 0 }}</p>
            </div>
            <div class="bg-[#f0f8f5] rounded-lg p-3">
                <svg class="w-6 h-6 text-[#006a38]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Active Rentals -->
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Active Rentals</p>
                <p class="font-space font-bold text-3xl text-[#006a38]">{{ $buyerMetrics['active_rentals_renter'] ?? 0 }}</p>
            </div>
            <div class="bg-[#f0f8f5] rounded-lg p-3">
                <svg class="w-6 h-6 text-[#006a38]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Eco Score -->
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Eco Score</p>
                <p class="font-space font-bold text-3xl text-[#006a38]">{{ number_format($ecoMetrics['total_eco_score'] ?? 0, 0) }}</p>
            </div>
            <div class="bg-[#f0f8f5] rounded-lg p-3">
                <svg class="w-6 h-6 text-[#006a38]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
        </div>
    </div>
</section>

<!-- Main Content Grid -->
<div class="px-0 md:px-8 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Notifications (spans 2 columns) -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)]">
        <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.1)] flex items-center justify-between">
            <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Recent Notifications</h2>
            <a href="{{ route('notifications.index') }}" class="text-[12px] text-[#006a38] font-space font-bold hover:underline">View All</a>
        </div>
        <div class="divide-y divide-[rgba(189,202,189,0.1)]">
            @forelse ($user->unreadNotifications->take(3) as $notification)
                <a href="{{ route('notifications.index') }}" class="block px-6 py-4 hover:bg-[#f9f9f9] transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-[#006a38] mt-2 flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="font-manrope text-sm text-[#1a1c1c]">{{ $notification->data['message'] ?? 'New activity in your workspace.' }}</p>
                            <p class="mt-1 font-manrope text-xs text-[#888]">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="px-6 py-8 text-center text-[#888]">
                    <p class="font-manrope text-sm">No new notifications</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c] mb-4">Quick Actions</h2>
        <div class="space-y-3">
            <a href="{{ route('products.create') }}" class="w-full block bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-4 py-3 font-space font-bold text-sm uppercase tracking-wider text-center rounded-lg hover:brightness-110 transition-all">New Listing</a>
            <a href="{{ route('products.myListings') }}" class="w-full block bg-transparent border-2 border-[#006a38] text-[#006a38] px-4 py-3 font-space font-bold text-sm uppercase tracking-wider text-center rounded-lg hover:bg-[rgba(0,106,56,0.06)] transition-all">View Listings</a>
            <a href="{{ route('notifications.index') }}" class="w-full block bg-transparent border-2 border-[#006a38] text-[#006a38] px-4 py-3 font-space font-bold text-sm uppercase tracking-wider text-center rounded-lg hover:bg-[rgba(0,106,56,0.06)] transition-all">Check Inbox</a>
        </div>
    </div>
</div>

<!-- My Listings Preview -->
<section class="px-0 md:px-8 py-6">
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)]">
        <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.1)] flex items-center justify-between">
            <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">My Listings</h2>
            <a href="{{ route('products.myListings') }}" class="text-[12px] text-[#006a38] font-space font-bold hover:underline">View All</a>
        </div>
        
        @php
            $recentListings = collect($products ?? [])->take(3);
        @endphp
        
        @if($recentListings->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                @foreach($recentListings as $product)
                    <a href="{{ route('products.show', $product->id) }}" class="group">
                        <div class="bg-[#f9f9f9] rounded-lg overflow-hidden border border-[rgba(189,202,189,0.1)] hover:border-[#006a38] transition-all">
                            <div class="aspect-square bg-[#e2e2e2] overflow-hidden relative">
                                @if($product->images && $product->images->first())
                                    <img src="{{ asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[#888]">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-space font-bold text-sm text-[#1a1c1c] group-hover:text-[#006a38] line-clamp-2">{{ $product->title }}</h3>
                                <div class="mt-2 flex items-center justify-between">
                                    <p class="font-space font-bold text-lg text-[#006a38]">Rs. {{ number_format($product->price, 0) }}</p>
                                    <span class="text-[10px] font-space font-bold px-2 py-1 rounded bg-[#f0f8f5] text-[#006a38]">{{ $product->quantity }} in stock</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="px-6 py-8 text-center">
                <p class="font-manrope text-sm text-[#888] mb-4">You haven't created any listings yet.</p>
                <a href="{{ route('products.create') }}" class="inline-block bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-6 py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 transition-all rounded-lg">Create First Listing</a>
            </div>
        @endif
    </div>
</section>

<!-- Recent Orders Preview -->
<section class="px-0 md:px-8 py-6">
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)]">
        <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.1)] flex items-center justify-between">
            <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Recent Orders</h2>
            <a href="{{ route('products.myPurchases') }}" class="text-[12px] text-[#006a38] font-space font-bold hover:underline">View All</a>
        </div>
        
        @php
            $recentOrders = collect($orders ?? [])->take(5);
        @endphp
        
        @if($recentOrders->count() > 0)
            <div class="divide-y divide-[rgba(189,202,189,0.1)]">
                @foreach($recentOrders as $order)
                    <div class="px-6 py-4 hover:bg-[#f9f9f9] transition-colors">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-space font-bold text-sm text-[#1a1c1c]">{{ $order->product?->title ?? 'Product' }}</h3>
                                <div class="mt-1 flex items-center gap-4">
                                    <p class="font-manrope text-xs text-[#888]">{{ $order->created_at->format('M d, Y') }}</p>
                                    <p class="font-manrope text-xs text-[#888]">Qty: {{ $order->quantity ?? 1 }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <p class="font-space font-bold text-sm text-[#006a38]">Rs. {{ number_format($order->total_price ?? (($order->unit_price ?? 0) * ($order->quantity ?? 1)), 0) }}</p>
                                <span class="text-[10px] font-space font-bold px-2 py-1 rounded {{ $order->status === 'pending' ? 'bg-[#ffd580] text-[#664d03]' : 'bg-[#d4edda] text-[#155724]' }}">
                                    {{ ucfirst($order->status ?? 'pending') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-8 text-center">
                <p class="font-manrope text-sm text-[#888]">No orders yet</p>
            </div>
        @endif
    </div>
</section>

<!-- Sustainability Impact -->
<section class="px-0 md:px-8 py-6">
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)]">
        <div class="px-6 py-4 border-b border-[rgba(189,202,189,0.1)]">
            <h2 class="font-space text-sm font-bold uppercase tracking-widest text-[#1a1c1c]">Sustainability Impact</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
            <div class="text-center">
                <div class="flex justify-center mb-3">
                    <div class="bg-[#f0f8f5] rounded-full p-4">
                        <svg class="w-8 h-8 text-[#006a38]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 015.646 5.646 9 9 0 0120.354 15.354z"></path>
                        </svg>
                    </div>
                </div>
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Items Reused</p>
                <p class="font-space font-bold text-3xl text-[#006a38]">{{ $ecoMetrics['eco_stats']['transaction_count'] ?? 0 }}</p>
            </div>

            <div class="text-center">
                <div class="flex justify-center mb-3">
                    <div class="bg-[#f0f8f5] rounded-full p-4">
                        <svg class="w-8 h-8 text-[#006a38]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Total Eco Points</p>
                <p class="font-space font-bold text-3xl text-[#006a38]">{{ number_format($ecoMetrics['total_eco_score'] ?? 0, 0) }}</p>
            </div>

            <div class="text-center">
                <div class="flex justify-center mb-3">
                    <div class="bg-[#f0f8f5] rounded-full p-4">
                        <svg class="w-8 h-8 text-[#006a38]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Eco Level</p>
                <p class="font-space font-bold text-3xl text-[#006a38]">{{ $ecoMetrics['current_eco_level'] ?? 'None' }}</p>
            </div>
        </div>
    </div>
</section>

<div class="h-8"></div>
@endsection
