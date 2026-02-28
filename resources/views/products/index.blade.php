@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Explore Products</h1>
            <p class="text-gray-600">Discover amazing products available for buy, rent, or swap</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div class="md:col-span-2">
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input id="city" name="city" type="text" value="{{ request('city') }}"
                           class="w-full border-gray-300 rounded-lg text-sm p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                           placeholder="Search by city">
                </div>
                <div class="md:col-span-2">
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location Text</label>
                    <input id="location" name="location" type="text" value="{{ request('location') }}"
                           class="w-full border-gray-300 rounded-lg text-sm p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                           placeholder="Area, landmark, municipality">
                </div>
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                        Search
                    </button>
                    <a href="{{ route('products.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse ($products as $product)
                <a href="{{ route('products.show', $product->id) }}"
                   class="group bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden transform hover:-translate-y-2">

                    {{-- Product Image --}}
                    <div class="relative h-56 bg-gray-100 overflow-hidden">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                 alt="{{ $product->title }}"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        @else
                            <div class="flex items-center justify-center h-full text-gray-400">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0
                                          012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0
                                          00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif

                        {{-- Badges --}}
                        <div class="absolute top-3 left-3 flex flex-wrap gap-1">
                            @if(in_array('sell', $product->type))
                                <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full font-semibold">Sale</span>
                            @endif
                            @if(in_array('rent', $product->type))
                                <span class="bg-yellow-500 text-black text-xs px-2 py-1 rounded-full font-semibold">Rent</span>
                            @endif
                            @if(in_array('swap', $product->type))
                                <span class="bg-green-600 text-white text-xs px-2 py-1 rounded-full font-semibold">Swap</span>
                            @endif
                        </div>

                        @if($product->quantity > 0 && $product->quantity <= 5)
                            <div class="absolute top-3 right-3">
                                <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full font-semibold">
                                    Only {{ $product->quantity }} left
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Product Info --}}
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-900 truncate mb-1">{{ $product->title }}</h3>
                        <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $product->description }}</p>

                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs text-gray-500 capitalize">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0
                                          010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0
                                          013 12V7a4 4 0 014-4z" />
                                </svg>
                                {{ $product->category }}
                            </span>
                            @if(in_array('sell', $product->type))
                                <span class="text-xl font-bold text-blue-700">Rs. {{ number_format($product->price, 2) }}</span>
                            @endif
                        </div>

                        <div class="text-xs text-gray-500 mb-2">
                            <span>{{ $product->city ?: 'Location unavailable' }}</span>
                            @if(isset($product->distance_km))
                                <span class="ml-2 text-blue-700 font-semibold">{{ number_format($product->distance_km, 1) }} km away</span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $product->quantity }} available</span>
                            <span class="text-blue-600 font-medium group-hover:text-blue-800">View Details →</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-20">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 
                              01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 
                              00-.707.293l-2.414 2.414a1 1 0 
                              01-.707.293h-3.172a1 1 0 
                              01-.707-.293l-2.414-2.414A1 1 0 
                              006.586 13H4" />
                    </svg>
                    <p class="text-xl text-gray-500 mb-2">No products available</p>
                    <p class="text-sm text-gray-400">Check back later for new listings</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
