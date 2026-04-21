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
        $rentFare = $rentalRequest->total_amount ?? ($rentalRequest->rental?->rent_fare ?? 0);
        $rentDeposit = $rentalRequest->rent_deposit ?? ($rentalRequest->rental?->rent_deposit ?? 0);
        $pricing = (new \App\Services\CheckoutPricingService())->calculateRent(
            (float) $rentFare,
            (float) $rentDeposit
        );
        $serviceFee = (float) ($pricing['service_fee'] ?? 0);
        $totalAmount = (float) ($pricing['total_amount'] ?? 0);
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
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Rent Fee</p>
                <p class="font-manrope text-sm text-[#1a1c1c] mt-2">Rs. {{ number_format($rentFare, 2) }}</p>
            </div>
            <div class="bg-[#f3f3f3] p-4">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Deposit</p>
                <p class="font-manrope text-sm text-[#1a1c1c] mt-2">Rs. {{ number_format($rentDeposit, 2) }}</p>
            </div>
            <div class="bg-[#f3f3f3] p-4">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Service Charge ({{ number_format($pricing['fee_percentage'] ?? 0, 0) }}%)</p>
                <p class="font-manrope text-sm text-[#1a1c1c] mt-2">Rs. {{ number_format($serviceFee, 2) }}</p>
            </div>
            <div class="bg-[#006a38] p-4 text-white">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest">Total Payable</p>
                <p class="font-space font-bold text-xl text-white mt-2">Rs. {{ number_format($totalAmount, 2) }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('rental.pay', $rentalRequest->id) }}" class="space-y-6">
            @csrf
            
            <!-- Buyer Details Section -->
            <div class="mb-6 p-6 bg-[#f3f3f3]">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-4">Delivery Details</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="buyer_name" class="block font-manrope text-sm font-medium text-[#1a1c1c] mb-2">Full Name *</label>
                        <input type="text" id="buyer_name" name="buyer_name" value="{{ old('buyer_name', Auth::user()->name) }}" required class="w-full px-4 py-2 border-2 border-gray-300 font-manrope text-sm focus:border-[#006a38] focus:outline-none">
                        @error('buyer_name')
                            <p class="font-manrope text-xs text-[#ba1a1a] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="buyer_phone" class="block font-manrope text-sm font-medium text-[#1a1c1c] mb-2">Phone Number</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 font-manrope text-sm text-[#666666] pointer-events-none">+977</span>
                            <input type="tel" id="buyer_phone" name="buyer_phone" value="{{ old('buyer_phone') ? substr(preg_replace('/[^0-9]+/', '', old('buyer_phone', Auth::user()->phone_number ?? '')), -10) : substr(preg_replace('/[^0-9]+/', '', Auth::user()->phone_number ?? ''), -10) }}" placeholder="10 digits" maxlength="10" pattern="[0-9]{10}" class="w-full px-4 py-2 pl-14 border-2 border-gray-300 font-manrope text-sm focus:border-[#006a38] focus:outline-none" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                        </div>
                        @error('buyer_phone')
                            <p class="font-manrope text-xs text-[#ba1a1a] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="buyer_email" class="block font-manrope text-sm font-medium text-[#1a1c1c] mb-2">Email Address *</label>
                        <input type="email" id="buyer_email" name="buyer_email" value="{{ old('buyer_email', Auth::user()->email) }}" required class="w-full px-4 py-2 border-2 border-gray-300 font-manrope text-sm focus:border-[#006a38] focus:outline-none">
                        @error('buyer_email')
                            <p class="font-manrope text-xs text-[#ba1a1a] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="buyer_address" class="block font-manrope text-sm font-medium text-[#1a1c1c] mb-2">Delivery Address (Auto-filled)</label>
                        <input type="hidden" id="buyer_address" name="buyer_address" value="{{ Auth::user()->province?->name }}, {{ Auth::user()->city?->name }}">
                        <p class="w-full px-4 py-2 border-2 border-gray-300 font-manrope text-sm bg-gray-100 text-gray-700 rounded">{{ Auth::user()->province?->name }}, {{ Auth::user()->city?->name }}</p>
                    </div>
                </div>
            </div>

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
