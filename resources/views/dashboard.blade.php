@extends('layouts.app')

@section('content')
<div class="space-y-12">
    <section class="surface-card-strong relative overflow-hidden p-6 sm:p-9">
        <div class="absolute -right-20 -top-20 h-64 w-64 bg-primary-200/30 blur-3xl"></div>
        <div class="absolute -left-16 bottom-0 h-56 w-56 bg-accent-300/35 blur-3xl"></div>
        <div class="relative">
            <p class="section-kicker">Workspace</p>
            <h1 class="hero-title mt-4">Welcome Back, {{ $user->name ?? 'User' }}</h1>
            <p class="hero-subtitle">A single control center with real-time metrics from your listings, orders, rentals, swaps, disputes, and inbox.</p>
            <div class="mt-6 flex flex-wrap gap-2">
                <a href="{{ route('products.create') }}" class="btn-pill btn-pill-dark">Add Listing</a>
                <a href="{{ route('products.myListings') }}" class="btn-pill btn-pill-soft">My Listings</a>
                <a href="{{ route('products.myPurchases') }}" class="btn-pill btn-pill-soft">My Purchases</a>
                <a href="{{ route('notifications.index') }}" class="btn-pill btn-pill-soft">Notifications</a>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
        <article class="surface-card p-4"><p class="meta-text">Total Earnings</p><p class="mt-3 text-3xl font-bold text-[var(--reloop-primary-dark)]">Rs. {{ number_format($workspaceMetrics['total_earnings'], 2) }}</p></article>
        <article class="surface-card p-4"><p class="meta-text">Sell-through Rate</p><p class="mt-3 text-3xl font-bold">{{ $workspaceMetrics['sell_through_rate'] }}%</p></article>
        <article class="surface-card p-4"><p class="meta-text">Inbox Items</p><p class="mt-3 text-3xl font-bold">{{ $workspaceMetrics['inbox_items'] }}</p></article>
        <article class="surface-card p-4"><p class="meta-text">Published Listings</p><p class="mt-3 text-3xl font-bold">{{ $sellerMetrics['published_listings'] }}</p></article>
        <article class="surface-card p-4"><p class="meta-text">Completed Purchases</p><p class="mt-3 text-3xl font-bold">{{ $buyerMetrics['purchases_count'] }}</p></article>
        <article class="surface-card p-4"><p class="meta-text">Total Spent</p><p class="mt-3 text-3xl font-bold">Rs. {{ number_format($buyerMetrics['total_spent'], 2) }}</p></article>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <article class="surface-card p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-extrabold">Seller Pipeline</h2>
                <a href="{{ route('products.myListings') }}" class="btn-pill btn-pill-soft !px-3 !py-1 text-xs">Open</a>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between border-b border-[var(--reloop-border)] pb-2"><span>Units currently active</span><strong>{{ $sellerMetrics['active_units'] }}</strong></div>
                <div class="flex items-center justify-between border-b border-[var(--reloop-border)] pb-2"><span>Units sold (completed)</span><strong>{{ $sellerMetrics['units_sold'] }}</strong></div>
                <div class="flex items-center justify-between border-b border-[var(--reloop-border)] pb-2"><span>Sales revenue</span><strong>Rs. {{ number_format($sellerMetrics['sales_revenue'], 2) }}</strong></div>
                <div class="flex items-center justify-between border-b border-[var(--reloop-border)] pb-2"><span>Rental revenue (paid)</span><strong>Rs. {{ number_format($sellerMetrics['rental_revenue_owner'], 2) }}</strong></div>
                <div class="flex items-center justify-between border-b border-[var(--reloop-border)] pb-2"><span>Incoming rental requests</span><strong>{{ $sellerMetrics['pending_rental_requests_incoming'] }}</strong></div>
                <div class="flex items-center justify-between"><span>Incoming swap requests</span><strong>{{ $sellerMetrics['pending_swap_requests_incoming'] }}</strong></div>
            </div>
        </article>

        <article class="surface-card p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-extrabold">Buyer Pipeline</h2>
                <a href="{{ route('products.myPurchases') }}" class="btn-pill btn-pill-soft !px-3 !py-1 text-xs">Open</a>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between border-b border-[var(--reloop-border)] pb-2"><span>Pending orders</span><strong>{{ $buyerMetrics['pending_orders'] }}</strong></div>
                <div class="flex items-center justify-between border-b border-[var(--reloop-border)] pb-2"><span>Completed purchases</span><strong>{{ $buyerMetrics['purchases_count'] }}</strong></div>
                <div class="flex items-center justify-between border-b border-[var(--reloop-border)] pb-2"><span>Purchased units</span><strong>{{ $buyerMetrics['purchased_units'] }}</strong></div>
                <div class="flex items-center justify-between border-b border-[var(--reloop-border)] pb-2"><span>Active rentals (as renter)</span><strong>{{ $buyerMetrics['active_rentals_renter'] }}</strong></div>
                <div class="flex items-center justify-between border-b border-[var(--reloop-border)] pb-2"><span>Completed swaps</span><strong>{{ $buyerMetrics['completed_swaps'] }}</strong></div>
                <div class="flex items-center justify-between"><span>Open disputes</span><strong>{{ $buyerMetrics['open_disputes'] }}</strong></div>
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <article class="surface-card p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-extrabold">Unread Notifications</h2>
                <span class="status-chip status-info">{{ $buyerMetrics['unread_notifications'] }} Unread</span>
            </div>
            <div class="space-y-3">
                @forelse ($user->unreadNotifications->take(6) as $notification)
                    <a href="{{ route('notifications.index') }}" class="block border border-[var(--reloop-border)] bg-[var(--reloop-primary-soft)]/25 p-3 hover:bg-[var(--reloop-primary-soft)]/45">
                        <p class="text-sm font-semibold text-[var(--reloop-ink)]">{{ $notification->data['message'] ?? 'New activity in your workspace.' }}</p>
                        <p class="mt-1 text-xs text-[var(--reloop-ink-soft)]">{{ $notification->created_at->diffForHumans() }}</p>
                    </a>
                @empty
                    <p class="text-sm text-[var(--reloop-ink-soft)]">No unread notifications.</p>
                @endforelse
            </div>
        </article>

        <article class="surface-card p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-extrabold">Listing Health</h2>
                <span class="status-chip {{ $sellerMetrics['flagged_listings'] > 0 ? 'status-warning' : 'status-success' }}">{{ $sellerMetrics['flagged_listings'] }} Flagged</span>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between border-b border-[var(--reloop-border)] pb-2"><span>Total units listed</span><strong>{{ $sellerMetrics['total_units_listed'] }}</strong></div>
                <div class="flex items-center justify-between border-b border-[var(--reloop-border)] pb-2"><span>Listings needing review</span><strong>{{ $sellerMetrics['flagged_listings'] }}</strong></div>
                <div class="flex items-center justify-between"><span>Unread inbox alerts</span><strong>{{ $buyerMetrics['unread_notifications'] }}</strong></div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('products.myListings') }}" class="btn-pill btn-pill-dark !px-3 !py-1.5 text-xs">Review Listings</a>
                <a href="{{ route('notifications.index') }}" class="btn-pill btn-pill-soft !px-3 !py-1.5 text-xs">Open Inbox</a>
            </div>
        </article>
    </section>
</div>
@endsection
