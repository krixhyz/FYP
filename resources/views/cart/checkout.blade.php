@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <p class="section-kicker">Buy Workflow</p>
        <h1 class="section-title mt-1">Checkout Summary</h1>
    </div>

    @php $total = 0; @endphp
    <div class="surface-card p-6 space-y-3">
        @foreach($cartItems as $item)
            @php
                $unit = $item->product->price ?? 0;
                $qty = $item->quantity ?? 1;
                $line = $unit * $qty;
                $total += $line;
            @endphp
            <div class="flex justify-between text-sm text-neutral-700">
                <span>{{ $item->product->title }} (x{{ $qty }})</span>
                <span>Rs. {{ number_format($line,2) }}</span>
            </div>
        @endforeach
        <hr class="border-neutral-200">
        <div class="flex justify-between font-bold">
            <span>Total</span>
            <span>Rs. {{ number_format($total,2) }}</span>
        </div>

        <form action="{{ route('orders.placeFromCart') }}" method="POST">
            @csrf
            <button type="submit" class="btn-pill btn-pill-dark mt-4 w-full justify-center py-3">
                Pay with eSewa
            </button>
        </form>
    </div>
</div>
@endsection
