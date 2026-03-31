@extends('layouts.app')

@section('content')
<!-- Hero Section (removed decorative blobs) -->
<section class="bg-[#f3f3f3] px-8 md:px-16 py-12 mb-8">
    <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-3">Digital Marketplace</p>
    <h1 class="font-space font-bold text-5xl md:text-6xl text-[#1a1c1c] mb-3">Explore Circular Fashion</h1>
    <p class="font-manrope text-base text-[#444746] max-width-lg mb-6" style="max-width: 480px">Browse curated pieces for buying, renting, and swapping. This feed is designed as a live gallery for conscious wardrobe loops.</p>
    <p class="font-space text-sm font-bold uppercase tracking-widest text-[#006a38]">{{ $products->count() }} active listings</p>
</section>

<!-- Search/Filter Block -->
<section class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] px-8 py-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Refine Results</p>
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">
            {{ $products->total() }} result{{ $products->total() === 1 ? '' : 's' }}
        </p>
    </div>

    <form action="{{ route('products.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="search" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Search</label>
            <input id="search" type="search" name="search" value="{{ $search ?? request('search') }}" placeholder="Search title, description, category"
                class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="category" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Category</label>
                <select id="category" name="category" class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full">
                    <option value="">All</option>
                    @foreach (['electronics', 'clothing', 'furniture', 'general'] as $option)
                        <option value="{{ $option }}" @selected(($category ?? request('category')) === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="listing_type" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Type</label>
                <select id="listing_type" name="listing_type" class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full">
                    <option value="">Any</option>
                    @foreach (['sell' => 'Buy', 'rent' => 'Rent', 'swap' => 'Swap'] as $value => $label)
                        <option value="{{ $value }}" @selected(($listingType ?? request('listing_type')) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="min_price" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Min Price</label>
                <input id="min_price" type="number" min="0" step="0.01" name="min_price" value="{{ $minPrice ?? request('min_price') }}"
                    class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full">
            </div>

            <div>
                <label for="max_price" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Max Price</label>
                <input id="max_price" type="number" min="0" step="0.01" name="max_price" value="{{ $maxPrice ?? request('max_price') }}"
                    class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full">
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 md:col-span-2 mt-4">
            <button type="submit" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-6 py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all">Apply</button>
            <a href="{{ route('products.index') }}" class="bg-[#d9e8d9] text-[#1a3a1a] px-[22px] py-[10px] font-space font-medium text-sm uppercase transition-all hover:bg-[#c5dbc5]">Reset</a>
        </div>
    </form>
</section>

<!-- Product Grid -->
<section class="bg-[#f3f3f3] px-8 py-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse ($products as $product)
            <div class="group bg-white shadow-none hover:shadow-[0_20px_40px_rgba(26,28,28,0.06)] outline outline-1 outline-transparent hover:outline-[rgba(189,202,189,0.5)] transition-all duration-150 flex flex-col">
                <div class="relative aspect-[4/3] bg-[#f3f3f3] overflow-hidden">
                    <a href="{{ route('products.show', $product->id) }}" class="absolute inset-0">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                 alt="{{ $product->title }}"
                                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @else
                            <div class="flex h-full items-center justify-center text-[#444746]">
                                <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                    </a>

                    <!-- Chips Row -->
                    <div class="absolute left-3 top-2.5 flex flex-wrap gap-1.5 z-10">
                        @if(in_array('sell', $product->type))
                            <span class="bg-[#e2e2e2] text-[#1a1c1c] text-[11px] font-space font-bold uppercase tracking-[0.05em] px-3 py-1.5">Buy</span>
                        @endif
                        @if(in_array('rent', $product->type))
                            <span class="bg-[#e2e2e2] text-[#1a1c1c] text-[11px] font-space font-bold uppercase tracking-[0.05em] px-3 py-1.5">Rent</span>
                        @endif
                        @if(in_array('swap', $product->type))
                            <span class="bg-[#e2e2e2] text-[#1a1c1c] text-[11px] font-space font-bold uppercase tracking-[0.05em] px-3 py-1.5">Swap</span>
                        @endif
                    </div>

                    <!-- Action Buttons Row (always visible) -->
                    @auth
                        <div class="absolute bottom-0 left-0 right-0 flex gap-2 p-2 bg-gradient-to-t from-black/30 to-transparent z-20">
                            <!-- Wishlist Button -->
                            <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST" class="flex-shrink-0">
                                @csrf
                                <button type="submit"
                                        title="{{ in_array($product->id, $wishlistedIds) ? 'Remove from wishlist' : 'Save to wishlist' }}"
                                        class="flex h-9 w-9 items-center justify-center bg-[#006a38] text-white hover:brightness-110 transition">
                                    <svg class="h-4 w-4"
                                         fill="{{ in_array($product->id, $wishlistedIds) ? 'white' : 'none' }}"
                                         stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </button>
                            </form>

                            <!-- Add to Cart Button -->
                            <form action="{{ route('cart.store', $product->id) }}" method="POST" class="flex-1" data-cart-action="add">
                                @csrf
                                <button type="submit" title="Add to cart" class="w-full flex h-9 items-center justify-center gap-1.5 bg-white text-[#006a38] font-space text-xs font-bold uppercase tracking-wider hover:bg-[#f9f9f9] transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 4v16m8-8H4" />
                                    </svg>
                                    Cart
                                </button>
                            </form>

                            <!-- Buy Button -->
                            <a href="{{ route('products.show', $product->id) }}" class="flex-shrink-0">
                                <button type="button" title="View & Buy" class="flex h-9 px-3 items-center justify-center gap-1 bg-[#006a38] text-white font-space text-xs font-bold uppercase tracking-wider hover:brightness-110 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Get
                                </button>
                            </a>
                        </div>
                    @endauth

                    <!-- Action Buttons for Guests -->
                    @guest
                        <div class="absolute bottom-0 left-0 right-0 flex gap-2 p-2 bg-gradient-to-t from-black/30 to-transparent z-20">
                            <!-- Add to Cart Button (Guest) -->
                            <form action="{{ route('cart.store', $product->id) }}" method="POST" class="flex-1" data-cart-action="add">
                                @csrf
                                <button type="submit" title="Add to cart" class="w-full flex h-9 items-center justify-center gap-1.5 bg-white text-[#006a38] font-space text-xs font-bold uppercase tracking-wider hover:bg-[#f9f9f9] transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 4v16m8-8H4" />
                                    </svg>
                                    Cart
                                </button>
                            </form>

                            <!-- Buy Button (Guest) -->
                            <a href="{{ route('products.show', $product->id) }}" class="flex-shrink-0">
                                <button type="button" title="View & Buy" class="flex h-9 px-3 items-center justify-center gap-1 bg-[#006a38] text-white font-space text-xs font-bold uppercase tracking-wider hover:brightness-110 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Get
                                </button>
                            </a>
                        </div>
                    @endguest
                </div>

                <!-- Card Body -->
                <div class="px-3 pt-2.5">
                    <h3 class="font-space font-bold text-sm text-[#1a1c1c]">{{ $product->title }}</h3>
                </div>
                <div class="px-3 pb-3">
                    <p class="font-space text-sm font-medium text-[#006a38]">
                        @if(in_array('sell', $product->type))
                            Rs. {{ number_format($product->price, 2) }}
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="col-span-full bg-[#f3f3f3] px-8 py-20 text-left">
                <p class="font-space font-bold text-base text-[#1a1c1c] mb-2">No products available</p>
                <p class="font-manrope text-sm text-[#444746] mb-6">Check back soon for fresh listings or adjust your filters.</p>
                <a href="{{ route('products.index') }}" class="bg-[#d9e8d9] text-[#1a3a1a] px-[22px] py-[10px] font-space font-medium text-sm uppercase transition-all hover:bg-[#c5dbc5] inline-block">Clear Filters</a>
            </div>
        @endforelse
    </div>
</section>

<!-- Pagination -->
@if($products->hasPages())
    <section class="bg-white px-8 py-5">
        {{ $products->links() }}
    </section>
@endif

<!-- Recently Viewed Section -->
@auth
    @if($recentlyViewed->count() > 0)
        <section class="px-8 md:px-16 py-8">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-4">Recently Viewed</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach ($recentlyViewed as $rv)
                    <a href="{{ route('products.show', $rv->product->id) }}" class="group bg-white shadow-none hover:shadow-[0_20px_40px_rgba(26,28,28,0.06)] transition-all">
                        <div class="aspect-square bg-[#f3f3f3] overflow-hidden">
                            @if($rv->product->image)
                                <img src="{{ asset('storage/' . $rv->product->image) }}"
                                     alt="{{ $rv->product->title }}"
                                     class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <div class="flex h-full items-center justify-center text-[#444746]">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="px-3 py-2">
                            <p class="font-space font-bold text-xs uppercase text-[#1a1c1c]">{{ $rv->product->title }}</p>
                            @if(in_array('sell', $rv->product->type ?? []))
                                <p class="font-space text-sm font-bold text-[#006a38] mt-1">Rs. {{ number_format($rv->product->price, 2) }}</p>
                            @endif
                            <p class="font-manrope text-[11px] text-[#444746] mt-1">{{ $rv->viewed_at->diffForHumans() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endauth
@endsection
