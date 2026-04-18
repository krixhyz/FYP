@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto px-8 md:px-16 py-12 space-y-8">
    <!-- Hero Section -->
    <section class="bg-[#f3f3f3] px-6 md:px-8 py-8 border-b border-[rgba(189,202,189,0.3)] flex items-center justify-between">
        <div>
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Support</p>
            <h1 class="font-space font-bold text-3xl text-[#1a1c1c]">My Disputes</h1>
        </div>
        <a href="{{ route('products.myPurchases') }}" class="border-2 border-[#006a38] text-[#006a38] px-4 py-2 font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)]">My Purchases</a>
    </section>

    @if(session('success'))
        <div class="border-2 border-[#10b981] bg-[#d1fae5] px-4 py-3 font-manrope text-sm text-[#065f46]">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-6 md:p-8 space-y-3">
        @forelse ($disputes as $dispute)
            <div class="bg-[#f3f3f3] p-5 {{ !$loop->last ? 'mb-3' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <p class="font-space font-bold text-[#1a1c1c]">{{ $dispute->subject }}</p>
                        <p class="font-manrope text-xs text-[#888888] mt-1">
                            {{ ucfirst($dispute->transaction_type) }} #{{ $dispute->{$dispute->transaction_type === 'order' ? 'order_id' : ($dispute->transaction_type === 'rental' ? ($dispute->rented_rental_id ?: $dispute->rental_request_id) : 'swap_id')} }} | Filed {{ $dispute->created_at->diffForHumans() }}
                        </p>
                        <p class="font-manrope text-sm text-[#444746] mt-2 line-clamp-2">{{ $dispute->description }}</p>
                        @if(!empty($dispute->evidence_photos))
                            <p class="mt-2 font-manrope text-xs text-[#006a38]">{{ count($dispute->evidence_photos) }} evidence photo(s) attached</p>
                        @endif
                        @if($dispute->admin_notes)
                            <div class="mt-3 bg-[#f0fdf4] border-l-4 border-[#10b981] px-4 py-3">
                                <p class="font-space font-bold text-xs text-[#065f46] uppercase tracking-widest">Admin Note</p>
                                <p class="font-manrope text-sm text-[#065f46] mt-1">{{ $dispute->admin_notes }}</p>
                            </div>
                        @endif
                    </div>
                    <span class="shrink-0 bg-[#f3f3f3] border-2 px-3 py-1 font-space text-[11px] font-bold uppercase tracking-widest
                        {{ $dispute->status === 'open' ? 'border-[#f59e0b] text-[#92400e]' : '' }}
                        {{ $dispute->status === 'in_review' ? 'border-[#3b82f6] text-[#1e3a8a]' : '' }}
                        {{ in_array($dispute->status, ['resolved','dismissed']) ? 'border-[#10b981] text-[#065f46]' : '' }}
                    ">{{ ucfirst(str_replace('_', ' ', $dispute->status)) }}</span>
                </div>
            </div>
        @empty
            <div class="p-10 text-center">
                <p class="font-manrope text-sm text-[#888888]">You have not filed any disputes.</p>
            </div>
        @endforelse
    </div>

    <div>{{ $disputes->links() }}</div>
</div>
@endsection
