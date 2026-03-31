@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-8 md:px-16 py-12 space-y-8">
    <!-- Hero Section -->
    <section class="bg-[#f3f3f3] px-6 md:px-8 py-8 border-b border-[rgba(189,202,189,0.3)]">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Buy Workflow</p>
        <h1 class="font-space font-bold text-3xl text-[#1a1c1c] mb-1">Checkout Summary</h1>
        <p class="font-manrope text-base text-[#444746]">Adjust item quantities before you proceed to payment.</p>
    </section>

    @if(session('error'))
        <div class="bg-[#f8d7da] border-2 border-[#f5c6cb] text-[#721c24] px-4 py-3 font-manrope text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-6 space-y-3">
        @php $total = 0; @endphp
        @foreach($cartItems as $item)
            @php
                $unit = $item->product->price ?? 0;
                $qty = $item->quantity ?? 1;
                $line = $unit * $qty;
                $total += $line;
            @endphp
            <div class="grid grid-cols-1 gap-3 bg-[#f3f3f3] p-4 sm:grid-cols-[1fr_auto] sm:items-center">
                <div>
                    <p class="font-manrope font-medium text-sm text-[#1a1c1c]">{{ $item->product->title }}</p>
                    <p class="font-manrope text-xs text-[#888888]">Unit: Rs. {{ number_format($unit,2) }} | Line Total: Rs. {{ number_format($line,2) }}</p>
                </div>
                <form action="{{ route('cart.update', $item->id) }}" method="POST" class="grid grid-cols-[40px_64px_40px_auto] items-center gap-2 justify-self-start sm:justify-self-end" data-cart-action="checkout-update">
                    @csrf
                    @method('PATCH')
                    <button type="button" class="cart-qty-decrement w-10 h-10 border-2 border-gray-300 flex items-center justify-center hover:bg-[#f9f9f9]" data-target="qty-{{ $item->id }}">−</button>
                    <input
                        id="qty-{{ $item->id }}"
                        type="number"
                        name="quantity"
                        min="1"
                        max="{{ max(1, (int) ($item->product->quantity ?? 1)) }}"
                        value="{{ $qty }}"
                        class="h-10 bg-white border-0 border-b-2 border-gray-400 text-center font-manrope text-sm"
                    >
                    <button type="button" class="cart-qty-increment w-10 h-10 border-2 border-gray-300 flex items-center justify-center hover:bg-[#f9f9f9]" data-target="qty-{{ $item->id }}">+</button>
                    <button type="submit" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white py-2 px-3 font-space font-bold text-xs uppercase tracking-wider hover:brightness-110">Update</button>
                </form>
            </div>
        @endforeach

        <div class="flex justify-between bg-[#f3f3f3] px-4 py-3">
            <span class="font-space font-bold text-[#1a1c1c]">Total</span>
            <span class="font-space font-bold text-[#1a1c1c]">Rs. {{ number_format($total,2) }}</span>
        </div>

        <form action="{{ route('orders.placeFromCart') }}" method="POST">
            @csrf
            <div class="mb-4 grid gap-2">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Choose Payment Method</p>
                <label class="flex items-center gap-3 bg-[#f3f3f3] px-4 py-3 cursor-pointer hover:bg-[#e8e8e8]">
                    <input type="radio" name="payment_gateway" value="esewa" {{ old('payment_gateway', 'esewa') === 'esewa' ? 'checked' : '' }} class="w-4 h-4">
                    <span class="font-manrope text-sm text-[#1a1c1c]">eSewa</span>
                </label>
                <label class="flex items-center gap-3 bg-[#f3f3f3] px-4 py-3 cursor-pointer hover:bg-[#e8e8e8]">
                    <input type="radio" name="payment_gateway" value="khalti" {{ old('payment_gateway', 'esewa') === 'khalti' ? 'checked' : '' }} class="w-4 h-4">
                    <span class="font-manrope text-sm text-[#1a1c1c]">Khalti</span>
                </label>
                @error('payment_gateway')
                    <p class="font-manrope text-xs text-[#ba1a1a]">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-6 py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110">
                Proceed to Payment
            </button>
        </form>
    </div>
</div>

<script>
    (function () {
        const clamp = (v, min, max) => {
            const n = parseInt(v, 10);
            if (Number.isNaN(n)) return min;
            return Math.min(max, Math.max(min, n));
        };

        document.querySelectorAll('.cart-qty-decrement').forEach((button) => {
            button.addEventListener('click', () => {
                const targetId = button.dataset.target;
                const input = document.getElementById(targetId);
                if (!input) return;
                const min = parseInt(input.min || '1', 10);
                const max = parseInt(input.max || '1', 10);
                input.value = clamp(input.value, min, max) - 1;
                input.dispatchEvent(new Event('blur'));
            });
        });

        document.querySelectorAll('.cart-qty-increment').forEach((button) => {
            button.addEventListener('click', () => {
                const targetId = button.dataset.target;
                const input = document.getElementById(targetId);
                if (!input) return;
                const min = parseInt(input.min || '1', 10);
                const max = parseInt(input.max || '1', 10);
                input.value = clamp(input.value, min, max) + 1;
                input.dispatchEvent(new Event('blur'));
            });
        });

        document.querySelectorAll('input[id^="qty-"]').forEach((input) => {
            input.addEventListener('blur', () => {
                const min = parseInt(input.min || '1', 10);
                const max = parseInt(input.max || '1', 10);
                input.value = clamp(input.value, min, max);
            });
        });
    })();
</script>
@endsection
