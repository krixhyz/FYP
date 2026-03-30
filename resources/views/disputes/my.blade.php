@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="section-kicker">Support</p>
            <h1 class="section-title mt-1">My Disputes</h1>
        </div>
        <a href="{{ route('products.myPurchases') }}" class="btn-pill btn-pill-soft">My Purchases</a>
    </div>

    @if(session('success'))
        <div class="border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="surface-card overflow-hidden">
        @forelse ($disputes as $dispute)
            <div class="border-b border-slate-100 p-6 last:border-b-0">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-semibold">{{ $dispute->subject }}</p>
                        <p class="mt-0.5 text-xs text-neutral-500">
                            {{ ucfirst($dispute->transaction_type) }} #{{ $dispute->{$dispute->transaction_type === 'order' ? 'order_id' : ($dispute->transaction_type === 'rental' ? 'rental_request_id' : 'swap_id')} }} | Filed {{ $dispute->created_at->diffForHumans() }}
                        </p>
                        <p class="mt-2 text-sm text-neutral-700 line-clamp-2">{{ $dispute->description }}</p>
                        @if($dispute->admin_notes)
                            <div class="mt-2 border border-blue-100 bg-blue-50 px-3 py-2 text-sm text-blue-700"><span class="font-medium">Admin note:</span> {{ $dispute->admin_notes }}</div>
                        @endif
                    </div>
                    <span class="shrink-0 status-chip {{ $dispute->statusBadge() }}">{{ ucfirst(str_replace('_', ' ', $dispute->status)) }}</span>
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-neutral-600">You have not filed any disputes.</div>
        @endforelse
    </div>

    <div>{{ $disputes->links() }}</div>
</div>
@endsection
