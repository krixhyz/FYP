@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-8 md:px-16 py-12 space-y-8">
    <!-- Hero Section -->
    <section class="bg-[#f3f3f3] px-6 md:px-8 py-8 border-b border-[rgba(189,202,189,0.3)]">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Buy Workflow</p>
        <h1 class="font-space font-bold text-3xl text-[#1a1c1c] mb-1">Checkout</h1>
        <p class="font-manrope text-base text-[#444746]">Review your order and continue to payment.</p>
    </section>

    @php
        $hasOrder = isset($order);
        $checkoutProduct = $hasOrder ? $order->product : $product;
        $unit = $hasOrder ? ($order->unit_price ?? ($order->product?->price ?? 0)) : ($checkoutProduct->price ?? 0);
        $qty  = $hasOrder ? ($order->quantity ?? 1) : ($quantity ?? 1);
        $total = $unit * $qty;
    @endphp

    <!-- Order Summary Card -->
    <section class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-6 md:p-8">
        <!-- Product Details -->
        <div class="flex gap-4 mb-8 pb-8 border-b border-[rgba(189,202,189,0.2)]">
            @if($checkoutProduct?->image)
                <img src="{{ asset('storage/'.$checkoutProduct->image) }}" class="h-24 w-24 object-cover flex-shrink-0" alt="">
            @endif
            <div>
                <h2 class="font-space font-bold text-lg text-[#1a1c1c]">{{ $checkoutProduct?->title ?? 'Product' }}</h2>
                <p class="font-manrope text-sm text-[#444746] mt-2">{{ \Illuminate\Support\Str::limit($checkoutProduct?->description ?? '', 100) }}</p>
            </div>
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-0 mb-8 bg-[#f3f3f3]">
            <div class="flex justify-between px-4 py-3 border-b border-r border-[rgba(189,202,189,0.2)] last:border-b-0">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Unit Price</p>
                <p class="font-manrope text-sm text-[#1a1c1c]">Rs. {{ number_format($unit,2) }}</p>
            </div>
            <div class="flex justify-between px-4 py-3 border-b border-[rgba(189,202,189,0.2)] last:border-b-0">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Status</p>
                <p class="font-manrope text-sm text-[#1a1c1c]">{{ $hasOrder ? ucfirst($order->status) : 'Awaiting Payment' }}</p>
            </div>
            <div class="flex justify-between px-4 py-3 border-b border-r border-[rgba(189,202,189,0.2)] last:border-b-0">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Quantity</p>
                <p id="checkout-qty-display" class="font-manrope text-sm text-[#1a1c1c]">{{ $qty }}</p>
            </div>
            <div class="flex justify-between px-4 py-3 border-b border-[rgba(189,202,189,0.2)] last:border-b-0">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Total</p>
                <p id="checkout-total-display" class="font-space font-bold text-lg text-[#006a38]">Rs. {{ number_format($total,2) }}</p>
            </div>
        </div>

        <form id="order-payment-form" method="POST" action="{{ $hasOrder ? route('order.confirm', $order->id) : route('order.confirm.product', $checkoutProduct->id) }}" class="space-y-6">
            @csrf
            
            @unless($hasOrder)
                <!-- Quantity Adjuster -->
                <div class="space-y-3">
                    <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Adjust Quantity</p>
                    <div class="flex items-center gap-3">
                        <button type="button" id="qty-decrement" class="w-10 h-10 bg-white border-2 border-gray-300 font-space font-bold text-lg flex items-center justify-center hover:border-[#006a38]">−</button>
                        <input
                            id="checkout-qty-input"
                            type="number"
                            name="quantity"
                            min="1"
                            max="{{ max(1, (int) ($checkoutProduct->quantity ?? 1)) }}"
                            value="{{ $qty }}"
                            class="w-20 h-10 bg-[#f3f3f3] border-0 border-b-2 border-gray-400 text-center font-manrope text-sm focus:border-[#006a38] focus:outline-none"
                        >
                        <button type="button" id="qty-increment" class="w-10 h-10 bg-white border-2 border-gray-300 font-space font-bold text-lg flex items-center justify-center hover:border-[#006a38]">+</button>
                    </div>
                    <p class="font-manrope text-xs text-[#444746]">Available: {{ $checkoutProduct->quantity ?? 1 }}</p>
                </div>
            @endunless

            <!-- Payment Gateway Selection -->
            <div class="space-y-3">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Choose Payment Method</p>
                <label class="flex items-center gap-3 bg-[#f3f3f3] p-3 cursor-pointer hover:bg-[#e8e8e8] transition">
                    <input type="radio" name="payment_gateway" value="esewa" {{ old('payment_gateway') === 'esewa' ? 'checked' : '' }} class="w-4 h-4">
                    <span class="font-manrope text-sm text-[#1a1c1c]">eSewa</span>
                </label>
                <label class="flex items-center gap-3 bg-[#f3f3f3] p-3 cursor-pointer hover:bg-[#e8e8e8] transition">
                    <input type="radio" name="payment_gateway" value="khalti" {{ old('payment_gateway') === 'khalti' ? 'checked' : '' }} class="w-4 h-4">
                    <span class="font-manrope text-sm text-[#1a1c1c]">Khalti</span>
                </label>
                @error('payment_gateway')
                    <p class="font-manrope text-xs text-[#ba1a1a]">{{ $message }}</p>
                @enderror
            </div>
            
            <button type="submit" class="w-full bg-gradient-to-br from-[#006a38] to-[#09864a] text-white py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all">Proceed to Payment</button>
        </form>

        <!-- Cancel/Back Actions -->
        <div class="pt-6 border-t border-[rgba(189,202,189,0.2)]">
            @if($hasOrder)
                <form method="POST" action="{{ route('order.cancelCheckout', $order->id) }}" onsubmit="return confirm('Leave checkout and cancel this unpaid order?');" class="mb-3">
                    @csrf
                    <button type="submit" class="w-full bg-transparent border-2 border-[#ba1a1a] text-[#ba1a1a] py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(186,26,26,0.06)] transition-all">Leave Checkout and Cancel Order</button>
                </form>
            @else
                <a href="{{ route('products.show', $checkoutProduct->id) }}" class="w-full block bg-transparent border-2 border-[#006a38] text-[#006a38] py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] text-center transition-all">Back to Product</a>
            @endif
        </div>
    </section>
</div>

@if($hasOrder)
    <script>
        (function () {
            let isSubmittingPayment = false;
            const paymentForm = document.getElementById('order-payment-form');
            if (paymentForm) {
                paymentForm.addEventListener('submit', function () {
                    isSubmittingPayment = true;
                });
            }

            window.addEventListener('pagehide', function () {
                if (isSubmittingPayment) {
                    return;
                }

                const body = new URLSearchParams();
                body.append('_token', '{{ csrf_token() }}');

                if (navigator.sendBeacon) {
                    const blob = new Blob([body.toString()], { type: 'application/x-www-form-urlencoded;charset=UTF-8' });
                    navigator.sendBeacon('{{ route('order.cancelCheckout', $order->id) }}', blob);
                    return;
                }

                fetch('{{ route('order.cancelCheckout', $order->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                    },
                    body: body.toString(),
                    keepalive: true,
                    credentials: 'same-origin',
                });
            });
        })();
    </script>
@else
    <script>
        (function () {
            const input = document.getElementById('checkout-qty-input');
            const minus = document.getElementById('qty-decrement');
            const plus = document.getElementById('qty-increment');
            const qtyDisplay = document.getElementById('checkout-qty-display');
            const totalDisplay = document.getElementById('checkout-total-display');
            const unit = {{ (float) $unit }};
            const min = 1;
            const max = {{ max(1, (int) ($checkoutProduct->quantity ?? 1)) }};

            if (!input || !qtyDisplay || !totalDisplay) {
                return;
            }

            const clamp = (value) => {
                const parsed = parseInt(value, 10);
                if (Number.isNaN(parsed)) return min;
                return Math.min(max, Math.max(min, parsed));
            };

            const updateUI = () => {
                const qty = clamp(input.value);
                input.value = qty;
                qtyDisplay.textContent = qty;
                totalDisplay.textContent = `Rs. ${(unit * qty).toFixed(2)}`;
            };

            minus?.addEventListener('click', () => {
                input.value = clamp(input.value) - 1;
                updateUI();
            });

            plus?.addEventListener('click', () => {
                input.value = clamp(input.value) + 1;
                updateUI();
            });

            input.addEventListener('input', updateUI);
            input.addEventListener('blur', updateUI);
            updateUI();
        })();
    </script>
@endif
@endsection
