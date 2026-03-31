@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-8 md:px-16 py-12 space-y-8">
    <!-- Hero Section -->
    <section class="bg-[#f3f3f3] px-6 md:px-8 py-8 border-b border-[rgba(189,202,189,0.3)]">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Rent Workflow</p>
        <h1 class="font-space font-bold text-3xl text-[#1a1c1c] mb-1">Rental Checkout</h1>
        <p class="font-manrope text-base text-[#444746]">Review rental details and proceed to payment.</p>
    </section>

    @php
        $rentType = $rentalRequest->rental?->rent_type ?? 'daily';
        $rentFare = $rentalRequest->rental?->rent_fare ?? 0;
        $rentDeposit = $rentalRequest->rent_deposit ?? ($rentalRequest->rental?->rent_deposit ?? 0);
        $totalAmount = ($rentalRequest->total_amount ?? 0) + $rentDeposit;
    @endphp

    <section class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-6 md:p-8 space-y-6">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="bg-[#f3f3f3] p-4">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Product</p>
                <p class="font-manrope text-sm text-[#1a1c1c] mt-2">{{ $rentalRequest->product->title }}</p>
            </div>
            <div class="bg-[#f3f3f3] p-4">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Rent Type</p>
                <p class="font-manrope text-sm text-[#1a1c1c] mt-2">{{ ucfirst($rentType) }}</p>
            </div>
            <div class="bg-[#f3f3f3] p-4">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Duration</p>
                <p class="font-manrope text-sm text-[#1a1c1c] mt-2">{{ $rentalRequest->duration }} {{ $rentType == 'hourly' ? 'hours' : 'days' }}</p>
            </div>
            <div class="bg-[#f3f3f3] p-4">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Fare</p>
                <p class="font-manrope text-sm text-[#1a1c1c] mt-2">Rs. {{ number_format($rentFare, 2) }}</p>
            </div>
            <div class="bg-[#f3f3f3] p-4">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Deposit</p>
                <p class="font-manrope text-sm text-[#1a1c1c] mt-2">Rs. {{ number_format($rentDeposit, 2) }}</p>
            </div>
            <div class="bg-[#006a38] p-4 text-white">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest">Total Amount</p>
                <p class="font-space font-bold text-xl text-white mt-2">Rs. {{ number_format($totalAmount, 2) }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('rental.pay', $rentalRequest->id) }}" class="space-y-6">
            @csrf
            <div class="space-y-3">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Choose Payment Method</p>
                <label class="flex items-center gap-3 bg-[#f3f3f3] px-4 py-3 cursor-pointer hover:bg-[#e8e8e8]">
                    <input type="radio" name="payment_gateway" value="esewa" {{ old('payment_gateway') === 'esewa' ? 'checked' : '' }} class="w-4 h-4">
                    <span class="font-manrope text-sm text-[#1a1c1c]">eSewa</span>
                </label>
                <label class="flex items-center gap-3 bg-[#f3f3f3] px-4 py-3 cursor-pointer hover:bg-[#e8e8e8]">
                    <input type="radio" name="payment_gateway" value="khalti" {{ old('payment_gateway') === 'khalti' ? 'checked' : '' }} class="w-4 h-4">
                    <span class="font-manrope text-sm text-[#1a1c1c]">Khalti</span>
                </label>
                @error('payment_gateway')
                    <p class="font-manrope text-xs text-[#ba1a1a]">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full bg-gradient-to-br from-[#006a38] to-[#09864a] text-white py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110">
                Proceed to Payment
            </button>
            <a href="{{ route('products.index') }}" class="w-full block bg-transparent border-2 border-[#006a38] text-[#006a38] py-[10px] text-center font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)]">
                Back to Products
            </a>
        </form>
    </section>
</div>
@endsection
