@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-8">
    <section class="surface-card-strong p-6 sm:p-8">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Rent Workflow</p>
        <h1 class="mt-4 font-space text-4xl font-bold text-[#1a1c1c]">Rental Request Review</h1>
    </section>

    <section class="surface-card p-5 sm:p-6">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">
            <div class="bg-[#f3f3f3] p-3"><p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888888]">Product</p><p class="mt-1 font-semibold">{{ $rentalRequest->product->title }}</p></div>
            <div class="bg-[#f3f3f3] p-3"><p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888888]">Renter</p><p class="mt-1 font-semibold">{{ $rentalRequest->renter->name }}</p></div>
            <div class="bg-[#f3f3f3] p-3"><p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888888]">Duration</p><p class="mt-1 font-semibold">{{ $rentalRequest->duration }} days</p></div>
            <div class="bg-[#f3f3f3] p-3"><p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888888]">Fare</p><p class="mt-1 font-semibold">Rs. {{ $rentalRequest->rental->rent_fare ?? 0 }}</p></div>
            <div class="bg-[#f3f3f3] p-3"><p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888888]">Deposit</p><p class="mt-1 font-semibold">Rs. {{ $rentalRequest->rent_deposit }}</p></div>
            <div class="bg-[#006a38] p-3 text-white"><p class="font-space text-[10px] font-bold uppercase tracking-widest">Total Amount</p><p class="mt-1 font-manrope text-xl font-bold">Rs. {{ $rentalRequest->total_amount + $rentalRequest->rent_deposit }}</p></div>
            <div class="bg-[#f3f3f3] p-3"><p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888888]">Start Date</p><p class="mt-1 font-semibold">{{ $rentalRequest->start_date }}</p></div>
            <div class="bg-[#f3f3f3] p-3"><p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888888]">End Date</p><p class="mt-1 font-semibold">{{ $rentalRequest->end_date }}</p></div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-2 sm:grid-cols-2">
            <form action="{{ route('rental.approve', $rentalRequest->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn-pill btn-pill-dark w-full justify-center">Approve</button>
            </form>
            <form action="{{ route('rental.reject', $rentalRequest->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn-pill btn-pill-soft w-full justify-center">Reject</button>
            </form>
        </div>
    </section>
</div>
@endsection
