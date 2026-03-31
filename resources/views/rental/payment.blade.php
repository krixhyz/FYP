@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-8">
    <section class="surface-card-strong p-6 sm:p-8">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Rent Workflow</p>
        <h1 class="mt-4 font-space text-4xl font-bold text-[#1a1c1c]">Rental Payment</h1>
    </section>

    @php
        $rentType = $rentalRequest->rental?->rent_type ?? 'daily';
        $rentFare = $rentalRequest->rental?->rent_fare ?? 0;
        $rentDeposit = $rentalRequest->rent_deposit ?? ($rentalRequest->rental?->rent_deposit ?? 0);
        $totalAmount = ($rentalRequest->total_amount ?? 0) + $rentDeposit;
    @endphp

    <section class="surface-card p-5 sm:p-6 space-y-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">
            <div class="bg-[#f3f3f3] p-3"><p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888888]">Product</p><p class="mt-1 font-semibold">{{ $rentalRequest->product->title }}</p></div>
            <div class="bg-[#f3f3f3] p-3"><p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888888]">Rent Type</p><p class="mt-1 font-semibold">{{ ucfirst($rentType) }}</p></div>
            <div class="bg-[#f3f3f3] p-3"><p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888888]">Duration</p><p class="mt-1 font-semibold">{{ $rentalRequest->duration }} {{ $rentType == 'hourly' ? 'hours' : 'days' }}</p></div>
            <div class="bg-[#f3f3f3] p-3"><p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888888]">Fare</p><p class="mt-1 font-semibold">Rs. {{ number_format($rentFare, 2) }}</p></div>
            <div class="bg-[#f3f3f3] p-3"><p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888888]">Deposit</p><p class="mt-1 font-semibold">Rs. {{ number_format($rentDeposit, 2) }}</p></div>
            <div class="bg-[#006a38] p-3 text-white"><p class="font-space text-[10px] font-bold uppercase tracking-widest">Total Amount</p><p class="mt-1 font-manrope text-xl font-bold">Rs. {{ number_format($totalAmount, 2) }}</p></div>
        </div>

        <form method="POST" action="{{ route('rental.pay', $rentalRequest->id) }}" class="mt-2">
            @csrf
            <button type="submit" class="btn-pill btn-pill-dark w-full justify-center">Pay with eSewa</button>
        </form>

        <a href="{{ route('products.index') }}" class="btn-pill btn-pill-soft">Back to Products</a>
    </section>
</div>
@endsection
