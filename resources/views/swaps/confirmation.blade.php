@extends('layouts.app')

@section('content')
@php
    $offeredProduct = $swapRequest->offeredProduct;
    $requestedProduct = $swapRequest->product;
    $confirmation = $swapRequest->orderConfirmation;

    $userId = auth()->id();
    $isOwner = $userId === $swapRequest->owner_id;
    $isRequester = $userId === $swapRequest->requester_id;

    $ownerConfirmedAt = $confirmation?->owner_confirmed_at;
    $requesterConfirmedAt = $confirmation?->requester_confirmed_at;
    $finalCompletedAt = $confirmation?->final_completed_at;

    $myUser = $isOwner ? $swapRequest->owner : $swapRequest->requester;
    $counterpartyUser = $isOwner ? $swapRequest->requester : $swapRequest->owner;

    $cashAmount = (float) ($swapRequest->asking_amount ?? $swapRequest->offered_amount ?? 0);
    $hasCashComponent = $swapRequest->money_direction !== 'none' && $cashAmount > 0;

    $canSubmitConfirmation = $swapRequest->status === 'paid' && $confirmation && (
        ($isOwner && !$ownerConfirmedAt) ||
        ($isRequester && !$requesterConfirmedAt)
    );

    $awaitingMyConfirmation = $swapRequest->status === 'paid' && $confirmation && (
        ($isOwner && !$ownerConfirmedAt) ||
        ($isRequester && !$requesterConfirmedAt)
    );

    $expiryDate = null;
    $daysRemaining = null;
    if ($swapRequest->status === 'paid' && $confirmation && !$finalCompletedAt && !$confirmation->auto_expired_at) {
        $expiryDate = $confirmation->created_at->copy()->addDays(7);
        $daysRemaining = now()->diffInDays($expiryDate, false);
    }
@endphp

<div class="mx-auto max-w-5xl space-y-6 px-3 md:px-0">
    <section class="surface-card-strong p-6 sm:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Swap Workflow</p>
                <h1 class="mt-2 font-space text-3xl sm:text-4xl font-bold text-[#1a1c1c]">Swap Confirmation</h1>
                <p class="mt-2 font-manrope text-sm text-[#444746]">Review exchange details and confirm receipt to finalize this transaction.</p>
            </div>
            <div class="min-w-[200px] rounded border border-[rgba(189,202,189,0.35)] bg-white/80 px-4 py-3">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#5f6663]">Current Status</p>
                <p class="mt-1 font-space text-base font-bold text-[#1a1c1c]">{{ ucfirst(str_replace('_', ' ', $swapRequest->status)) }}</p>
                <p class="mt-1 font-manrope text-xs text-[#6b6f6e]">Request ID #{{ $swapRequest->id }}</p>
            </div>
        </div>
    </section>

    @if($swapRequest->status === 'paid')
        <section class="rounded border border-[rgba(12,125,60,0.22)] bg-[#f1f8f3] px-4 py-3">
            <p class="font-manrope text-sm text-[#1f3b2a]">
                <span class="font-semibold">Payment received.</span>
                Both parties must confirm item receipt to complete the swap.
            </p>
        </section>
    @endif

    @if(!$confirmation)
        <section class="rounded border border-[rgba(186,26,26,0.2)] bg-[rgba(186,26,26,0.05)] px-4 py-3">
            <p class="font-manrope text-sm text-[#7d1b1b]">Confirmation record is not available for this swap yet. Please contact support or retry from your swap details page.</p>
        </section>
    @endif

    <section class="surface-card p-5 sm:p-6">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="font-space text-xl font-bold text-[#1a1c1c]">Exchange Summary</h2>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <article class="rounded border border-[rgba(189,202,189,0.2)] bg-[#f8faf8] p-4">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#6b6f6e]">Your Offered Item</p>
                <h3 class="mt-2 font-space text-lg font-bold text-[#1a1c1c]">{{ $offeredProduct?->title ?? 'Offered product unavailable' }}</h3>
                <p class="mt-2 font-manrope text-sm text-[#59605e]">Value: Rs. {{ number_format((float) ($offeredProduct?->price ?? 0), 2) }}</p>
            </article>

            <article class="rounded border border-[rgba(189,202,189,0.2)] bg-[#f8faf8] p-4">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#6b6f6e]">Received Item</p>
                <h3 class="mt-2 font-space text-lg font-bold text-[#1a1c1c]">{{ $requestedProduct?->title ?? 'Requested product unavailable' }}</h3>
                <p class="mt-2 font-manrope text-sm text-[#59605e]">Value: Rs. {{ number_format((float) ($requestedProduct?->price ?? 0), 2) }}</p>
            </article>
        </div>

        @if($hasCashComponent)
            <div class="mt-4 rounded border border-[rgba(189,202,189,0.2)] bg-white p-4">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#6b6f6e]">Cash Component</p>
                <p class="mt-1 font-space text-lg font-bold text-[#1a1c1c]">Rs. {{ number_format($cashAmount, 2) }}</p>
                <p class="mt-1 font-manrope text-sm text-[#59605e]">
                    @if($swapRequest->money_direction === 'owner_asks_cash')
                        Owner receives additional cash from requester.
                    @elseif($swapRequest->money_direction === 'requester_offers_cash')
                        Requester pays additional cash to owner.
                    @endif
                </p>
            </div>
        @endif
    </section>

    <section class="surface-card p-5 sm:p-6">
        <h2 class="font-space text-xl font-bold text-[#1a1c1c]">Dispatch Details</h2>
        <p class="mt-2 font-manrope text-sm text-[#59605e]">Use these details to coordinate item dispatch and delivery confirmation.</p>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <article class="rounded border border-[rgba(189,202,189,0.2)] bg-[#f8faf8] p-4">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#6b6f6e]">Your Details</p>
                <p class="mt-2 font-space text-base font-bold text-[#1a1c1c]">{{ $myUser?->name ?? 'N/A' }}</p>
                <p class="mt-1 font-manrope text-sm text-[#59605e]">Email: {{ $myUser?->email ?? 'N/A' }}</p>
                <p class="mt-1 font-manrope text-sm text-[#59605e]">Phone: {{ $myUser?->phone_number ?? 'N/A' }}</p>
            </article>

            <article class="rounded border border-[rgba(189,202,189,0.2)] bg-[#f8faf8] p-4">
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#6b6f6e]">Counterparty Details</p>
                <p class="mt-2 font-space text-base font-bold text-[#1a1c1c]">{{ $counterpartyUser?->name ?? 'N/A' }}</p>
                <p class="mt-1 font-manrope text-sm text-[#59605e]">Email: {{ $counterpartyUser?->email ?? 'N/A' }}</p>
                <p class="mt-1 font-manrope text-sm text-[#59605e]">Phone: {{ $counterpartyUser?->phone_number ?? 'N/A' }}</p>
            </article>
        </div>
    </section>

    <section class="surface-card p-5 sm:p-6">
        <h2 class="font-space text-xl font-bold text-[#1a1c1c]">Confirmation Progress</h2>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <article class="rounded border border-[rgba(189,202,189,0.2)] bg-[#f8faf8] p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#6b6f6e]">Owner</p>
                        <h3 class="mt-1 font-space text-base font-bold text-[#1a1c1c]">{{ $swapRequest->owner->name }}</h3>
                    </div>
                    @if($ownerConfirmedAt)
                        <span class="rounded bg-[#d9f2e2] px-2.5 py-1 font-space text-[10px] font-bold uppercase tracking-wider text-[#0f6a36]">Confirmed</span>
                    @else
                        <span class="rounded bg-[#fff6db] px-2.5 py-1 font-space text-[10px] font-bold uppercase tracking-wider text-[#8b5f00]">Awaiting</span>
                    @endif
                </div>

                @if($ownerConfirmedAt)
                    <p class="mt-3 font-manrope text-xs text-[#59605e]">Confirmed on {{ $ownerConfirmedAt->format('M d, Y \a\t g:i A') }}</p>
                    @if($confirmation?->owner_notes)
                        <div class="mt-2 rounded border border-[rgba(189,202,189,0.2)] bg-white p-3">
                            <p class="font-manrope text-sm text-[#1a1c1c]">{{ $confirmation->owner_notes }}</p>
                        </div>
                    @endif
                @endif
            </article>

            <article class="rounded border border-[rgba(189,202,189,0.2)] bg-[#f8faf8] p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#6b6f6e]">Requester</p>
                        <h3 class="mt-1 font-space text-base font-bold text-[#1a1c1c]">{{ $swapRequest->requester->name }}</h3>
                    </div>
                    @if($requesterConfirmedAt)
                        <span class="rounded bg-[#d9f2e2] px-2.5 py-1 font-space text-[10px] font-bold uppercase tracking-wider text-[#0f6a36]">Confirmed</span>
                    @else
                        <span class="rounded bg-[#fff6db] px-2.5 py-1 font-space text-[10px] font-bold uppercase tracking-wider text-[#8b5f00]">Awaiting</span>
                    @endif
                </div>

                @if($requesterConfirmedAt)
                    <p class="mt-3 font-manrope text-xs text-[#59605e]">Confirmed on {{ $requesterConfirmedAt->format('M d, Y \a\t g:i A') }}</p>
                    @if($confirmation?->requester_notes)
                        <div class="mt-2 rounded border border-[rgba(189,202,189,0.2)] bg-white p-3">
                            <p class="font-manrope text-sm text-[#1a1c1c]">{{ $confirmation->requester_notes }}</p>
                        </div>
                    @endif
                @endif
            </article>
        </div>
    </section>

    @if($canSubmitConfirmation)
        <section class="surface-card p-5 sm:p-6">
            <h2 class="font-space text-xl font-bold text-[#1a1c1c]">Confirm Receipt</h2>
            <p class="mt-2 font-manrope text-sm text-[#59605e]">
                @if($isOwner)
                    Confirm that you received the item from {{ $swapRequest->requester->name }}.
                @else
                    Confirm that you received the item from {{ $swapRequest->owner->name }}.
                @endif
            </p>

            <form action="{{ route('swap.confirm.received', $swapRequest->id) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="notes" class="label">Confirmation Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="4" class="input" placeholder="Describe item condition or delivery notes.">{{ old('notes') }}</textarea>
                    <p class="mt-1 font-manrope text-xs text-[#6b6f6e]">Keep notes brief and factual.</p>
                </div>

                <button type="submit" class="btn-pill btn-pill-dark">Confirm Item Received</button>
            </form>
        </section>
    @elseif($swapRequest->status === 'paid' && $confirmation)
        <section class="rounded border border-[rgba(189,202,189,0.3)] bg-[#f8faf8] px-4 py-3">
            <p class="font-manrope text-sm text-[#1a1c1c]">
                @if($awaitingMyConfirmation)
                    Your confirmation is pending.
                @else
                    You have already confirmed receipt. Waiting for the other party.
                @endif
            </p>
        </section>
    @endif

    @if($swapRequest->status === 'completed' && $finalCompletedAt)
        <section class="rounded border border-[rgba(12,125,60,0.22)] bg-[#f1f8f3] px-4 py-4">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#0f6a36]">Completed</p>
            <p class="mt-1 font-manrope text-sm text-[#1f3b2a]">Swap finalized on {{ $finalCompletedAt->format('M d, Y \a\t g:i A') }}.</p>
        </section>
    @endif

    @if($daysRemaining !== null && $daysRemaining <= 2)
        <section class="rounded border border-[rgba(217,119,6,0.25)] bg-[rgba(217,119,6,0.08)] px-4 py-3">
            <p class="font-manrope text-sm text-[#7b4d00]">
                <span class="font-semibold">Confirmation deadline:</span>
                {{ max(0, (int) $daysRemaining) }} day(s) remaining. Complete confirmation before {{ $expiryDate->format('M d, Y') }}.
            </p>
        </section>
    @endif

    <section class="flex flex-wrap gap-2 pb-2">
        <a href="{{ route('swap.mySwaps') }}" class="btn-pill btn-pill-soft">View My Swaps</a>
        <a href="{{ route('dashboard') }}" class="btn-pill btn-pill-dark">Back to Dashboard</a>
    </section>
</div>
@endsection
