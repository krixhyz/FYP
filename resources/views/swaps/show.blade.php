@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-8">
    <section class="surface-card-strong p-6 sm:p-8">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Swap Workflow</p>
        <h1 class="mt-4 font-space text-4xl font-bold text-[#1a1c1c]">Swap Request Details</h1>
    </section>

    <section class="surface-card p-5 sm:p-6 space-y-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">
            <div class="bg-accent-50 p-3"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Requester</p><p class="mt-1 font-semibold">{{ $swapRequest->requester->name }}</p></div>
            <div class="bg-accent-50 p-3"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Requested Product</p><p class="mt-1 font-semibold">{{ $swapRequest->product->title }}</p></div>
            <div class="bg-accent-50 p-3"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Status</p><p class="mt-1 font-semibold">{{ ucfirst(str_replace('_', ' ', $swapRequest->status)) }}</p></div>
            <div class="bg-accent-50 p-3"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Offered Product</p><p class="mt-1 font-semibold">{{ $swapRequest->offeredProduct?->title ?? 'Not provided' }}</p></div>
            <div class="bg-accent-50 p-3"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Offered Amount</p><p class="mt-1 font-semibold">Rs. {{ number_format($swapRequest->offered_amount ?? 0, 2) }}</p></div>
            <div class="bg-accent-50 p-3"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Message</p><p class="mt-1 font-semibold">{{ $swapRequest->message ?: 'No additional message' }}</p></div>
        </div>

        @if($swapRequest->status === 'countered')
            <div class="bg-[#006a38] p-4 text-white text-sm">
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
            <form method="POST" action="{{ route('swap.request.counter', $swapRequest) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="label">Counter Amount (Optional)</label>
                    <input type="number" step="0.01" name="counter_amount" class="input" />
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

    @if(auth()->id() === $swapRequest->requester_id && $swapRequest->status === 'awaiting_payment')
        <a href="{{ route('swap.checkout', $swapRequest) }}" class="btn-pill btn-pill-dark">Proceed to Checkout</a>
    @endif
</div>
@endsection
