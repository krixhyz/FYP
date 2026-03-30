@extends('layouts.app')

@section('content')
<div class="space-y-10">
    <a href="{{ route('products.index') }}" class="btn-pill btn-pill-soft !px-3">Back to Gallery</a>

    <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <article class="surface-card p-4">
            @php $allImages = array_filter(array_unique(array_merge($product->images ?? [], $product->image ? [$product->image] : []))); @endphp
            <div class="aspect-square overflow-hidden bg-accent-100" id="mainImage">
                @if(count($allImages) > 0)
                    <img id="mainImg" src="{{ asset('storage/' . reset($allImages)) }}"
                         alt="{{ $product->title }}"
                         class="h-full w-full object-cover">
                @else
                    <div class="flex h-full items-center justify-center text-neutral-400">
                        <svg class="h-20 w-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
            </div>

            @if(count($allImages) > 1)
                <div class="mt-3 grid grid-cols-5 gap-2 sm:grid-cols-6">
                    @foreach($allImages as $img)
                        <img src="{{ asset('storage/' . $img) }}"
                             alt="thumbnail"
                             onclick="document.getElementById('mainImg').src='{{ asset('storage/' . $img) }}'"
                             class="h-16 w-full cursor-pointer object-cover bg-accent-100">
                    @endforeach
                </div>
            @endif
        </article>

        <article class="surface-card p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-neutral-500">Listing</p>
                    <h1 class="mt-2 text-4xl font-bold text-neutral-900">{{ $product->title }}</h1>
                    <p class="mt-2 text-sm text-neutral-600 capitalize">Category: {{ $product->category }}</p>
                </div>
                @auth
                    @if(Auth::id() !== $product->user_id)
                        <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    title="{{ $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}"
                                    class="allow-loop-circle flex h-10 w-10 items-center justify-center transition {{ $isWishlisted ? 'bg-red-600 text-white' : 'bg-accent-100 text-neutral-500' }}">
                                <svg class="h-4 w-4" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </button>
                        </form>
                    @endif
                @endauth
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                @if(in_array('sell', $product->type))
                    <span class="badge-primary">Buy</span>
                @endif
                @if(in_array('rent', $product->type))
                    <span class="badge bg-amber-100 text-amber-900">Rent</span>
                @endif
                @if(in_array('swap', $product->type))
                    <span class="badge-success">Swap</span>
                @endif
            </div>

            @if(in_array('sell', $product->type))
                <p class="mt-5 text-4xl font-bold text-primary-800">Rs. {{ number_format($product->price, 2) }}</p>
            @endif

            <div class="mt-5 space-y-2 bg-accent-50 p-4 text-sm">
                <div class="flex justify-between"><span>Available Quantity</span><span class="font-semibold">{{ $product->quantity }}</span></div>
                <div class="flex justify-between"><span>Owner</span><a href="{{ route('users.show', $product->user->id) }}" class="font-semibold text-primary-800">{{ $product->user->name }}</a></div>
                @php
                    $ownerAvg = \App\Models\Review::where('reviewee_id', $product->user_id)->avg('rating');
                    $ownerCount = \App\Models\Review::where('reviewee_id', $product->user_id)->count();
                @endphp
                @if($ownerAvg)
                    <div class="flex justify-between"><span>Seller Rating</span><span class="font-semibold">{{ number_format($ownerAvg, 1) }}/5 ({{ $ownerCount }})</span></div>
                @endif
                <div class="flex justify-between"><span>Listed</span><span class="font-semibold">{{ $product->created_at->diffForHumans() }}</span></div>
            </div>

            <div class="mt-5">
                <h3 class="text-sm font-semibold uppercase tracking-[0.08em] text-neutral-500">Description</h3>
                <p class="mt-2 text-sm leading-relaxed text-neutral-700">{{ $product->description }}</p>
            </div>

            @auth
                @if(Auth::id() !== $product->user_id && $product->status === 'available')
                    <div class="mt-6 space-y-3">
                        @if(in_array('sell', $product->type) && $product->quantity > 0)
                            <form action="{{ route('order.store', $product->id) }}" method="POST" class="space-y-2 bg-accent-50 p-4">
                                @csrf
                                <label class="label mb-0">Quantity</label>
                                <input type="number" name="quantity" min="1" max="{{ $product->quantity }}" value="1" class="input">
                                <button type="submit" class="btn-pill btn-pill-dark w-full justify-center">Buy Now</button>
                            </form>
                        @endif

                        @if(in_array('rent', $product->type))
                            <a href="{{ route('rental.create', $product->id) }}" class="btn-pill btn-pill-soft w-full justify-center">Request Rental</a>
                        @endif

                        @if(in_array('swap', $product->type))
                            <form action="{{ route('swap.request.form', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-pill btn-pill-soft w-full justify-center">Propose Swap</button>
                            </form>
                        @endif

                        @if(in_array('sell', $product->type) && $product->quantity > 0)
                            <form action="{{ route('cart.store', $product->id) }}" method="POST" class="grid grid-cols-[1fr_auto] gap-2">
                                @csrf
                                <input type="number" name="quantity" min="1" max="{{ $product->quantity }}" value="1" class="input">
                                <button type="submit" class="btn-pill btn-pill-dark !px-4">Add to Cart</button>
                            </form>
                        @endif
                    </div>
                @endif
            @else
                <div class="mt-6 alert-error">
                    <p class="font-medium">Please log in to buy, rent, or swap this listing.</p>
                    <a href="{{ route('login') }}" class="mt-1 inline-block text-sm font-semibold text-red-800 underline">Go to Login</a>
                </div>
            @endauth
        </article>
    </section>
</div>
@endsection
