@extends('layouts.app')

@section('content')
<div class="px-8 md:px-16 py-12">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8 pb-6 border-b border-[rgba(189,202,189,0.2)]">
        <div>
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Buy Workflow</p>
            <h1 class="font-space font-bold text-3xl text-[#1a1c1c]">Shopping Cart</h1>
        </div>
        <a href="{{ route('products.index') }}" class="bg-transparent border-2 border-[#006a38] text-[#006a38] px-6 py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] transition-all">Continue Browsing</a>
    </div>

    @if($cartItems->isEmpty())
        <!-- Empty State -->
        <div class="bg-[#f3f3f3] px-8 md:px-16 py-16 text-center">
            <p class="font-space font-bold text-lg text-[#1a1c1c] mb-4">Your cart is empty</p>
            <p class="font-manrope text-base text-[#444746] mb-6">Add items from the marketplace to get started.</p>
            <a href="{{ route('products.index') }}" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-6 py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 inline-block">Browse Products</a>
        </div>
    @else
        <!-- Cart Items -->
        <div class="space-y-3 mb-8">
            @php $grandTotal = 0; @endphp
            @foreach($cartItems as $item)
                @php
                    $unit = $item->product->price ?? 0;
                    $qty = $item->quantity ?? 1;
                    $lineTotal = $unit * $qty;
                    $grandTotal += $lineTotal;
                @endphp
                <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-5 flex flex-col md:flex-row md:items-center gap-4 transition-all duration-300" data-cart-item="{{$item->id}}" data-unit-price="{{ $unit }}" data-line-total="{{ $lineTotal }}">
                    @if($item->product->image)
                        <img src="{{ asset('storage/'.$item->product->image) }}" class="h-20 w-20 object-cover md:flex-shrink-0" alt="">
                    @endif
                    
                    <div class="flex-1">
                        <h3 class="font-space font-bold text-[#1a1c1c]">{{ $item->product->title }}</h3>
                        <p class="font-manrope text-sm text-[#444746] mt-1">Unit: Rs. {{ number_format($unit,2) }}</p>
                    </div>

                    <!-- Quantity Control -->
                    <form action="{{ route('cart.update', $item->id) }}" method="POST" class="flex items-center gap-2" data-cart-action="update">
                        @csrf
                        @method('PATCH')
                        <div class="flex items-center gap-0">
                            <button type="button" onclick="const input = this.closest('form').querySelector('input[name=quantity]'); const min = parseInt(input.min || '1', 10); const max = parseInt(input.max || '1', 10); const current = parseInt(input.value, 10); const safe = Number.isNaN(current) ? min : Math.min(max, Math.max(min, current)); input.value = Math.max(min, safe - 1);" class="w-8 h-8 bg-white border-2 border-gray-300 font-space font-bold text-lg flex items-center justify-center hover:border-[#006a38]">−</button>
                            <input type="number" name="quantity" value="{{ $qty }}" min="1" max="{{ max(1, (int) ($item->product->quantity ?? 1)) }}" class="w-16 h-8 bg-[#f3f3f3] border-0 border-b-2 border-gray-400 text-center font-manrope text-sm focus:border-[#006a38] focus:outline-none">
                            <button type="button" onclick="const input = this.closest('form').querySelector('input[name=quantity]'); const min = parseInt(input.min || '1', 10); const max = parseInt(input.max || '1', 10); const current = parseInt(input.value, 10); const safe = Number.isNaN(current) ? min : Math.min(max, Math.max(min, current)); input.value = Math.min(max, safe + 1);" class="w-8 h-8 bg-white border-2 border-gray-300 font-space font-bold text-lg flex items-center justify-center hover:border-[#006a38]">+</button>
                        </div>
                        <button type="submit" class="bg-transparent border-2 border-[#006a38] text-[#006a38] px-3 py-[6px] font-space text-[11px] font-bold uppercase hover:bg-[rgba(0,106,56,0.06)]">Update</button>
                    </form>

                    <div class="text-right md:min-w-[120px]">
                        <p class="font-space font-bold text-lg text-[#006a38]" data-line-total-display>Rs. {{ number_format($lineTotal,2) }}</p>
                    </div>

                    <form action="{{ route('cart.destroy', $item->id) }}" method="POST" data-cart-action="remove">
                        @csrf
                        @method('DELETE')
                        <button class="text-[11px] font-space font-bold text-[#ba1a1a] uppercase hover:text-[#8a1313]">Remove</button>
                    </form>
                </div>
            @endforeach
        </div>

        <!-- Order Summary -->
        <div class="bg-[#f3f3f3] px-6 py-6 space-y-4">
            <div class="flex justify-between items-center">
                <p class="font-space text-sm font-bold uppercase tracking-widest text-[#444746]">Grand Total</p>
                <p class="font-space font-bold text-2xl text-[#006a38]" data-cart-grand-total>Rs. {{ number_format($grandTotal,2) }}</p>
            </div>
            <a href="{{ route('cart.checkout') }}" class="w-full block bg-gradient-to-br from-[#006a38] to-[#09864a] text-white py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all text-center">
                Proceed to Checkout
            </a>
        </div>
    @endif
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const clamp = (value, min, max) => {
            const parsed = parseInt(value, 10);
            if (Number.isNaN(parsed)) return min;
            return Math.min(max, Math.max(min, parsed));
        };

        document.querySelectorAll('form[data-cart-action="update"]').forEach((form) => {
            const input = form.querySelector('input[name="quantity"]');
            if (!input) return;

            const normalize = () => {
                const min = parseInt(input.min || '1', 10);
                const max = parseInt(input.max || '1', 10);
                input.value = clamp(input.value, min, max);
            };

            input.addEventListener('blur', normalize);
            input.addEventListener('input', normalize);
            form.addEventListener('submit', normalize);

            normalize();
        });
    });
</script>
