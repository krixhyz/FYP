@extends('layouts.dashboard')

@section('content')
@php
    $toImageUrl = function ($value) {
        if (empty($value)) {
            return null;
        }

        if (is_object($value)) {
            $value = data_get($value, 'image_url') ?? data_get($value, 'url') ?? data_get($value, 'path');
        }

        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, 'data:image') || str_starts_with($value, '/')) {
            return $value;
        }

        return asset('storage/' . ltrim($value, '/'));
    };

    $resolveProductImage = function ($item) use ($toImageUrl) {
        if (!$item) {
            return null;
        }

        $images = $item->images ?? null;
        if (is_array($images) && !empty($images)) {
            $first = $images[0] ?? null;
            $url = $toImageUrl($first);
            if ($url) {
                return $url;
            }
        }

        return $toImageUrl($item->image_url ?? null) ?? $toImageUrl($item->image ?? null);
    };
@endphp

<section class="px-0 md:px-8 py-6">
    <div class="mb-6">
        <p class="font-space text-[12px] font-bold uppercase tracking-widest text-[#888] mb-2">Swap Management</p>
        <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-2">Incoming Swap Requests</h1>
        <p class="font-manrope text-base text-[#444746]">Review each offer, compare values, and accept, reject, or counter with full context.</p>
    </div>

    @if($requests->isEmpty())
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-12 text-center">
            <svg class="w-16 h-16 text-[#ccc] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m0 0l4 4m10-4v12m0 0l4-4m0 0l-4-4"></path>
            </svg>
            <p class="font-manrope text-base text-[#888]">No open swap requests at the moment</p>
        </div>
    @else
        <div class="space-y-5">
            @foreach ($requests as $req)
                @php
                    $requestedPrice = (float) ($req->product->price ?? 0);
                    $offeredPrice = (float) ($req->offeredProduct->price ?? 0);
                    $offeredCash = (float) ($req->offered_amount ?? 0);
                    $askingCash = (float) ($req->asking_amount ?? 0);
                    $ownerReceives = $offeredPrice + $offeredCash;
                    $valueGap = $ownerReceives - $requestedPrice;

                    $offeredImage = $resolveProductImage($req->offeredProduct);
                    $requestedImage = $resolveProductImage($req->product);
                @endphp

                <article class="bg-white rounded-xl shadow-[0_6px_14px_rgba(0,0,0,0.08)] border border-[rgba(189,202,189,0.18)] p-5 hover:shadow-[0_10px_20px_rgba(0,0,0,0.12)] transition-all">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div>
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#6b6f6e]">Swap Request #{{ $req->id }}</p>
                            <h3 class="font-space font-bold text-xl text-[#1a1c1c]">{{ $req->requester->name }} wants {{ $req->product->title }}</h3>
                        </div>
                        <a href="{{ route('swap.request.show', $req) }}" class="inline-flex items-center bg-[#006a38] text-white px-4 py-2 font-space text-[11px] font-bold uppercase rounded hover:bg-[#004a29] transition-all">View / Counter</a>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <section class="border border-[rgba(189,202,189,0.2)] rounded-lg p-4 bg-[#f8faf8]">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#6b6f6e] mb-2">They Offer</p>
                            <h4 class="font-space font-bold text-base text-[#1a1c1c] mb-3">{{ $req->offeredProduct ? $req->offeredProduct->title : 'No product offered' }}</h4>
                            @if($offeredImage)
                                <img src="{{ $offeredImage }}" alt="Offered Product" class="w-full h-40 object-cover rounded-lg mb-3">
                            @else
                                <div class="w-full h-40 bg-[#e7ece8] rounded-lg flex items-center justify-center text-[#7d8582] mb-3">No image</div>
                            @endif
                            <p class="font-manrope text-sm text-[#59605e] line-clamp-2">{{ $req->offeredProduct ? Str::limit($req->offeredProduct->description, 100) : 'Requester did not attach a product.' }}</p>
                            <div class="mt-3 space-y-1">
                                <p class="font-manrope text-xs text-[#6b6f6e]">Offered product price</p>
                                <p class="font-space font-bold text-[#1a1c1c]">Rs. {{ number_format($offeredPrice, 2) }}</p>
                            </div>
                        </section>

                        <section class="border border-[rgba(189,202,189,0.2)] rounded-lg p-4 bg-[#f8faf8]">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#6b6f6e] mb-2">Your Product</p>
                            <h4 class="font-space font-bold text-base text-[#1a1c1c] mb-3">{{ $req->product->title }}</h4>
                            @if($requestedImage)
                                <img src="{{ $requestedImage }}" alt="Target Product" class="w-full h-40 object-cover rounded-lg mb-3">
                            @else
                                <div class="w-full h-40 bg-[#e7ece8] rounded-lg flex items-center justify-center text-[#7d8582] mb-3">No image</div>
                            @endif
                            <p class="font-manrope text-sm text-[#59605e] line-clamp-2">{{ Str::limit($req->product->description, 100) }}</p>
                            <div class="mt-3 space-y-1">
                                <p class="font-manrope text-xs text-[#6b6f6e]">Your product price</p>
                                <p class="font-space font-bold text-[#1a1c1c]">Rs. {{ number_format($requestedPrice, 2) }}</p>
                            </div>
                        </section>

                        <section class="border border-[rgba(189,202,189,0.2)] rounded-lg p-4 bg-[#f8faf8] flex flex-col">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#6b6f6e] mb-2">Offer Breakdown</p>

                            <div class="space-y-2 mb-4">
                                <div class="flex items-center justify-between">
                                    <span class="font-manrope text-sm text-[#59605e]">Cash added by requester</span>
                                    <span class="font-space font-bold text-[#006a38]">Rs. {{ number_format($offeredCash, 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-manrope text-sm text-[#59605e]">Cash demanded by requester</span>
                                    <span class="font-space font-bold text-[#9a3412]">Rs. {{ number_format($askingCash, 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between border-t border-[rgba(189,202,189,0.25)] pt-2">
                                    <span class="font-manrope text-sm text-[#1a1c1c]">Total value you receive</span>
                                    <span class="font-space font-bold text-[#1a1c1c]">Rs. {{ number_format($ownerReceives, 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-manrope text-sm text-[#1a1c1c]">Difference vs your product</span>
                                    <span class="font-space font-bold {{ $valueGap >= 0 ? 'text-[#006a38]' : 'text-[#ba1a1a]' }}">
                                        {{ $valueGap >= 0 ? '+' : '-' }} Rs. {{ number_format(abs($valueGap), 2) }}
                                    </span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#6b6f6e] mb-2">Requester Message</p>
                                @if($req->message)
                                    <div class="bg-white p-3 rounded border border-[rgba(189,202,189,0.2)]">
                                        <p class="font-manrope text-sm text-[#59605e] italic">"{{ $req->message }}"</p>
                                    </div>
                                @else
                                    <div class="bg-white p-3 rounded border border-[rgba(189,202,189,0.2)]">
                                        <p class="font-manrope text-sm text-[#8b9491]">No message provided.</p>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-auto grid grid-cols-1 gap-2">
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
                </article>
            @endforeach
        </div>
    @endif
</section>

<div class="h-8"></div>
@endsection
