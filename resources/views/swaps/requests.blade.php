@extends('layouts.dashboard')

@section('content')
<!-- Header Section -->
<section class="px-0 md:px-8 py-8">
    <div>
        <p class="font-space text-[12px] font-bold uppercase tracking-widest text-[#888] mb-2">Swap Management</p>
        <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-2">Incoming Swap Requests</h1>
        <p class="font-manrope text-base text-[#444746]">Review and respond to swap requests from other users.</p>
    </div>
</section>

<!-- Requests List -->
<section class="px-0 md:px-8 py-6">
    @if($requests->isEmpty())
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-12 text-center">
            <svg class="w-16 h-16 text-[#ccc] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4"></path>
            </svg>
            <p class="font-manrope text-base text-[#888]">No pending swap requests at the moment</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($requests as $req)
                <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Offered Product -->
                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9]">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-3">They Offer</p>
                            <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-3">{{ $req->offeredProduct ? $req->offeredProduct->title : 'Cash Offer' }}</h3>
                            @if($req->offeredProduct && $req->offeredProduct->images && $req->offeredProduct->images->first())
                                <img src="{{ asset('storage/' . $req->offeredProduct->images->first()->path) }}" alt="Offered Product" class="w-full h-40 object-cover rounded-lg mb-3">
                            @else
                                <div class="w-full h-40 bg-[#e2e2e2] rounded-lg flex items-center justify-center text-[#888] mb-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                </div>
                            @endif
                            <p class="font-manrope text-sm text-[#666] line-clamp-2">{{ $req->offeredProduct ? Str::limit($req->offeredProduct->description, 100) : 'User offers money instead of an item.' }}</p>
                            @if($req->offered_amount)
                                <p class="font-space font-bold text-[#006a38] mt-3">+ Rs. {{ number_format($req->offered_amount, 2) }}</p>
                            @endif
                        </div>

                        <!-- Requested Product -->
                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9]">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-3">You Offer</p>
                            <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-3">{{ $req->product->title }}</h3>
                            @if($req->product->images && $req->product->images->first())
                                <img src="{{ asset('storage/' . $req->product->images->first()->path) }}" alt="Target Product" class="w-full h-40 object-cover rounded-lg mb-3">
                            @else
                                <div class="w-full h-40 bg-[#e2e2e2] rounded-lg flex items-center justify-center text-[#888] mb-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                </div>
                            @endif
                            <p class="font-manrope text-sm text-[#666] line-clamp-2">{{ Str::limit($req->product->description, 100) }}</p>
                        </div>

                        <!-- Requester Info & Actions -->
                        <div class="border border-[rgba(189,202,189,0.1)] rounded-lg p-4 bg-[#f9f9f9] flex flex-col">
                            <div class="mb-6">
                                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-2">Requested By</p>
                                <p class="font-space font-bold text-lg text-[#1a1c1c] mb-3">{{ $req->requester->name }}</p>
                                @if($req->message)
                                    <div class="bg-white p-3 rounded border border-[rgba(189,202,189,0.1)]">
                                        <p class="font-manrope text-sm text-[#666] italic">"{{ $req->message }}"</p>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-auto space-y-2">
                                <a href="{{ route('swap.request.show', $req) }}" class="block w-full bg-[#006a38] text-white px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#004a29] transition-all text-center">View / Counter</a>
                                <form action="{{ route('swap.request.accept', $req) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-[#d4edda] text-[#155724] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[#c3e6cb] transition-all">Accept</button>
                                </form>
                                <form action="{{ route('swap.request.reject', $req) }}" method="POST">
                                    @csrf
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
