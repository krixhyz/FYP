@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl space-y-8">
    <section class="surface-card-strong p-6 sm:p-8">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Swap Workflow</p>
        <h1 class="mt-4 font-space text-4xl font-bold text-[#1a1c1c]">Incoming Swap Requests</h1>
    </section>

    @if($requests->isEmpty())
        <div class="surface-card p-8 text-center text-neutral-600">No pending swap requests at the moment.</div>
    @else
        <div class="space-y-4">
            @foreach ($requests as $req)
                <article class="surface-card p-5">
                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-[1fr_1fr_0.9fr]">
                        <div class="bg-accent-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.08em] text-neutral-500">Offered Product</p>
                            <h3 class="mt-2 text-lg font-bold text-neutral-900">{{ $req->offeredProduct ? $req->offeredProduct->title : 'Cash Offer' }}</h3>
                            @if($req->offeredProduct && $req->offeredProduct->image)
                                <img src="{{ asset('storage/' . $req->offeredProduct->image) }}" alt="Offered Product" class="mt-3 h-40 w-full object-cover">
                            @endif
                            <p class="mt-2 text-sm text-neutral-600">{{ $req->offeredProduct ? Str::limit($req->offeredProduct->description, 100) : 'User offers money instead of an item.' }}</p>
                            @if($req->offered_amount)
                                <p class="mt-2 text-sm font-semibold text-[#006a38]">Cash Top-up: Rs. {{ number_format($req->offered_amount,2) }}</p>
                            @endif
                        </div>

                        <div class="bg-accent-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.08em] text-neutral-500">Requested Product</p>
                            <h3 class="mt-2 text-lg font-bold text-neutral-900">{{ $req->product->title }}</h3>
                            @if($req->product->image)
                                <img src="{{ asset('storage/' . $req->product->image) }}" alt="Target Product" class="mt-3 h-40 w-full object-cover">
                            @endif
                            <p class="mt-2 text-sm text-neutral-600">{{ Str::limit($req->product->description, 100) }}</p>
                        </div>

                        <div class="flex flex-col justify-between gap-3 bg-accent-100 p-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-neutral-500">Requester</p>
                                <p class="mt-1 font-semibold text-neutral-900">{{ $req->requester->name }}</p>
                                @if($req->message)
                                    <p class="mt-2 text-sm text-neutral-600">{{ $req->message }}</p>
                                @endif
                            </div>
                            <div class="grid grid-cols-1 gap-2">
                                <a href="{{ route('swap.request.show', $req) }}" class="btn-pill btn-pill-soft justify-center">View / Counter</a>
                                <form action="{{ route('swap.request.accept', $req) }}" method="POST">@csrf<button type="submit" class="btn-pill btn-pill-dark w-full justify-center">Accept</button></form>
                                <form action="{{ route('swap.request.reject', $req) }}" method="POST">@csrf<button type="submit" class="btn-pill btn-pill-soft w-full justify-center">Reject</button></form>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
