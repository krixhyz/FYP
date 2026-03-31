@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<div class="surface-card-strong p-6 md:p-8">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="section-kicker">Admin Audit</p>
            <h2 class="section-title mt-1">User Reports</h2>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <select name="type" class="input-field !py-2 text-sm">
                <option value="">Filter by Type</option>
                <option value="order" @selected(request('type') === 'order')>order</option>
                <option value="rental" @selected(request('type') === 'rental')>rental</option>
                <option value="swap" @selected(request('type') === 'swap')>swap</option>
            </select>
            <button class="btn-pill btn-pill-soft !px-4 !py-2 text-sm">Filter</button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($reportItems as $item)
            @if(!request('type') || request('type') === $item->transaction_type)
                <div class="surface-card p-4 border-amber-300 bg-amber-50">
                    <div class="mb-2 flex items-center gap-2">
                        <h3 class="text-xs font-bold uppercase tracking-[0.14em]">{{ $item->transaction_type }}</h3>
                        <span class="status-chip status-warning">{{ $item->status === 'open' ? 'New' : 'Investigating' }}</span>
                    </div>
                    <p class="text-[#1a1c1c]">Reporter: {{ $item->reporter?->name ?? 'Unknown' }} | Subject: {{ $item->subject }}</p>
                    <p class="mt-2 bg-white border border-neutral-300 px-3 py-2 text-sm">{{ $item->description }}</p>
                    <p class="font-manrope text-xs text-[#888888] mt-2">Reported on {{ $item->created_at->format('F j, Y') }}</p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.disputes.show', $item) }}" class="btn-pill btn-pill-soft !px-4 !py-2 text-sm">Investigate</a>

                        <form method="POST" action="{{ route('admin.disputes.resolve', $item) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="in_review">
                            <input type="hidden" name="admin_notes" value="Escalated for action by report operations.">
                            <button class="btn-pill !px-4 !py-2 text-sm !border-[#ba1a1a] !text-[#ba1a1a] hover:!bg-[#ba1a1a] hover:!text-white">Take Action</button>
                        </form>

                        <form method="POST" action="{{ route('admin.disputes.resolve', $item) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="dismissed">
                            <input type="hidden" name="admin_notes" value="Report dismissed after review.">
                            <button class="btn-pill btn-pill-dark !px-4 !py-2 text-sm">Dismiss Report</button>
                        </form>
                    </div>
                </div>
            @endif
        @empty
            <p class="meta-text">No reports available.</p>
        @endforelse
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
        <div class="surface-card p-4">
            <p class="meta-text">Open Disputes</p>
            <p class="text-2xl font-extrabold">{{ $base['open_disputes'] }}</p>
        </div>
        <div class="surface-card p-4">
            <p class="meta-text">In Review</p>
            <p class="text-2xl font-extrabold">{{ $base['in_review_disputes'] }}</p>
        </div>
        <div class="surface-card p-4">
            <p class="meta-text">Resolved</p>
            <p class="text-2xl font-extrabold">{{ $base['resolved_disputes'] }}</p>
        </div>
    </div>

    @if($isSuperAdmin)
        <div class="mt-4 border border-[var(--reloop-green)] bg-[var(--reloop-green-soft)] p-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-[var(--reloop-green-ink)]">Super Admin Export and Financial Insights</h3>
                <a href="{{ route('admin.reports', ['export' => 'csv']) }}" class="btn-pill btn-pill-dark !px-3 !py-2 text-sm">Export Full CSV</a>
            </div>
            <p class="text-[var(--reloop-green-ink)] mt-2">Total Revenue: Rs. {{ number_format($full['total_revenue'] ?? 0, 2) }} | Active Users: {{ $full['active_users'] ?? 0 }}</p>
        </div>
    @endif
</div>
@endsection
