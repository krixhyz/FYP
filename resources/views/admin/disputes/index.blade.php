@extends('layouts.admin')
@section('title', 'Disputes')

@section('content')
<div class="surface-card-strong p-6 md:p-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="section-kicker">Admin Cases</p>
            <h2 class="section-title mt-1">Dispute Resolution</h2>
        </div>
        <form method="GET" class="flex gap-2">
            <select name="status" onchange="this.form.submit()" class="input-field !py-2 text-sm">
                <option value="">Filter by Status</option>
                @foreach(['open','in_review','resolved','dismissed'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($disputes as $dispute)
            <div class="surface-card p-5 {{ in_array($dispute->status, ['open','in_review']) ? '' : 'border-2 border-[#ba1a1a] bg-[#fee2e2]' }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-2xl font-extrabold">{{ $dispute->subject }}</h3>
                            <span class="status-chip {{ $dispute->status === 'in_review' ? 'status-info' : ($dispute->status === 'open' ? 'status-warning' : 'status-error') }}">
                                {{ str_replace('_',' ', $dispute->status) }}
                            </span>
                        </div>
                        <p class="font-manrope text-xs text-[#888888] mt-1">Reporter: {{ $dispute->reporter?->name ?? 'Unknown user' }}</p>
                        <p class="font-manrope text-xs text-[#888888] mt-1">Type: {{ $dispute->transaction_type }} | Date Opened {{ $dispute->created_at->format('M j') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-2 mt-4">
                    <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn-pill btn-pill-soft justify-center !py-2">Contact Parties</a>

                    <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="resolved">
                        <input type="hidden" name="admin_notes" value="Resolved in favor of buyer by operations.">
                        <button class="btn-pill btn-pill-dark w-full justify-center !py-2">Resolve in Favor of Buyer</button>
                    </form>

                    <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="dismissed">
                        <input type="hidden" name="admin_notes" value="Resolved in favor of seller by operations.">
                        <button class="btn-pill w-full justify-center !border-[#444746] !text-[#444746] !py-2 hover:!bg-[#444746] hover:!text-white">Resolve in Favor of Seller</button>
                    </form>

                    <form method="POST" action="{{ route('admin.disputes.escalate', $dispute) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="reason" value="Requires super admin review due to risk profile.">
                        <button class="btn-pill w-full justify-center !border-[#ba1a1a] !text-[#ba1a1a] !py-2 hover:!bg-[#ba1a1a] hover:!text-white">Escalate to Super Admin</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="meta-text">No disputes found.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $disputes->links() }}</div>
</div>
@endsection
