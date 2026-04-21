@extends('layouts.dashboard')

@section('content')
<!-- Header Section -->
<section class="px-0 md:px-8 py-8">
    <div>
        <p class="font-space text-[12px] font-bold uppercase tracking-widest text-[#888] mb-2">Rental Management</p>
        <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-2">Incoming Rental Requests</h1>
        <p class="font-manrope text-base text-[#444746]">Review and respond to rental requests from other users.</p>
    </div>
</section>

<!-- Requests List -->
<section class="px-0 md:px-8 py-6">
    @if($requests->isEmpty())
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-12 text-center">
            <svg class="w-16 h-16 text-[#ccc] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="font-manrope text-base text-[#888]">No pending rental requests at the moment</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($requests as $req)
                <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Product Details -->
                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9]">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-3">Product Being Rented</p>
                            <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-3">{{ $req->product->title }}</h3>
                            @php
                                $requestProductImage = collect($req->product->images ?? [])->first();
                                $requestProductImagePath = data_get($requestProductImage, 'path', is_string($requestProductImage) ? $requestProductImage : null) ?? $req->product->image;
                            @endphp
                            @if($requestProductImagePath)
                                <img src="{{ asset('storage/' . $requestProductImagePath) }}" alt="{{ $req->product->title }}" class="w-full h-40 object-cover rounded-lg mb-3">
                            @else
                                <div class="w-full h-40 bg-[#e2e2e2] rounded-lg flex items-center justify-center text-[#888] mb-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                </div>
                            @endif
                            <p class="font-manrope text-sm text-[#666] line-clamp-2">{{ Str::limit($req->product->description, 100) }}</p>
                        </div>

                        <!-- Rental Details -->
                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9]">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-3">Rental Period</p>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Start Date</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ \Carbon\Carbon::parse($req->start_date)->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">End Date</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ \Carbon\Carbon::parse($req->end_date)->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#888] mb-1">Duration</p>
                                    <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ $req->duration }} days</p>
                                </div>
                                <div class="pt-2 border-t border-[rgba(189,202,189,0.1)]">
                                    <p class="text-xs text-[#888] mb-1">Total Amount</p>
                                    <p class="font-space font-bold text-lg text-[#006a38]">Rs. {{ number_format($req->total_amount, 2) }}</p>
                                </div>
                                @if($req->rent_deposit > 0)
                                    <div>
                                        <p class="text-xs text-[#888] mb-1">Security Deposit</p>
                                        <p class="font-space font-bold text-sm text-[#d97706]">Rs. {{ number_format($req->rent_deposit, 2) }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Renter Info & Actions -->
                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9] flex flex-col">
                            <div class="mb-6">
                                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-2">Requested By</p>
                                <p class="font-space font-bold text-lg text-[#1a1c1c] mb-3">
                                    <a href="{{ route('users.show', $req->renter) }}" class="text-[#006a38] hover:underline">{{ $req->renter->name }}</a>
                                </p>
                                <div class="text-xs text-[#888] space-y-1">
                                    <p><strong>Email:</strong> {{ $req->renter->email }}</p>
                                    @if($req->renter->phone)
                                        <p><strong>Phone:</strong> {{ $req->renter->phone }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-auto space-y-2">
                                <a href="{{ route('rental.review', $req->id) }}" class="block w-full bg-[#006a38] text-white px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#004a29] transition-all text-center">Review Details</a>
                                <form action="{{ route('rental.approve', $req->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full bg-[#d4edda] text-[#155724] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#c3e6cb] transition-all">Approve</button>
                                </form>
                                <form action="{{ route('rental.reject', $req->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full bg-[#f8d7da] text-[#721c24] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#f5c6cb] transition-all">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

<div class="h-8"></div>
@endsection
