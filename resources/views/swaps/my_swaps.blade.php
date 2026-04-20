@extends('layouts.dashboard')

@section('content')
@php
    $toImageUrl = function ($value) {
        if (empty($value)) {
            return null;
        }

        if (is_object($value)) {
            $value = data_get($value, 'image_url') ?? data_get($value, 'url') ?? data_get($value, 'path');
        }

        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, 'data:image') || str_starts_with($value, '/')) {
            return $value;
        }

        return asset('storage/' . ltrim($value, '/'));
    };

    $resolveProductImage = function ($item) use ($toImageUrl) {
        if (!$item) {
            return null;
        }

        $images = $item->images ?? null;
        if (is_array($images) && !empty($images)) {
            $first = $images[0] ?? null;
            $firstUrl = $toImageUrl($first);
            if ($firstUrl) {
                return $firstUrl;
            }
        }

        return $toImageUrl($item->image_url ?? null) ?? $toImageUrl($item->image ?? null);
    };
@endphp
<!-- Header Section -->
<section class="px-0 md:px-8 py-8">
    <div>
        <p class="font-space text-[12px] font-bold uppercase tracking-widest text-[#888] mb-2">Swap Workspace</p>
        <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-2">My Swaps</h1>
        <p class="font-manrope text-base text-[#444746]">View your completed swaps and non-completed swap outcomes.</p>
    </div>
</section>

<!-- Quick Stats -->
<section class="px-0 md:px-8 py-6 grid grid-cols-2 sm:grid-cols-3 gap-4">
    @php
        $completedCount = $completedSwaps->count();
        $nonCompletedCount = $pendingSwapRequests->count();
        $totalSwapCount = $completedCount + $nonCompletedCount;
    @endphp
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Completed Swaps</p>
        <p class="font-space font-bold text-3xl text-[#006a38]">{{ $completedCount }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Non-completed</p>
        <p class="font-space font-bold text-3xl text-[#006a38]">{{ $nonCompletedCount }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Total Swaps</p>
        <p class="font-space font-bold text-3xl text-[#006a38]">{{ $totalSwapCount }}</p>
    </div>
</section>

<!-- Tab Navigation -->
<div id="tabs" class="px-0 md:px-8 py-6 border-b border-[rgba(189,202,189,0.1)] flex gap-8">
    <button class="tab-button active font-space font-bold text-sm uppercase tracking-widest text-[#1a1c1c] pb-3 border-b-2 border-[#006a38] cursor-pointer" data-tab="completed">
        Completed ({{ $completedCount }})
    </button>
    <button class="tab-button font-space font-bold text-sm uppercase tracking-widest text-[#888] pb-3 border-b-2 border-transparent hover:text-[#1a1c1c] cursor-pointer transition-colors" data-tab="pending">
        Non-completed ({{ $nonCompletedCount }})
    </button>
</div>

<!-- Completed Swaps Tab -->
<section id="completed" class="tab-content px-0 md:px-8 py-6">
    @if($completedSwaps->count() > 0)
        <div class="space-y-4">
            @foreach($completedSwaps as $swap)
                @php
                    $completedOfferedImage = $resolveProductImage($swap->offeredProduct);
                    $completedRequestedImage = $resolveProductImage($swap->requestedProduct);
                @endphp
                <div id="swap-card-{{ $swap->swap_request_id }}" class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                    <div class="flex items-start gap-4">
                        <!-- Swap Items Images -->
                        <div class="flex gap-2 flex-shrink-0">
                            <div class="w-24 h-24 bg-[#e2e2e2] rounded-lg overflow-hidden">
                                @if($completedOfferedImage)
                                    <img src="{{ $completedOfferedImage }}" alt="{{ $swap->offeredProduct->title ?? 'Item' }}" class="w-full h-full object-cover">
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
                                @if($completedRequestedImage)
                                    <img src="{{ $completedRequestedImage }}" alt="{{ $swap->requestedProduct->title ?? 'Item' }}" class="w-full h-full object-cover">
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
                                <p class="text-sm text-[#888]">With:
                                    @php
                                        $counterparty = $swap->owner_a_id === auth()->id() ? $swap->ownerB : $swap->ownerA;
                                    @endphp
                                    @if($counterparty)
                                        <a href="{{ route('users.show', $counterparty->id) }}" class="text-[#006a38] hover:underline font-semibold">{{ $counterparty->name }}</a>
                                    @else
                                        N/A
                                    @endif
                                </p>
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
                                    @php
                                        $completedOn = $swap->updated_at ?? $swap->created_at;
                                    @endphp
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ $completedOn ? \Carbon\Carbon::parse($completedOn)->format('M d, Y') : 'N/A' }}</p>
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

<!-- Non-completed Swaps Tab -->
<section id="pending" class="tab-content hidden px-0 md:px-8 py-6">
    @if($pendingSwapRequests->count() > 0)
        <div class="space-y-4">
            @foreach($pendingSwapRequests as $swapRequest)
                @php
                    $pendingOfferedImage = $resolveProductImage($swapRequest->offeredProduct);
                    $pendingRequestedImage = $resolveProductImage($swapRequest->product);
                @endphp
                <div id="swap-card-{{ $swapRequest->id }}" class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                    <div class="flex items-start gap-4">
                        <!-- Swap Items Images -->
                        <div class="flex gap-2 flex-shrink-0">
                            <div class="w-24 h-24 bg-[#e2e2e2] rounded-lg overflow-hidden">
                                @if($pendingOfferedImage)
                                    <img src="{{ $pendingOfferedImage }}" alt="{{ $swapRequest->offeredProduct->title ?? 'Item' }}" class="w-full h-full object-cover">
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
                                @if($pendingRequestedImage)
                                    <img src="{{ $pendingRequestedImage }}" alt="{{ $swapRequest->product->title ?? 'Item' }}" class="w-full h-full object-cover">
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
                                <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-1">{{ $swapRequest->offeredProduct?->title ?? 'Item' }} ↔ {{ $swapRequest->product?->title ?? 'Item' }}</h3>
                                <p class="text-sm text-[#888]">With:
                                    @php
                                        $counterparty = $swapRequest->owner_id === auth()->id() ? $swapRequest->requester : $swapRequest->owner;
                                    @endphp
                                    @if($counterparty)
                                        <a href="{{ route('users.show', $counterparty->id) }}" class="text-[#006a38] hover:underline font-semibold">{{ $counterparty->name }}</a>
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>

                            <div class="mb-4 pb-4 border-b border-[rgba(189,202,189,0.1)]">
                                <div class="flex items-center gap-2">
                                    @php
                                        $statusLabel = ucfirst(str_replace('_', ' ', $swapRequest->status));
                                    @endphp
                                    <span class="text-[11px] font-space font-bold px-3 py-1 rounded bg-[#ffd580] text-[#664d03]">
                                        {{ $statusLabel }}
                                    </span>
                                    <p class="text-xs text-[#888]">{{ $swapRequest->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            @if($swapRequest->status === 'paid')
                                @php
                                    $orderConfirmation = $swapRequest->orderConfirmation;
                                    $authId = auth()->id();
                                    $isOwnerSide = $authId === $swapRequest->owner_id;
                                    $myConfirmed = $orderConfirmation
                                        ? ($isOwnerSide ? !empty($orderConfirmation->owner_confirmed_at) : !empty($orderConfirmation->requester_confirmed_at))
                                        : false;
                                    $counterparty = $isOwnerSide ? $swapRequest->requester : $swapRequest->owner;
                                @endphp

                                <div class="space-y-3">
                                    <div class="rounded border border-[rgba(189,202,189,0.2)] bg-[#f8faf8] p-3">
                                        <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#6b6f6e] mb-1">Dispatch Contact</p>
                                        <p class="font-manrope text-sm text-[#1a1c1c]">{{ $counterparty?->name ?? 'Counterparty' }}</p>
                                        <p class="font-manrope text-xs text-[#59605e]">{{ $counterparty?->email ?? 'No email' }}</p>
                                        <p class="font-manrope text-xs text-[#59605e]">{{ $counterparty?->phone_number ?? 'No phone' }}</p>
                                    </div>

                                    @if(!$myConfirmed)
                                        <form method="POST" action="{{ route('swap.confirm.received', $swapRequest->id) }}" class="inline-flex">
                                            @csrf
                                            <input type="hidden" name="notes" value="Confirmed from My Swaps dashboard">
                                            <button type="submit" class="bg-[#006a38] text-white px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#004a29] transition-all">Confirm Received</button>
                                        </form>
                                    @else
                                        <span class="inline-block bg-[#f0f8f5] border border-[#006a38] text-[#006a38] px-4 py-2 font-space text-[10px] font-bold uppercase rounded">You Confirmed</span>
                                    @endif

                                    <a href="{{ route('swap.request.show', $swapRequest->id) }}" class="inline-block bg-[#f3f3f3] text-[#444746] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#e8e8e8] transition-all">Open Details</a>
                                </div>
                            @else
                                <a href="{{ route('swap.request.show', $swapRequest->id) }}" class="inline-block bg-[#006a38] text-white px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#004a29] transition-all">View Details</a>
                            @endif

                            @if($swapRequest->status !== 'paid')
                                <p class="mt-2 text-xs text-[#888]">Action available once payment is completed.</p>
                            @else
                                <p class="mt-2 text-xs text-[#888]">Confirm from this card after receiving the item.</p>
                            @endif
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
            <p class="font-manrope text-base text-[#888]">No non-completed swap records</p>
        </div>
    @endif
</section>

<div class="h-8"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    function activateTab(tabId) {
        tabContents.forEach(content => content.classList.add('hidden'));
        tabButtons.forEach(btn => {
            btn.classList.remove('text-[#1a1c1c]', 'border-[#006a38]');
            btn.classList.add('text-[#888]', 'border-transparent');
        });

        const targetContent = document.getElementById(tabId);
        const targetBtn = document.querySelector(`.tab-button[data-tab="${tabId}"]`);
        if (!targetContent || !targetBtn) {
            return;
        }

        targetContent.classList.remove('hidden');
        targetBtn.classList.remove('text-[#888]', 'border-transparent');
        targetBtn.classList.add('text-[#1a1c1c]', 'border-[#006a38]');
    }

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            activateTab(tabId);
        });
    });

    const params = new URLSearchParams(window.location.search);
    const requestedTab = params.get('tab');
    if (requestedTab === 'pending' || requestedTab === 'completed') {
        activateTab(requestedTab);
    }

    const swapRequestId = params.get('swap_request_id');
    if (swapRequestId) {
        const card = document.getElementById(`swap-card-${swapRequestId}`);
        if (card) {
            card.classList.add('ring-2', 'ring-[#006a38]');
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});
</script>
@endsection
