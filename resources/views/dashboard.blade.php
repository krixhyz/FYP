@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="bg-[#f3f3f3] px-8 md:px-16 py-12 mb-8">
    <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-3">Workspace</p>
    <h1 class="font-space font-bold text-5xl md:text-6xl text-[#1a1c1c] mb-3">Welcome Back, {{ $user->name ?? 'User' }}</h1>
    <p class="font-manrope text-base text-[#444746] max-width-lg mb-8" style="max-width: 480px">A single control center with real-time metrics from your listings, orders, rentals, swaps, disputes, and inbox.</p>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('products.create') }}" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-6 py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all">Add Listing</a>
        <a href="{{ route('products.myListings') }}" class="bg-transparent border-2 border-[#006a38] text-[#006a38] px-[22px] py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] transition-all">My Listings</a>
        <a href="{{ route('products.myPurchases') }}" class="bg-transparent border-2 border-[#006a38] text-[#006a38] px-[22px] py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] transition-all">My Purchases</a>
        <a href="{{ route('notifications.index') }}" class="bg-transparent border-2 border-[#006a38] text-[#006a38] px-[22px] py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] transition-all">Notifications</a>
    </div>
</section>

<!-- Stats Strip -->
<section class="bg-[#f3f3f3] border-t border-[rgba(189,202,189,0.3)] px-8 md:px-16 py-8">
    <div class="grid grid-cols-3 md:grid-cols-6 gap-0">
        <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">Total Earnings</p>
            <p class="font-space font-bold text-2xl text-[#006a38]">Rs. {{ number_format($workspaceMetrics['total_earnings'], 2) }}</p>
        </div>
        <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">Sell-through Rate</p>
            <p class="font-space font-bold text-2xl text-[#006a38]">{{ $workspaceMetrics['sell_through_rate'] }}%</p>
        </div>
        <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">Inbox Items</p>
            <p class="font-space font-bold text-2xl text-[#006a38]">{{ $workspaceMetrics['inbox_items'] }}</p>
        </div>
        <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">Published Listings</p>
            <p class="font-space font-bold text-2xl text-[#006a38]">{{ $sellerMetrics['published_listings'] }}</p>
        </div>
        <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">Completed Purchases</p>
            <p class="font-space font-bold text-2xl text-[#006a38]">{{ $buyerMetrics['purchases_count'] }}</p>
        </div>
        <div class="px-5 py-4 border-r border-[rgba(189,202,189,0.3)] last:border-r-0">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">Total Spent</p>
            <p class="font-space font-bold text-2xl text-[#006a38]">Rs. {{ number_format($buyerMetrics['total_spent'], 2) }}</p>
        </div>
    </div>
</section>

<!-- Pipeline Panels -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 px-8 md:px-16 py-8">
    <!-- Seller Pipeline -->
    <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
        <div class="bg-[#f3f3f3] px-5 py-3 flex justify-between items-center">
            <h2 class="font-space font-bold text-sm text-[#1a1c1c]">Seller Pipeline</h2>
            <span class="bg-[#006a38] text-white text-[10px] font-space font-bold uppercase tracking-wider px-2 py-1">Open</span>
        </div>
        <div class="px-5 py-0">
            <div class="py-2.5 flex justify-between items-center border-b border-[rgba(189,202,189,0.2)] last:border-b-0 hover:bg-[#f9f9f9] transition-colors">
                <span class="font-manrope text-sm text-[#444746]">Units currently active</span>
                <span class="font-manrope text-sm font-medium text-[#1a1c1c]">{{ $sellerMetrics['active_units'] }}</span>
            </div>
            <div class="py-2.5 flex justify-between items-center border-b border-[rgba(189,202,189,0.2)] last:border-b-0 hover:bg-[#f9f9f9] transition-colors">
                <span class="font-manrope text-sm text-[#444746]">Units sold (completed)</span>
                <span class="font-manrope text-sm font-medium text-[#1a1c1c]">{{ $sellerMetrics['units_sold'] }}</span>
            </div>
            <div class="py-2.5 flex justify-between items-center border-b border-[rgba(189,202,189,0.2)] last:border-b-0 hover:bg-[#f9f9f9] transition-colors">
                <span class="font-manrope text-sm text-[#444746]">Sales revenue</span>
                <span class="font-manrope text-sm font-medium text-[#1a1c1c]">Rs. {{ number_format($sellerMetrics['sales_revenue'], 2) }}</span>
            </div>
            <div class="py-2.5 flex justify-between items-center border-b border-[rgba(189,202,189,0.2)] last:border-b-0 hover:bg-[#f9f9f9] transition-colors">
                <span class="font-manrope text-sm text-[#444746]">Rental revenue (paid)</span>
                <span class="font-manrope text-sm font-medium text-[#1a1c1c]">Rs. {{ number_format($sellerMetrics['rental_revenue_owner'], 2) }}</span>
            </div>
            <div class="py-2.5 flex justify-between items-center border-b border-[rgba(189,202,189,0.2)] last:border-b-0 hover:bg-[#f9f9f9] transition-colors">
                <span class="font-manrope text-sm text-[#444746]">Incoming rental requests</span>
                <span class="font-manrope text-sm font-medium text-[#1a1c1c]">{{ $sellerMetrics['pending_rental_requests_incoming'] }}</span>
            </div>
            <div class="py-2.5 flex justify-between items-center border-b border-[rgba(189,202,189,0.2)] last:border-b-0 hover:bg-[#f9f9f9] transition-colors">
                <span class="font-manrope text-sm text-[#444746]">Incoming swap requests</span>
                <span class="font-manrope text-sm font-medium text-[#1a1c1c]">{{ $sellerMetrics['pending_swap_requests_incoming'] }}</span>
            </div>
        </div>
    </div>

    <!-- Buyer Pipeline -->
    <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
        <div class="bg-[#f3f3f3] px-5 py-3 flex justify-between items-center">
            <h2 class="font-space font-bold text-sm text-[#1a1c1c]">Buyer Pipeline</h2>
            <span class="bg-[#006a38] text-white text-[10px] font-space font-bold uppercase tracking-wider px-2 py-1">Open</span>
        </div>
        <div class="px-5 py-0">
            <div class="py-2.5 flex justify-between items-center border-b border-[rgba(189,202,189,0.2)] last:border-b-0 hover:bg-[#f9f9f9] transition-colors">
                <span class="font-manrope text-sm text-[#444746]">Pending orders</span>
                <span class="font-manrope text-sm font-medium text-[#1a1c1c]">{{ $buyerMetrics['pending_orders'] }}</span>
            </div>
            <div class="py-2.5 flex justify-between items-center border-b border-[rgba(189,202,189,0.2)] last:border-b-0 hover:bg-[#f9f9f9] transition-colors">
                <span class="font-manrope text-sm text-[#444746]">Completed purchases</span>
                <span class="font-manrope text-sm font-medium text-[#1a1c1c]">{{ $buyerMetrics['purchases_count'] }}</span>
            </div>
            <div class="py-2.5 flex justify-between items-center border-b border-[rgba(189,202,189,0.2)] last:border-b-0 hover:bg-[#f9f9f9] transition-colors">
                <span class="font-manrope text-sm text-[#444746]">Purchased units</span>
                <span class="font-manrope text-sm font-medium text-[#1a1c1c]">{{ $buyerMetrics['purchased_units'] }}</span>
            </div>
            <div class="py-2.5 flex justify-between items-center border-b border-[rgba(189,202,189,0.2)] last:border-b-0 hover:bg-[#f9f9f9] transition-colors">
                <span class="font-manrope text-sm text-[#444746]">Active rentals (as renter)</span>
                <span class="font-manrope text-sm font-medium text-[#1a1c1c]">{{ $buyerMetrics['active_rentals_renter'] }}</span>
            </div>
            <div class="py-2.5 flex justify-between items-center border-b border-[rgba(189,202,189,0.2)] last:border-b-0 hover:bg-[#f9f9f9] transition-colors">
                <span class="font-manrope text-sm text-[#444746]">Completed swaps</span>
                <span class="font-manrope text-sm font-medium text-[#1a1c1c]">{{ $buyerMetrics['completed_swaps'] }}</span>
            </div>
            <div class="py-2.5 flex justify-between items-center border-b border-[rgba(189,202,189,0.2)] last:border-b-0 hover:bg-[#f9f9f9] transition-colors">
                <span class="font-manrope text-sm text-[#444746]">Open disputes</span>
                <span class="font-manrope text-sm font-medium text-[#1a1c1c]">{{ $buyerMetrics['open_disputes'] }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Panels -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 px-8 md:px-16 py-8">
    <!-- Unread Notifications -->
    <div class="md:col-span-2 bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
        <div class="bg-[#f3f3f3] px-5 py-3">
            <h2 class="font-space font-bold text-sm text-[#1a1c1c]">Unread Notifications</h2>
        </div>
        <div class="px-5 py-4">
            @forelse ($user->unreadNotifications->take(6) as $notification)
                <a href="{{ route('notifications.index') }}" class="block px-4 py-3 bg-white border-b border-[rgba(189,202,189,0.2)] last:border-b-0 hover:bg-[#f9f9f9] transition-colors">
                    <p class="font-manrope text-sm text-[#1a1c1c]">{{ $notification->data['message'] ?? 'New activity in your workspace.' }}</p>
                    <p class="mt-1 font-manrope text-xs text-[#444746]">{{ $notification->created_at->diffForHumans() }}</p>
                </a>
            @empty
                <p class="font-manrope text-sm text-[#444746] py-6 text-center italic">No unread notifications.</p>
            @endforelse
        </div>
    </div>

    <!-- Listing Health -->
    <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)]">
        <div class="bg-[#f3f3f3] px-5 py-3">
            <h2 class="font-space font-bold text-sm text-[#1a1c1c]">Listing Health</h2>
        </div>
        <div class="px-5 py-4">
            <div class="py-2.5 flex justify-between items-center border-b border-[rgba(189,202,189,0.2)] last:border-b-0">
                <span class="font-manrope text-sm text-[#444746]">Total units listed</span>
                <span class="font-manrope text-sm font-medium text-[#1a1c1c]">{{ $sellerMetrics['total_units_listed'] }}</span>
            </div>
            <div class="py-2.5 flex justify-between items-center border-b border-[rgba(189,202,189,0.2)] last:border-b-0">
                <span class="font-manrope text-sm text-[#444746]">Needing review</span>
                <span class="font-manrope text-sm font-medium text-[#1a1c1c]">{{ $sellerMetrics['flagged_listings'] }}</span>
            </div>
        </div>
        <div class="px-5 py-4 bg-[#f3f3f3] flex gap-3">
            <a href="{{ route('products.myListings') }}" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-6 py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all flex-1 text-center">Review Listings</a>
            <a href="{{ route('notifications.index') }}" class="bg-transparent border-2 border-[#006a38] text-[#006a38] px-[22px] py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] transition-all flex-1 text-center">Open Inbox</a>
        </div>
    </div>
</div>
@endsection
