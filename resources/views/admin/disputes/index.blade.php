@extends('layouts.admin')
@section('title', 'Disputes')

@section('content')
<div class="surface-card-strong p-6 md:p-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
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
            <select name="status" class="input-field !py-2 text-sm">
                <option value="">Filter by Status</option>
                <option value="open" @selected(request('status') === 'open')>open</option>
                <option value="in_review" @selected(request('status') === 'in_review')>in review</option>
                <option value="resolved" @selected(request('status') === 'resolved')>resolved</option>
                <option value="dismissed" @selected(request('status') === 'dismissed')>dismissed</option>
            </select>
            <button class="btn-pill btn-pill-soft !px-4 !py-2 text-sm">Filter</button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($disputes as $dispute)
            <div class="surface-card p-4 border-amber-300 bg-amber-50">
                <div class="mb-2 flex items-center gap-2">
                    <h3 class="text-xs font-bold uppercase tracking-[0.14em]">{{ $dispute->transaction_type }}</h3>
                    <span class="status-chip status-warning">{{ $dispute->status === 'open' ? 'New' : 'Investigating' }}</span>
                </div>
                <p class="text-[#1a1c1c]">Reporter: {{ $dispute->reporter?->name ?? 'Unknown' }} | Subject: {{ $dispute->subject }}</p>
                <p class="mt-2 bg-white border border-neutral-300 px-3 py-2 text-sm">{{ $dispute->description }}</p>
                <p class="font-manrope text-xs text-[#888888] mt-2">Reported on {{ $dispute->created_at->format('F j, Y') }}</p>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn-pill btn-pill-soft !px-4 !py-2 text-sm">Investigate</a>

                    <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="in_review">
                        <input type="hidden" name="admin_notes" value="Escalated for action by report operations.">
                        <button class="btn-pill !px-4 !py-2 text-sm !border-[#ba1a1a] !text-[#ba1a1a] hover:!bg-[#ba1a1a] hover:!text-white">Take Action</button>
                    </form>

                    <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="dismissed">
                        <input type="hidden" name="favored_party" value="counterparty">
                        <input type="hidden" name="admin_notes" value="Report dismissed after review.">
                        <button class="btn-pill btn-pill-dark !px-4 !py-2 text-sm">Dismiss Report</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="meta-text">No reports available.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $disputes->links() }}</div>
</div>
@endsection
