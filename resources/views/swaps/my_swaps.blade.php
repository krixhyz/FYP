@extends('layouts.dashboard')

@section('content')
<!-- Header Section -->
<section class="px-0 md:px-8 py-8">
    <div>
        <p class="font-space text-[12px] font-bold uppercase tracking-widest text-[#888] mb-2">Swap Workspace</p>
        <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-2">My Swaps</h1>
        <p class="font-manrope text-base text-[#444746]">View your completed and pending swaps.</p>
    </div>
</section>

<!-- Quick Stats -->
<section class="px-0 md:px-8 py-6 grid grid-cols-2 sm:grid-cols-3 gap-4">
    @php
        $completedCount = $swaps->where('status', 'completed')->count();
        $pendingCount = $swaps->where('status', '!=', 'completed')->count();
    @endphp
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Completed Swaps</p>
        <p class="font-space font-bold text-3xl text-[#006a38]">{{ $completedCount }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Pending Swaps</p>
        <p class="font-space font-bold text-3xl text-[#006a38]">{{ $pendingCount }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Total Swaps</p>
        <p class="font-space font-bold text-3xl text-[#006a38]">{{ $swaps->count() }}</p>
    </div>
</section>

<!-- Tab Navigation -->
<div id="tabs" class="px-0 md:px-8 py-6 border-b border-[rgba(189,202,189,0.1)] flex gap-8">
    <button class="tab-button active font-space font-bold text-sm uppercase tracking-widest text-[#1a1c1c] pb-3 border-b-2 border-[#006a38] cursor-pointer" data-tab="completed">
        Completed ({{ $completedCount }})
    </button>
    <button class="tab-button font-space font-bold text-sm uppercase tracking-widest text-[#888] pb-3 border-b-2 border-transparent hover:text-[#1a1c1c] cursor-pointer transition-colors" data-tab="pending">
        Pending ({{ $pendingCount }})
    </button>
</div>

<!-- Completed Swaps Tab -->
<section id="completed" class="tab-content px-0 md:px-8 py-6">
    @php
        $completedSwaps = $swaps->where('status', 'completed');
    @endphp
    
    @if($completedSwaps->count() > 0)
        <div class="space-y-4">
            @foreach($completedSwaps as $swap)
                <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                    <div class="flex items-start gap-4">
                        <!-- Swap Items Images -->
                        <div class="flex gap-2 flex-shrink-0">
                            <div class="w-24 h-24 bg-[#e2e2e2] rounded-lg overflow-hidden">
                                @if($swap->offeredProduct?->images && $swap->offeredProduct->images->first())
                                    <img src="{{ asset('storage/' . $swap->offeredProduct->images->first()->path) }}" alt="{{ $swap->offeredProduct->title ?? 'Item' }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[#888]">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center justify-center px-2 text-[#888]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4"></path>
                                </svg>
                            </div>
                            <div class="w-24 h-24 bg-[#e2e2e2] rounded-lg overflow-hidden">
                                @if($swap->requestedProduct?->images && $swap->requestedProduct->images->first())
                                    <img src="{{ asset('storage/' . $swap->requestedProduct->images->first()->path) }}" alt="{{ $swap->requestedProduct->title ?? 'Item' }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[#888]">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Swap Details -->
                        <div class="flex-1 min-w-0">
                            <div class="mb-3">
                                <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-1">{{ $swap->offeredProduct?->title ?? 'Item' }} ↔ {{ $swap->requestedProduct?->title ?? 'Item' }}</h3>
                                <p class="text-sm text-[#888]">With: {{ $swap->ownerB?->name ?? $swap->ownerA?->name ?? 'N/A' }}</p>
                            </div>

                            <div class="grid grid-cols-3 gap-4 mb-4 pb-4 border-b border-[rgba(189,202,189,0.1)]">
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Status</p>
                                    <span class="text-[11px] font-space font-bold px-3 py-1 rounded bg-[#d4edda] text-[#155724] inline-block">Completed</span>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Date</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ $swap->created_at->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Completed</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ $swap->completed_at ? \Carbon\Carbon::parse($swap->completed_at)->format('M d, Y') : 'N/A' }}</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <a href="{{ route('review.create', ['type' => 'swap', 'id' => $swap->id]) }}" class="inline-block bg-[#f0f8f5] border border-[#d97706] text-[#d97706] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(217,119,6,0.06)] transition-all">Leave Review</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-12 text-center">
            <svg class="w-16 h-16 text-[#ccc] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4"></path>
            </svg>
            <p class="font-manrope text-base text-[#888]">No completed swaps yet</p>
        </div>
    @endif
</section>

<!-- Pending Swaps Tab -->
<section id="pending" class="tab-content hidden px-0 md:px-8 py-6">
    @php
        $pendingSwaps = $swaps->where('status', '!=', 'completed');
    @endphp
    
    @if($pendingSwaps->count() > 0)
        <div class="space-y-4">
            @foreach($pendingSwaps as $swap)
                <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                    <div class="flex items-start gap-4">
                        <!-- Swap Items Images -->
                        <div class="flex gap-2 flex-shrink-0">
                            <div class="w-24 h-24 bg-[#e2e2e2] rounded-lg overflow-hidden">
                                @if($swap->offeredProduct?->images && $swap->offeredProduct->images->first())
                                    <img src="{{ asset('storage/' . $swap->offeredProduct->images->first()->path) }}" alt="{{ $swap->offeredProduct->title ?? 'Item' }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[#888]">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center justify-center px-2 text-[#888]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4"></path>
                                </svg>
                            </div>
                            <div class="w-24 h-24 bg-[#e2e2e2] rounded-lg overflow-hidden">
                                @if($swap->requestedProduct?->images && $swap->requestedProduct->images->first())
                                    <img src="{{ asset('storage/' . $swap->requestedProduct->images->first()->path) }}" alt="{{ $swap->requestedProduct->title ?? 'Item' }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[#888]">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Swap Details -->
                        <div class="flex-1 min-w-0">
                            <div class="mb-3">
                                <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-1">{{ $swap->offeredProduct?->title ?? 'Item' }} ↔ {{ $swap->requestedProduct?->title ?? 'Item' }}</h3>
                                <p class="text-sm text-[#888]">With: {{ $swap->ownerB?->name ?? $swap->ownerA?->name ?? 'N/A' }}</p>
                            </div>

                            <div class="mb-4 pb-4 border-b border-[rgba(189,202,189,0.1)]">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-space font-bold px-3 py-1 rounded bg-[#ffd580] text-[#664d03]">
                                        {{ ucfirst($swap->status) }}
                                    </span>
                                    <p class="text-xs text-[#888]">{{ $swap->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <a href="{{ route('swap.request.show', $swap->id) }}" class="inline-block bg-[#006a38] text-white px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#004a29] transition-all">View Details</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-12 text-center">
            <svg class="w-16 h-16 text-[#ccc] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4"></path>
            </svg>
            <p class="font-manrope text-base text-[#888]">No pending swaps</p>
        </div>
    @endif
</section>

<div class="h-8"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            tabContents.forEach(content => content.classList.add('hidden'));
            tabButtons.forEach(btn => {
                btn.classList.remove('text-[#1a1c1c]', 'border-[#006a38]');
                btn.classList.add('text-[#888]', 'border-transparent');
            });

            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.remove('hidden');

            this.classList.remove('text-[#888]', 'border-transparent');
            this.classList.add('text-[#1a1c1c]', 'border-[#006a38]');
        });
    });
});
</script>
@endsection
