@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<div class="surface-card-strong p-6 md:p-8">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="section-kicker">Admin Audit</p>
            <h2 class="section-title mt-1">Reports Overview</h2>
        </div>
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
