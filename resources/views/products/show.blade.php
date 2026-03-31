@extends('layouts.app')

@section('content')
<!-- Back Link -->
<div class="px-8 md:px-16 py-8">
    <a href="{{ route('products.index') }}" class="font-space text-xs font-bold uppercase tracking-widest text-[#444746] hover:text-[#006a38] flex items-center gap-1.5">
        ← Back to Gallery
    </a>
</div>

<!-- Product Detail Layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-0 px-8 md:px-16 pb-12">
    <!-- Left Column — Image -->
    <div class="bg-[#f3f3f3] lg:pr-8">
        @php $allImages = array_filter(array_unique(array_merge($product->images ?? [], $product->image ? [$product->image] : []))); @endphp
        <div class="aspect-square w-full object-cover bg-[#e8e8e8]" id="mainImage">
            @if(count($allImages) > 0)
                <img id="mainImg" src="{{ asset('storage/' . reset($allImages)) }}"
                     alt="{{ $product->title }}"
                     class="h-full w-full object-cover">
            @else
                <div class="flex h-full items-center justify-center text-[#444746]">
                    <svg class="h-20 w-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif
        </div>

        @if(count($allImages) > 1)
            <div class="flex gap-2 p-4">
                @foreach($allImages as $img)
                    <img src="{{ asset('storage/' . $img) }}"
                         alt="thumbnail"
                         onclick="document.getElementById('mainImg').src='{{ asset('storage/' . $img) }}'"
                         class="w-16 h-16 object-cover bg-[#e8e8e8] cursor-pointer outline outline-2 outline-transparent hover:outline-[#006a38]">
                    @endforeach
            </div>
        @endif
    </div>

    <!-- Right Column — Detail Panel -->
    <div class="bg-white p-8 md:p-12">
        <!-- Header -->
        <a href="{{ route('products.index') }}" class="font-space text-xs font-bold uppercase tracking-widest text-[#444746] hover:text-[#006a38] flex items-center gap-1.5 mb-8 hidden lg:flex">
            ← Back to Gallery
        </a>

        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Listing</p>
        <h1 class="font-space font-bold text-3xl text-[#1a1c1c] mb-1">{{ $product->title }}</h1>
        <p class="font-manrope text-sm text-[#444746] mb-3">Category: {{ ucfirst($product->category) }}</p>

        <!-- Type Chips (now mode selector) -->
        <div class="flex gap-2 mb-6" x-data="{ mode: 'buy' }">
            @if(in_array('sell', $product->type))
                <button @click="mode='buy'" :class="mode==='buy' ? 'bg-[#006a38] text-white' : 'bg-[#e2e2e2] text-[#1a1c1c]'" class="text-[11px] font-space font-bold uppercase tracking-[0.05em] px-3 py-1.5 border-0 transition-colors">Buy</button>
            @endif
            @if(in_array('rent', $product->type))
                <button @click="mode='rent'" :class="mode==='rent' ? 'bg-[#006a38] text-white' : 'bg-[#e2e2e2] text-[#1a1c1c]'" class="text-[11px] font-space font-bold uppercase tracking-[0.05em] px-3 py-1.5 border-0 transition-colors">Rent</button>
            @endif
            @if(in_array('swap', $product->type))
                <button @click="mode='swap'" :class="mode==='swap' ? 'bg-[#006a38] text-white' : 'bg-[#e2e2e2] text-[#1a1c1c]'" class="text-[11px] font-space font-bold uppercase tracking-[0.05em] px-3 py-1.5 border-0 transition-colors">Swap</button>
            @endif
        </div>

        <!-- Price Display -->
        @if(in_array('sell', $product->type))
            <p class="font-space font-bold text-2xl text-[#006a38] mb-6">Rs. {{ number_format($product->price, 2) }}</p>
        @endif

        <!-- Metadata Table -->
        <div class="bg-[#f3f3f3] mb-6">
            <div class="flex justify-between px-4 py-3 border-b border-[rgba(189,202,189,0.2)] last:border-b-0">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Available Qty</p>
                <p class="font-manrope text-sm text-[#1a1c1c]">{{ $product->quantity }}</p>
            </div>
            <div class="flex justify-between px-4 py-3 border-b border-[rgba(189,202,189,0.2)] last:border-b-0">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Owner</p>
                <a href="{{ route('users.show', $product->user->id) }}" class="font-manrope text-sm text-[#006a38] hover:text-[#004a29]">{{ $product->user->name }}</a>
            </div>
            @php
                $ownerAvg = \App\Models\Review::where('reviewee_id', $product->user_id)->avg('rating');
                $ownerCount = \App\Models\Review::where('reviewee_id', $product->user_id)->count();
            @endphp
            @if($ownerAvg)
                <div class="flex justify-between px-4 py-3 border-b border-[rgba(189,202,189,0.2)] last:border-b-0">
                    <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Seller Rating</p>
                    <p class="font-manrope text-sm text-[#1a1c1c]">{{ number_format($ownerAvg, 1) }}/5 ({{ $ownerCount }})</p>
                </div>
            @endif
            <div class="flex justify-between px-4 py-3 border-b border-[rgba(189,202,189,0.2)] last:border-b-0">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Listed</p>
                <p class="font-manrope text-sm text-[#1a1c1c]">{{ $product->created_at->diffForHumans() }}</p>
            </div>
        </div>

        <!-- Description -->
        <div class="mb-8">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Description</p>
            <p class="font-manrope text-base text-[#1a1c1c] leading-relaxed">{{ $product->description }}</p>
        </div>

        <!-- Wishlist Icon (top-right) -->
        @auth
            @if(Auth::id() !== $product->user_id)
                <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST" class="absolute top-8 md:top-12 right-8 md:right-12 md:hidden lg:block">
                    @csrf
                    <button type="submit"
                            title="{{ $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}"
                            class="flex h-8 w-8 items-center justify-center transition">
                        <svg class="h-5 w-5" fill="{{ $isWishlisted ? '#006a38' : 'none' }}" stroke="{{ $isWishlisted ? '#006a38' : '#444746' }}" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </button>
                </form>
            @endif
        @endauth

        <!-- Transaction Area (Alpine.js) -->
        @auth
            @if(Auth::id() !== $product->user_id && $product->status === 'available')
                <div x-data="{ mode: 'buy' }" class="space-y-4">
                    <!-- Single Shared Quantity Input (hidden for swap) -->
                    <div x-show="mode !== 'swap'">
                        <label class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Quantity</label>
                        <div class="flex items-center gap-0">
                            <button type="button" class="w-10 h-10 bg-white border-2 border-gray-300 font-space font-bold text-lg flex items-center justify-center hover:border-[#006a38]">−</button>
                            <input type="number" id="quantityInput" name="quantity" value="1" min="1" max="{{ $product->quantity }}" class="w-20 h-10 bg-[#f3f3f3] border-0 border-b-2 border-gray-400 text-center font-manrope text-sm focus:border-[#006a38] focus:outline-none">
                            <button type="button" class="w-10 h-10 bg-white border-2 border-gray-300 font-space font-bold text-lg flex items-center justify-center hover:border-[#006a38]">+</button>
                        </div>
                        <p class="font-manrope text-xs text-[#444746] mt-1">{{ $product->quantity }} available</p>
                    </div>

                    <!-- BUY Actions -->
                    <div x-show="mode==='buy'" class="flex flex-col gap-3">
                        <form action="{{ route('order.store', $product->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <input type="hidden" name="quantity" id="buyQuantity" value="1">
                            <button type="submit" class="w-full bg-gradient-to-br from-[#006a38] to-[#09864a] text-white py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all">Buy Now</button>
                        </form>
                        <form action="{{ route('cart.store', $product->id) }}" method="POST" data-cart-action="add">
                            @csrf
                            <input type="hidden" name="quantity" id="cartQuantity" value="1">
                            <button type="submit" class="w-full bg-transparent border-2 border-[#006a38] text-[#006a38] py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] transition-all">Add to Cart</button>
                        </form>
                    </div>

                    <!-- RENT Action -->
                    <div x-show="mode==='rent'">
                        <a href="{{ route('rental.create', $product->id) }}" class="w-full block bg-gradient-to-br from-[#006a38] to-[#09864a] text-white py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all text-center">Request Rental</a>
                    </div>

                    <!-- SWAP Action -->
                    <div x-show="mode==='swap'">
                        <form action="{{ route('swap.request.form', $product->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-gradient-to-br from-[#006a38] to-[#09864a] text-white py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all">Propose Swap</button>
                        </form>
                    </div>
                </div>
            @endif
        @else
            <div class="bg-[#ba1a1a] text-white p-4 rounded-none">
                <p class="font-manrope font-medium">Please log in to buy, rent, or swap this listing.</p>
                <a href="{{ route('login') }}" class="mt-2 inline-block font-space font-bold uppercase text-sm hover:opacity-80">Go to Login</a>
            </div>
        @endauth
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('quantityInput');
        const buyQuantity = document.getElementById('buyQuantity');
        const cartQuantity = document.getElementById('cartQuantity');

        if (quantityInput) {
            quantityInput.addEventListener('input', function() {
                buyQuantity.value = this.value;
                cartQuantity.value = this.value;
            });

            // Quantity stepper buttons
            const stepperButtons = document.querySelectorAll('[class*="border-gray-300"]');
            stepperButtons.forEach((btn, index) => {
                btn.addEventListener('click', function() {
                    if (index === 0) { // Minus button
                        if (quantityInput.value > quantityInput.min) {
                            quantityInput.value = parseInt(quantityInput.value) - 1;
                        }
                    } else { // Plus button
                        if (quantityInput.value < quantityInput.max) {
                            quantityInput.value = parseInt(quantityInput.value) + 1;
                        }
                    }
                    buyQuantity.value = quantityInput.value;
                    cartQuantity.value = quantityInput.value;
                });
            });
        }
    });
</script>
@endsection
