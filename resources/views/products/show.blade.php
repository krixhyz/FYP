@extends('layouts.app')

@section('content')
<!-- Back Link -->
<div class="px-8 md:px-16 py-8">
    <a href="{{ route('products.index') }}" class="font-space text-xs font-bold uppercase tracking-widest text-[#444746] hover:text-[#006a38] flex items-center gap-1.5">
        ← Back to Gallery
    </a>
</div>

<!-- Product Detail Layout -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 px-8 md:px-16 pb-12">
    <!-- Left Column — Image Gallery -->
    <div class="lg:col-span-1">
        @php $allImages = array_filter(array_unique(array_merge($product->images ?? [], $product->image ? [$product->image] : []))); @endphp
        <div class="aspect-square w-full object-cover bg-[#e8e8e8] rounded-lg overflow-hidden" id="mainImage">
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
            <div class="flex gap-2 p-3">
                @foreach($allImages as $img)
                    <img src="{{ asset('storage/' . $img) }}"
                         alt="thumbnail"
                         onclick="document.getElementById('mainImg').src='{{ asset('storage/' . $img) }}'"
                         class="w-14 h-14 object-cover bg-[#e8e8e8] cursor-pointer outline outline-2 outline-transparent hover:outline-[#006a38] rounded transition">
                @endforeach
            </div>
        @endif
    </div>

    <!-- Center Column — Product Details -->
    <div class="lg:col-span-1">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Listing</p>
        <h1 class="font-space font-bold text-3xl text-[#1a1c1c] mb-1">{{ $product->title }}</h1>
        <p class="font-manrope text-sm text-[#444746] mb-4">{{ ucfirst($product->category->name ?? 'General') }}</p>

        <!-- Product Detail Container with Alpine.js State -->
        <div x-data="{ mode: '' }">

            <!-- Type Chips (mode selector) -->
            <div class="flex gap-2 mb-6">
                <button @click="mode='buy'"
                        :class="mode==='buy' ? 'bg-[#006a38] text-white' : 'bg-[#e2e2e2] text-[#1a1c1c]'"
                        class="text-[11px] font-space font-bold uppercase tracking-[0.05em] px-3 py-1.5 border-0 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        @if(!in_array('sell', $product->type)) disabled @endif>
                    Buy
                </button>

                <button @click="mode='rent'"
                        :class="mode==='rent' ? 'bg-[#006a38] text-white' : 'bg-[#e2e2e2] text-[#1a1c1c]'"
                        class="text-[11px] font-space font-bold uppercase tracking-[0.05em] px-3 py-1.5 border-0 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        @if(!in_array('rent', $product->type)) disabled @endif>
                    Rent
                </button>

                <button @click="mode='swap'"
                        :class="mode==='swap' ? 'bg-[#006a38] text-white' : 'bg-[#e2e2e2] text-[#1a1c1c]'"
                        class="text-[11px] font-space font-bold uppercase tracking-[0.05em] px-3 py-1.5 border-0 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        @if(!in_array('swap', $product->type)) disabled @endif>
                    Swap
                </button>
            </div>
            
            <!-- Section Heading for Rental Products (only visible in rent mode) -->
            @if(in_array('rent', $product->type))
                <p :style="mode==='rent' ? '' : 'display: none'" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">{{ count($product->type) > 1 ? 'Rent Option' : 'Rent' }}</p>
            @endif

            <!-- Price Display (buy mode only, sell products only) -->
            @if(in_array('sell', $product->type))
                <div :style="mode==='buy' ? '' : 'display: none'">
                    <p class="font-space font-bold text-2xl text-[#006a38] mb-6">Rs. {{ number_format($product->price, 2) }}</p>
                </div>
            @endif

            

            <!-- Swap Base Price Display (swap mode only) -->
            @if(in_array('swap', $product->type))
                <div :style="mode==='swap' ? '' : 'display: none'">
                    <p class="font-space font-bold text-2xl text-[#006a38] mb-6">Base Price: Rs. {{ number_format($product->swap_base_price ?? 0, 2) }}</p>
                </div>
            @endif

            <!-- Rental Info Display -->
            @if(in_array('rent', $product->type) && $product->rentals)
                <div :style="mode==='rent' ? '' : 'display: none'" class="mb-6">
                    <div class="bg-[#f3f3f3] divide-y divide-[rgba(189,202,189,0.2)]">
                        <div class="flex justify-between px-4 py-3">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Daily Rent</p>
                            <p class="font-space font-bold text-lg text-[#006a38]">Rs. {{ number_format($product->rentals->rent_fare ?? 0, 2) }}</p>
                        </div>
                        <div class="flex justify-between px-4 py-3">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Security Deposit</p>
                            <p class="font-manrope text-sm text-[#1a1c1c]">Rs. {{ number_format($product->rentals->rent_deposit ?? 0, 2) }}</p>
                        </div>
                        <div class="flex justify-between px-4 py-3">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Max Duration</p>
                            <p class="font-manrope text-sm text-[#1a1c1c]">{{ $product->rent_duration ?? $product->rentals->available_duration ?? 'N/A' }} days</p>
                        </div>
                        <div class="flex justify-between px-4 py-3">
                            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Available From</p>
                            <p class="font-manrope text-sm text-[#1a1c1c]">{{ $product->rentals->available_from ? \Carbon\Carbon::parse($product->rentals->available_from)->format('M d, Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Description -->
            <div class="mb-8">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Description</p>
                <p class="font-manrope text-base text-[#1a1c1c] leading-relaxed">{{ $product->description }}</p>
            </div>

            <!-- Transaction Area (Alpine.js) -->
            @if($product->status === 'available')
                @if(Auth::check() && Auth::id() !== $product->user_id)
                    <div class="space-y-4">
                        <!-- Single Shared Quantity Input (hidden for swap) -->
                        <div :style="mode==='buy' ? '' : 'display: none'">
                            <label class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Quantity</label>
                            <div class="flex items-center gap-0">
                                <button type="button" class="qty-minus w-10 h-10 bg-white border-2 border-gray-300 font-space font-bold text-lg flex items-center justify-center hover:border-[#006a38]">−</button>
                                <input type="number" id="quantityInput" name="quantity" value="1" min="1" max="{{ $product->quantity }}" readonly class="w-20 h-10 bg-[#f3f3f3] border-0 border-b-2 border-gray-400 text-center font-manrope text-sm focus:border-[#006a38] focus:outline-none cursor-not-allowed">
                                <button type="button" class="qty-plus w-10 h-10 bg-white border-2 border-gray-300 font-space font-bold text-lg flex items-center justify-center hover:border-[#006a38]">+</button>
                            </div>
                            <p class="font-manrope text-xs text-[#444746] mt-1">{{ $product->quantity }} available</p>
                        </div>

                        <!-- BUY Actions (only for sell-type products) -->
                        @if(in_array('sell', $product->type))
                            <div :style="mode==='buy' ? '' : 'display: none'" class="flex flex-col gap-3">
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
                        @endif

                        <!-- RENT Action -->
                        @if(in_array('rent', $product->type))
                            <div :style="mode==='rent' ? '' : 'display: none'">
                                <a href="{{ route('rental.create', $product->id) }}" class="w-full block bg-gradient-to-br from-[#006a38] to-[#09864a] text-white py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all text-center">Request Rental</a>
                            </div>
                        @endif

                        <!-- SWAP Action -->
                        @if(in_array('swap', $product->type))
                            <div :style="mode==='swap' ? '' : 'display: none'">
                                <a href="{{ route('swap.request.form', $product->id) }}" class="w-full block bg-gradient-to-br from-[#006a38] to-[#09864a] text-white py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all text-center">Propose Swap</a>
                            </div>
                        @endif
                    </div>
                @elseif(!Auth::check())
                    <div class="bg-[#ba1a1a] text-white p-4 rounded-none">
                        <p class="font-manrope font-medium">Please log in to buy, rent, or swap this listing.</p>
                        <a href="{{ route('login') }}" class="mt-2 inline-block font-space font-bold uppercase text-sm hover:opacity-80">Go to Login</a>
                    </div>
                @elseif(Auth::id() === $product->user_id)
                    <div class="bg-[#006a38] text-white p-4 rounded-none">
                        <p class="font-manrope font-medium">This is your listing. You can't transact with your own products.</p>
                        <a href="{{ route('products.edit', $product->id) }}" class="mt-2 inline-block font-space font-bold uppercase text-sm hover:opacity-80 underline">Edit Listing</a>
                    </div>
                @endif
            @else
                <div class="bg-[#fbbf24] text-[#663c00] p-4 rounded-none">
                    <p class="font-manrope font-medium">This product is no longer available.</p>
                </div>
            @endif

        </div>
        <!-- End Product Detail Container -->
    </div>

    <!-- Right Column — Metadata Sidebar -->
    <div class="lg:col-span-1">
        <!-- Wishlist -->
        <div class="mb-4">
            @auth
                @if(Auth::id() !== $product->user_id)
                    <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST" data-wishlist-action data-product-id="{{ $product->id }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 bg-transparent border-2 border-[#006a38] text-[#006a38] py-2.5 font-space font-bold text-xs uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] transition-all rounded-md">
                            <svg class="h-4 w-4" fill="{{ $isWishlisted ? '#006a38' : 'none' }}" stroke="{{ $isWishlisted ? '#006a38' : '#006a38' }}" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <span>{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}</span>
                        </button>
                    </form>
                @endif
            @endauth
        </div>

        <!-- Owner Info Card -->
        <div class="bg-[#f3f3f3] border border-[rgba(189,202,189,0.2)] rounded-lg p-4 mb-4">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#444746] mb-3">Seller</p>
            <div class="flex items-start gap-3">
                <div class="flex-1">
                    <a href="{{ route('users.show', $product->user->id) }}" class="font-space font-bold text-sm text-[#006a38] hover:text-[#004a29] block">{{ $product->user->name }}</a>
                    @if($product->user->profile_status === 'VERIFIED')
                        <span class="inline-block bg-[#d1fae5] text-[#065f46] text-[9px] font-space font-bold uppercase tracking-[0.05em] px-1.5 py-0.5 mt-1">Verified</span>
                    @endif
                    <p class="font-manrope text-xs text-[#666] mt-2">{{ number_format($ownerAvg, 1) }}/5 <span class="text-[#888]">({{ $ownerCount }})</span></p>
                </div>
            </div>
        </div>

        <!-- Availability Card -->
        <div class="bg-[#f3f3f3] border border-[rgba(189,202,189,0.2)] rounded-lg p-4 mb-4">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#444746] mb-3">Status</p>
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="font-manrope text-xs text-[#666]">Available:</span>
                    <span class="font-space font-bold text-lg text-[#006a38]">{{ $product->quantity }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-manrope text-xs text-[#666]">Listed:</span>
                    <span class="font-manrope text-xs text-[#1a1c1c]">{{ $product->created_at->diffForHumans() }}</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-white">
                    <span class="font-manrope text-xs text-[#666]">Condition:</span>
                    <span class="font-manrope text-xs font-semibold text-[#1a1c1c]">{{ ucfirst(str_replace('_', ' ', $product->condition ?? 'N/A')) }}</span>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('quantityInput');
        const buyQuantity = document.getElementById('buyQuantity');
        const cartQuantity = document.getElementById('cartQuantity');
        const minusBtn = document.querySelector('.qty-minus');
        const plusBtn = document.querySelector('.qty-plus');

        if (quantityInput) {
            const clampQuantity = (value) => {
                const min = parseInt(quantityInput.min || '1', 10);
                const max = parseInt(quantityInput.max || '1', 10);
                const parsed = parseInt(value, 10);

                if (Number.isNaN(parsed)) {
                    return min;
                }

                return Math.min(max, Math.max(min, parsed));
            };

            const syncPostedQuantity = (normalize = true) => {
                const min = parseInt(quantityInput.min || '1', 10);
                const parsed = parseInt(quantityInput.value, 10);
                const safeQty = Number.isNaN(parsed) ? min : parsed;
                const qty = clampQuantity(safeQty);

                if (normalize) {
                    quantityInput.value = qty;
                }

                if (buyQuantity) buyQuantity.value = qty;
                if (cartQuantity) cartQuantity.value = qty;
            };

            quantityInput.addEventListener('blur', function() {
                syncPostedQuantity(true);
            });

            // Quantity stepper buttons
            if (minusBtn) {
                minusBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    quantityInput.value = clampQuantity(quantityInput.value) - 1;
                    syncPostedQuantity();
                });
            }

            if (plusBtn) {
                plusBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    quantityInput.value = clampQuantity(quantityInput.value) + 1;
                    syncPostedQuantity();
                });
            }

            document.querySelectorAll('form[action="{{ route('order.store', $product->id) }}"], form[action="{{ route('cart.store', $product->id) }}"]').forEach((form) => {
                form.addEventListener('submit', function() {
                    syncPostedQuantity();
                });
            });

            syncPostedQuantity();
        }
    });
</script>
@endsection
