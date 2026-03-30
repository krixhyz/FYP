@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-8">
    <section class="surface-card-strong p-6 sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-primary-800">Swap Workflow</p>
        <h1 class="mt-4 text-4xl font-bold">Swap Checkout</h1>
    </section>

    <section class="surface-card p-5 sm:p-6 space-y-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">
            <div class="bg-accent-50 p-3"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Requested Product</p><p class="mt-1 font-semibold">{{ $swapRequest->product->title }}</p></div>
            <div class="bg-accent-50 p-3"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Offered Product</p><p class="mt-1 font-semibold">{{ $swapRequest->offeredProduct?->title ?? 'N/A' }}</p></div>
            <div class="bg-primary-800 p-3 text-white"><p class="text-xs uppercase tracking-[0.08em]">Cash Top-up</p><p class="mt-1 text-xl font-bold">Rs. {{ number_format($swapRequest->offered_amount ?? 0, 2) }}</p></div>
            <div class="bg-accent-50 p-3"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Notes</p><p class="mt-1 font-semibold">{{ $swapRequest->message ?: 'No notes provided' }}</p></div>
        </div>

        <form method="POST" action="{{ route('swap.pay', $swapRequest) }}" class="mt-2">
            @csrf
            <button type="submit" class="btn-pill btn-pill-dark w-full justify-center">Pay with eSewa</button>
        </form>
    </section>
</div>
@endsection
