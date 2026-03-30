@extends('layouts.app')

@section('content')
<div class="space-y-14">
    <section class="surface-card-strong relative overflow-hidden p-6 sm:p-9 lg:p-12">
        <div class="absolute -right-24 -top-24 h-72 w-72 bg-primary-200/30 blur-3xl"></div>
        <div class="absolute -left-20 bottom-0 h-64 w-64 bg-accent-300/35 blur-3xl"></div>

        <div class="relative grid gap-8 lg:grid-cols-[1.25fr_0.75fr] lg:items-end">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-primary-800">Digital Brutalist Gallery</p>
                <h1 class="mt-5 text-4xl font-bold leading-[0.92] text-neutral-900 sm:text-5xl">Explore Circular Fashion</h1>
                <p class="mt-5 max-w-2xl text-base text-neutral-700">Browse curated pieces for buying, renting, and swapping. This feed is designed as a live gallery for conscious wardrobe loops.</p>
            </div>
            <div class="bg-white p-4 text-sm text-neutral-700">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-neutral-500">Inventory Pulse</p>
                <p class="mt-3 text-3xl font-bold text-primary-800">{{ $products->count() }}</p>
                <p class="mt-2 text-sm">active listings in the current collection</p>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @forelse ($products as $product)
            @if ($product->status === 'available' && Auth::id() !== $product->user_id)
                <article class="group relative overflow-hidden bg-white">
                    <a href="{{ route('products.show', $product->id) }}" class="block">
                        <div class="relative h-60 overflow-hidden bg-accent-100">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}"
                                     alt="{{ $product->title }}"
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                            @else
                                <div class="flex h-full items-center justify-center text-neutral-400">
                                    <svg class="h-14 w-14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif

                            <div class="absolute left-3 top-3 flex flex-wrap gap-1">
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

                            @if($product->quantity > 0 && $product->quantity <= 5)
                                <div class="absolute right-3 top-3 bg-red-100 px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.06em] text-red-700">
                                    {{ $product->quantity }} Left
                                </div>
                            @endif

                            @auth
                                <div class="absolute bottom-3 right-3 z-10">
                                    <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                title="{{ in_array($product->id, $wishlistedIds) ? 'Remove from wishlist' : 'Save to wishlist' }}"
                                                class="allow-loop-circle flex h-10 w-10 items-center justify-center transition {{ in_array($product->id, $wishlistedIds) ? 'bg-red-600 text-white' : 'bg-white text-neutral-400' }}">
                                            <svg class="h-4 w-4"
                                                 fill="{{ in_array($product->id, $wishlistedIds) ? 'currentColor' : 'none' }}"
                                                 stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endauth
                        </div>

                        <div class="space-y-3 bg-white p-4">
                            <h3 class="truncate text-lg font-bold text-neutral-900">{{ $product->title }}</h3>
                            <p class="line-clamp-2 text-sm text-neutral-600">{{ $product->description }}</p>

                            <div class="flex items-end justify-between gap-2">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.07em] text-neutral-500">Category</p>
                                    <p class="text-sm font-medium capitalize text-neutral-700">{{ $product->category }}</p>
                                </div>
                                @if(in_array('sell', $product->type))
                                    <p class="text-xl font-bold text-primary-800">Rs. {{ number_format($product->price, 2) }}</p>
                                @endif
                            </div>

                            <div class="bg-accent-100 px-3 py-2 text-xs font-semibold uppercase tracking-[0.05em] text-neutral-700">
                                {{ $product->quantity }} available · View details
                            </div>
                        </div>
                    </a>
                </article>
            @endif
        @empty
            <div class="col-span-full bg-white px-6 py-16 text-center">
                <p class="text-lg font-semibold uppercase tracking-[0.08em] text-neutral-700">No products available</p>
                <p class="mt-2 text-sm text-neutral-500">Check back soon for fresh listings.</p>
            </div>
        @endforelse
    </section>

    @auth
        @if($recentlyViewed->count() > 0)
            <section class="space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-3 bg-accent-100 px-4 py-3">
                    <h2 class="text-2xl font-bold text-neutral-900">Recently Viewed</h2>
                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-neutral-600">Your latest gallery trail</p>
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach ($recentlyViewed as $rv)
                        <a href="{{ route('products.show', $rv->product->id) }}" class="group block overflow-hidden bg-white">
                            <div class="h-32 overflow-hidden bg-accent-100">
                                @if($rv->product->image)
                                    <img src="{{ asset('storage/' . $rv->product->image) }}"
                                         alt="{{ $rv->product->title }}"
                                         class="h-full w-full object-cover transition duration-300 group-hover:scale-110">
                                @else
                                    <div class="flex h-full items-center justify-center text-neutral-400">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="space-y-1 p-3">
                                <p class="truncate text-xs font-semibold uppercase tracking-[0.05em] text-neutral-900">{{ $rv->product->title }}</p>
                                @if(in_array('sell', $rv->product->type ?? []))
                                    <p class="text-sm font-bold text-primary-800">Rs. {{ number_format($rv->product->price, 2) }}</p>
                                @endif
                                <p class="text-[11px] text-neutral-500">{{ $rv->viewed_at->diffForHumans() }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    @endauth
</div>
@endsection
