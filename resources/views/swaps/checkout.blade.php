@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-8 md:px-16 py-12 space-y-8">
    <!-- Hero Section -->
    <section class="bg-[#f3f3f3] px-6 md:px-8 py-8 border-b border-[rgba(189,202,189,0.3)]">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Swap Workflow</p>
        <h1 class="font-space font-bold text-3xl text-[#1a1c1c] mb-1">Swap Checkout</h1>
        <p class="font-manrope text-base text-[#444746]">Review swap details and confirm your transaction.</p>
    </section>

    <section class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-6 md:p-8 space-y-6">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="bg-[#f3f3f3] p-4">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Requested Product</p>
                <p class="font-manrope text-sm text-[#1a1c1c] mt-2">{{ $swapRequest->product->title }}</p>
            </div>
            <div class="bg-[#f3f3f3] p-4">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Offered Product</p>
                <p class="font-manrope text-sm text-[#1a1c1c] mt-2">{{ $swapRequest->offeredProduct?->title ?? 'N/A' }}</p>
            </div>
            <div class="bg-[#006a38] p-4 text-white">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest">Cash Top-up</p>
                <p class="font-space font-bold text-xl text-white mt-2">Rs. {{ number_format($swapRequest->offered_amount ?? 0, 2) }}</p>
            </div>
            <div class="bg-[#f3f3f3] p-4">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Notes</p>
                <p class="font-manrope text-sm text-[#1a1c1c] mt-2">{{ $swapRequest->message ?: 'No notes provided' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('swap.pay', $swapRequest) }}" class="space-y-6">
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
                Confirm Swap and Pay
            </button>
        </form>
    </section>
</div>
@endsection
