@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-8">
    <section class="surface-card-strong p-6 sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-primary-800">Buy Workflow</p>
        <h1 class="mt-4 text-4xl font-bold">Checkout</h1>
        <p class="mt-2 text-sm text-neutral-700">Review your order and continue to payment.</p>
    </section>

    @php
        $unit = $order->unit_price ?? ($order->product?->price ?? 0);
        $qty  = $order->quantity ?? 1;
        $total = $unit * $qty;
    @endphp

    <section class="surface-card p-5 sm:p-6 space-y-5">
        <div class="grid grid-cols-[80px_1fr] items-center gap-4 bg-accent-50 p-3">
            @if($order->product?->image)
                <img src="{{ asset('storage/'.$order->product->image) }}" class="h-20 w-20 object-cover" alt="">
            @endif
            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ $order->product?->title ?? 'Product' }}</h2>
                <p class="text-xs text-neutral-600">{{ \Illuminate\Support\Str::limit($order->product?->description ?? '', 100) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">
            <div class="bg-accent-50 p-3"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Unit Price</p><p class="mt-1 font-semibold">Rs. {{ number_format($unit,2) }}</p></div>
            <div class="bg-accent-50 p-3"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Quantity</p><p class="mt-1 font-semibold">{{ $qty }}</p></div>
            <div class="bg-accent-50 p-3"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Total</p><p class="mt-1 text-xl font-bold text-primary-800">Rs. {{ number_format($total,2) }}</p></div>
            <div class="bg-accent-50 p-3"><p class="text-xs uppercase tracking-[0.08em] text-neutral-500">Status</p><p class="mt-1 font-semibold">{{ ucfirst($order->status) }}</p></div>
        </div>

        <form method="POST" action="{{ route('order.confirm', $order->id) }}">
            @csrf
            <button type="submit" class="btn-pill btn-pill-dark w-full justify-center py-3">Pay with eSewa</button>
        </form>

        <a href="{{ route('products.index') }}" class="btn-pill btn-pill-soft">Continue Browsing</a>
    </section>
</div>
@endsection
