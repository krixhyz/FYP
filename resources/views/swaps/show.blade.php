@extends('layouts.app')

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

    $requestedPrice = (float) ($swapRequest->product->price ?? 0);
    $offeredPrice = (float) ($swapRequest->offeredProduct->price ?? 0);
    $offeredCash = (float) ($swapRequest->offered_amount ?? 0);
    $askingCash = (float) ($swapRequest->asking_amount ?? 0);
    $ownerReceives = $offeredPrice + $offeredCash;
    $netDifference = $ownerReceives - $requestedPrice;
    $counterDirection = 'none';
    if ($requestedPrice > $offeredPrice) {
        $counterDirection = 'requester_offers_cash';
    } elseif ($requestedPrice < $offeredPrice) {
        $counterDirection = 'owner_asks_cash';
    }

    $canAskCounterCash = $counterDirection !== 'none';

    $requestedImage = $resolveProductImage($swapRequest->product);
    $offeredImage = $resolveProductImage($swapRequest->offeredProduct);

    $checkoutPayerId = match ($swapRequest->money_direction) {
        'requester_offers_cash' => $swapRequest->requester_id,
        'owner_asks_cash' => $swapRequest->owner_id,
        default => null,
    };
@endphp

<div class="mx-auto max-w-6xl space-y-6 px-3 md:px-0">
    <section class="surface-card-strong p-6 sm:p-8">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Swap Workflow</p>
                <h1 class="mt-2 font-space text-3xl sm:text-4xl font-bold text-[#1a1c1c]">Swap Request #{{ $swapRequest->id }}</h1>
                <p class="mt-2 font-manrope text-sm text-[#444746]">Full offer breakdown, cash terms, and response actions in one view.</p>
            </div>
            <div class="bg-white/80 px-3 py-2 rounded border border-white/60 min-w-[180px]">
                <p class="text-[10px] uppercase tracking-[0.08em] text-[#5f6663] font-space font-bold">Status</p>
                <p class="mt-1 font-space font-bold text-[#1a1c1c]">{{ ucfirst(str_replace('_', ' ', $swapRequest->status)) }}</p>
            </div>
        </div>
    </section>

    <section class="surface-card p-5 sm:p-6 space-y-5">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <article class="border border-[rgba(189,202,189,0.2)] bg-[#f8faf8] p-4 rounded-lg">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#6b6f6e] mb-2">They Offer</p>
                <h2 class="font-space text-lg font-bold text-[#1a1c1c] mb-3">{{ $swapRequest->offeredProduct?->title ?? 'No product offered' }}</h2>
                @if($offeredImage)
                    <img src="{{ $offeredImage }}" alt="Offered Product" class="w-full h-44 object-cover rounded-lg mb-3">
                @else
                    <div class="w-full h-44 bg-[#e7ece8] rounded-lg mb-3 flex items-center justify-center text-[#7d8582]">No image</div>
                @endif
                <p class="font-manrope text-sm text-[#59605e] line-clamp-2">{{ $swapRequest->offeredProduct ? Str::limit($swapRequest->offeredProduct->description, 120) : 'Requester did not attach a product.' }}</p>
                <div class="mt-3">
                    <p class="font-manrope text-xs text-[#6b6f6e]">Offered product value</p>
                    <p class="font-space text-base font-bold text-[#1a1c1c]">Rs. {{ number_format($offeredPrice, 2) }}</p>
                </div>
            </article>

            <article class="border border-[rgba(189,202,189,0.2)] bg-[#f8faf8] p-4 rounded-lg">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#6b6f6e] mb-2">Your Product</p>
                <h2 class="font-space text-lg font-bold text-[#1a1c1c] mb-3">{{ $swapRequest->product->title }}</h2>
                @if($requestedImage)
                    <img src="{{ $requestedImage }}" alt="Requested Product" class="w-full h-44 object-cover rounded-lg mb-3">
                @else
                    <div class="w-full h-44 bg-[#e7ece8] rounded-lg mb-3 flex items-center justify-center text-[#7d8582]">No image</div>
                @endif
                <p class="font-manrope text-sm text-[#59605e] line-clamp-2">{{ Str::limit($swapRequest->product->description, 120) }}</p>
                <div class="mt-3">
                    <p class="font-manrope text-xs text-[#6b6f6e]">Your product value</p>
                    <p class="font-space text-base font-bold text-[#1a1c1c]">Rs. {{ number_format($requestedPrice, 2) }}</p>
                </div>
            </article>

            <article class="border border-[rgba(189,202,189,0.2)] bg-[#f8faf8] p-4 rounded-lg">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#6b6f6e] mb-2">Offer Breakdown</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="font-manrope text-[#59605e]">Cash added by requester</span>
                        <span class="font-space font-bold text-[#006a38]">Rs. {{ number_format($offeredCash, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-manrope text-[#59605e]">Cash demanded by requester</span>
                        <span class="font-space font-bold text-[#9a3412]">Rs. {{ number_format($askingCash, 2) }}</span>
                    </div>
                    <div class="border-t border-[rgba(189,202,189,0.25)] pt-2 flex justify-between items-center">
                        <span class="font-manrope text-[#1a1c1c]">Total value you receive</span>
                        <span class="font-space font-bold text-[#1a1c1c]">Rs. {{ number_format($ownerReceives, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-manrope text-[#1a1c1c]">Difference vs your product</span>
                        <span class="font-space font-bold {{ $netDifference >= 0 ? 'text-[#006a38]' : 'text-[#ba1a1a]' }}">
                            {{ $netDifference >= 0 ? '+' : '-' }} Rs. {{ number_format(abs($netDifference), 2) }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 border-t border-[rgba(189,202,189,0.25)] pt-3">
                    <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#6b6f6e] mb-2">Requester</p>
                    <a href="{{ route('users.show', $swapRequest->requester_id) }}" class="font-space font-bold text-[#006a38] hover:underline">{{ $swapRequest->requester->name }}</a>
                </div>

                <div class="mt-3">
                    <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#6b6f6e] mb-2">Message</p>
                    <div class="bg-white border border-[rgba(189,202,189,0.2)] rounded p-3 min-h-[80px]">
                        <p class="font-manrope text-sm text-[#59605e]">{{ $swapRequest->message ?: 'No additional message.' }}</p>
                    </div>
                </div>
            </article>
        </div>

        @if($swapRequest->status === 'countered')
            <div class="bg-[#006a38] p-4 text-white text-sm rounded">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest">Counter Offer</p>
                <p class="mt-2 font-manrope">Counter Amount: Rs. {{ number_format($swapRequest->counter_amount ?? 0, 2) }}</p>
                <p class="font-manrope">Counter Message: {{ $swapRequest->counter_message ?: 'No message' }}</p>
            </div>
        @endif
    </section>

    @if(auth()->id() === $swapRequest->owner_id && $swapRequest->status === 'requested')
        <section class="surface-card p-5 sm:p-6 space-y-5">
            <h2 class="text-xl font-bold">Respond to Request</h2>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <form method="POST" action="{{ route('swap.request.accept', $swapRequest) }}">
                    @csrf
                    <button type="submit" class="btn-pill btn-pill-dark w-full justify-center">Accept Offer</button>
                </form>
                <form method="POST" action="{{ route('swap.request.reject', $swapRequest) }}">
                    @csrf
                    <button type="submit" class="btn-pill btn-pill-soft w-full justify-center">Reject Offer</button>
                </form>
            </div>

            <h3 class="text-lg font-bold">Send Counter Offer</h3>
            <form id="counter-offer-form" method="POST" action="{{ route('swap.request.counter', $swapRequest) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="label">Counter Amount (Optional)</label>
                    <input type="text" inputmode="decimal" autocomplete="off" pattern="^\d+(\.\d{1,2})?$" name="counter_amount" id="counter_amount" class="input" {{ $canAskCounterCash ? '' : 'disabled' }} />
                    @if(!$canAskCounterCash)
                        <p class="mt-1 text-xs text-[#9a3412]">Both products are equal-valued, so counter cash is disabled. Use message-only counter if needed.</p>
                    @elseif($counterDirection === 'requester_offers_cash')
                        <p class="mt-1 text-xs text-[#6b6f6e]">Locked direction: requester pays you. You may counter with a higher/lower amount, but cannot reverse direction.</p>
                    @else
                        <p class="mt-1 text-xs text-[#6b6f6e]">Locked direction: you pay requester. You may counter with a higher/lower amount, but cannot reverse direction.</p>
                    @endif
                </div>
                <div>
                    <label class="label">Counter Message</label>
                    <textarea name="counter_message" rows="3" class="input"></textarea>
                </div>
                <button type="submit" class="btn-pill btn-pill-dark">Submit Counter</button>
            </form>
        </section>
    @endif

    @if(auth()->id() === $swapRequest->requester_id && $swapRequest->status === 'countered')
        <section class="surface-card p-5 sm:p-6">
            <h2 class="mb-3 text-xl font-bold">Counter Decision</h2>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <form method="POST" action="{{ route('swap.request.counter.accept', $swapRequest) }}">
                    @csrf
                    <button type="submit" class="btn-pill btn-pill-dark w-full justify-center">Accept Counter</button>
                </form>
                <form method="POST" action="{{ route('swap.request.counter.reject', $swapRequest) }}">
                    @csrf
                    <button type="submit" class="btn-pill btn-pill-soft w-full justify-center">Reject Counter</button>
                </form>
            </div>
        </section>
    @endif

    @if($swapRequest->status === 'awaiting_payment' && $checkoutPayerId === auth()->id())
        <a href="{{ route('swap.checkout', $swapRequest) }}" class="btn-pill btn-pill-dark">Proceed to Checkout</a>
    @endif
</div>

<script>
    const counterForm = document.getElementById('counter-offer-form');
    const counterAmountInput = document.getElementById('counter_amount');
    const canAskCounterCash = {{ $canAskCounterCash ? 'true' : 'false' }};
    const counterDirection = '{{ $counterDirection }}';

    if (counterAmountInput) {
        counterAmountInput.addEventListener('input', function () {
            let cleaned = (this.value || '').replace(/[^0-9.]/g, '');
            const parts = cleaned.split('.');
            if (parts.length > 2) {
                cleaned = parts.shift() + '.' + parts.join('');
            }
            this.value = cleaned;
            this.setCustomValidity('');
        });
    }

    if (counterForm && counterAmountInput) {
        counterForm.addEventListener('submit', function (event) {
            counterAmountInput.setCustomValidity('');
            const value = (counterAmountInput.value || '').trim();

            if (!canAskCounterCash && value !== '') {
                counterAmountInput.setCustomValidity('Counter cash is not allowed for this price combination.');
                counterAmountInput.reportValidity();
                event.preventDefault();
                return;
            }

            if (counterDirection !== 'requester_offers_cash' && counterDirection !== 'owner_asks_cash' && value !== '') {
                counterAmountInput.setCustomValidity('Payment direction is locked to no-cash for equal-priced products.');
                counterAmountInput.reportValidity();
                event.preventDefault();
                return;
            }

            if (value !== '') {
                const valid = /^\d+(\.\d{1,2})?$/.test(value) && parseFloat(value) > 0;
                if (!valid) {
                    counterAmountInput.setCustomValidity('Enter a valid amount greater than 0 using numbers only (max 2 decimals).');
                    counterAmountInput.reportValidity();
                    event.preventDefault();
                }
            }
        });
    }
</script>
@endsection
