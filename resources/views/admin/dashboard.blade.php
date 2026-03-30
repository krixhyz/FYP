@extends('layouts.admin')

@section('title', 'Overview')

@section('content')
@if($isSuperAdmin)
    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="surface-card p-5"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Total Users</p><p class="mt-3 text-4xl font-bold">{{ number_format($totalUsers) }}</p><p class="mt-1 text-sm text-primary-800">{{ number_format($activeUsers) }} active</p></article>
        <article class="surface-card p-5"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Total Listings</p><p class="mt-3 text-4xl font-bold">{{ number_format($totalProducts) }}</p><p class="mt-1 text-sm text-red-700">{{ number_format($flaggedProducts) }} flagged</p></article>
        <article class="surface-card p-5"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Monthly Revenue</p><p class="mt-3 text-4xl font-bold text-primary-800">Rs. {{ number_format($monthlyRevenue, 1) }}</p><p class="mt-1 text-sm text-neutral-600">{{ number_format($completedTransactions) }} completed</p></article>
        <article class="surface-card p-5"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Pending Disputes</p><p class="mt-3 text-4xl font-bold">{{ number_format($openDisputes) }}</p><p class="mt-1 text-sm text-neutral-600">{{ number_format($reportedItems) }} reported</p></article>
    </section>

    <section class="surface-card mt-6 p-5">
        <h3 class="text-2xl font-bold">System Health</h3>
        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="bg-accent-50 p-4"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Server Uptime</p><p class="mt-2 text-3xl font-bold">99.9%</p></div>
            <div class="bg-accent-50 p-4"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">API Response</p><p class="mt-2 text-3xl font-bold">120ms</p></div>
            <div class="bg-accent-50 p-4"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Database Load</p><p class="mt-2 text-3xl font-bold">45%</p></div>
            <div class="bg-accent-50 p-4"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Storage Used</p><p class="mt-2 text-3xl font-bold">67%</p></div>
        </div>
    </section>

    <section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="surface-card p-5">
            <h3 class="text-2xl font-bold">Pending Moderation</h3>
            <div class="mt-4 space-y-3">
                @forelse($products->take(3) as $product)
                    <article class="{{ $product->flagged ? 'bg-red-50' : 'bg-accent-50' }} p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-lg font-semibold">{{ $product->title }}</p>
                                <p class="text-sm text-neutral-600">by {{ $product->user?->name ?? 'N/A' }}</p>
                            </div>
                            <span class="badge {{ $product->flagged ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800' }}">{{ $product->flagged ? 'Flagged' : 'Pending' }}</span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <form method="POST" action="{{ route('admin.content.decision', $product) }}">@csrf @method('PATCH')<input type="hidden" name="decision" value="approve"><button class="btn-pill btn-pill-dark w-full justify-center">Approve</button></form>
                            <form method="POST" action="{{ route('admin.content.decision', $product) }}">@csrf @method('PATCH')<input type="hidden" name="decision" value="reject"><button class="btn-pill btn-pill-soft w-full justify-center">Reject</button></form>
                        </div>
                    </article>
                @empty
                    <p class="bg-accent-50 p-4 text-sm text-neutral-600">No pending moderation items.</p>
                @endforelse
            </div>
        </div>

        <div class="surface-card p-5">
            <h3 class="text-2xl font-bold">Recent Disputes</h3>
            <div class="mt-4 space-y-3">
                @forelse($recentDisputes as $dispute)
                    <article class="bg-red-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-lg font-semibold">{{ $dispute->subject }}</p>
                                <p class="text-sm text-neutral-600">{{ $dispute->reporter?->name ?? 'Unknown user' }}</p>
                                <p class="mt-1 text-sm">Amount: {{ $dispute->order?->total_price ? 'Rs. ' . number_format((float)$dispute->order->total_price,2) : 'N/A' }}</p>
                            </div>
                            <span class="badge bg-red-100 text-red-700">Disputed</span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn-pill btn-pill-dark justify-center">Review</a>
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn-pill btn-pill-soft justify-center">Details</a>
                        </div>
                    </article>
                @empty
                    <p class="bg-accent-50 p-4 text-sm text-neutral-600">No disputes available.</p>
                @endforelse
            </div>
        </div>
    </section>
@else
    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="surface-card p-5"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Pending Users</p><p class="mt-3 text-4xl font-bold">{{ $pendingUsers }}</p><p class="mt-1 text-sm text-neutral-600">verification queue</p></article>
        <article class="surface-card p-5"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Flagged Listings</p><p class="mt-3 text-4xl font-bold">{{ $flaggedProducts }}</p><p class="mt-1 text-sm text-neutral-600">needs moderation</p></article>
        <article class="surface-card p-5"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Active Disputes</p><p class="mt-3 text-4xl font-bold">{{ $openDisputes }}</p><p class="mt-1 text-sm text-neutral-600">in queue</p></article>
        <article class="surface-card p-5"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Reports to Review</p><p class="mt-3 text-4xl font-bold">{{ $reportedItems }}</p><p class="mt-1 text-sm text-neutral-600">content/dispute reports</p></article>
    </section>

    <section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="surface-card p-5">
            <h3 class="text-2xl font-bold">Pending Verifications</h3>
            <div class="mt-4 space-y-3">
                @forelse($pendingVerifications as $candidate)
                    <article class="bg-accent-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-lg font-semibold">{{ $candidate->name }}</p>
                                <p class="text-sm text-neutral-600">{{ $candidate->email }}</p>
                                <p class="mt-1 text-xs text-neutral-500">Email verification pending</p>
                            </div>
                            <span class="badge bg-amber-100 text-amber-800">Pending</span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <form method="POST" action="{{ route('admin.users.status', $candidate) }}">@csrf @method('PATCH')<input type="hidden" name="account_status" value="active"><button class="btn-pill btn-pill-dark w-full justify-center">Verify</button></form>
                            <form method="POST" action="{{ route('admin.users.status', $candidate) }}">@csrf @method('PATCH')<input type="hidden" name="account_status" value="suspended"><button class="btn-pill btn-pill-soft w-full justify-center">Reject</button></form>
                        </div>
                    </article>
                @empty
                    <p class="bg-accent-50 p-4 text-sm text-neutral-600">No pending user verifications.</p>
                @endforelse
            </div>
        </div>

        <div class="surface-card p-5">
            <h3 class="text-2xl font-bold">Priority Disputes</h3>
            <div class="mt-4 space-y-3">
                @forelse($recentDisputes as $dispute)
                    <article class="bg-accent-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-lg font-semibold">{{ $dispute->subject }}</p>
                                <p class="text-sm text-neutral-600">Reporter: {{ $dispute->reporter?->name ?? 'Unknown' }}</p>
                                <p class="mt-1 text-xs text-neutral-500">Type: {{ $dispute->transaction_type }} • Opened {{ $dispute->created_at->format('M j') }}</p>
                            </div>
                            <span class="badge {{ $dispute->status === 'in_review' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-800' }}">{{ str_replace('_',' ', $dispute->status) }}</span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn-pill btn-pill-dark justify-center">Review</a>
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn-pill btn-pill-soft justify-center">Contact Parties</a>
                        </div>
                    </article>
                @empty
                    <p class="bg-accent-50 p-4 text-sm text-neutral-600">No disputes in queue.</p>
                @endforelse
            </div>
        </div>
    </section>
@endif
@endsection
