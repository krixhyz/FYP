@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-br from-red-50 to-pink-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <h1 class="text-3xl font-bold text-gray-900">My Wishlist</h1>
                <span class="bg-red-100 text-red-700 text-sm font-semibold px-3 py-1 rounded-full">
                    {{ $wishlistItems->count() }} {{ Str::plural('item', $wishlistItems->count()) }}
                </span>
            </div>
            <a href="{{ route('products.index') }}"
               class="text-sm text-indigo-600 hover:text-indigo-800 font-medium hover:underline">
                ← Browse Products
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if($wishlistItems->isEmpty())
            <div class="text-center py-24 bg-white rounded-2xl shadow-sm">
                <svg class="w-24 h-24 mx-auto text-red-200 mb-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <h2 class="text-xl font-semibold text-gray-600 mb-2">Your wishlist is empty</h2>
                <p class="text-gray-400 mb-6">Save items you love to revisit them later.</p>
                <a href="{{ route('products.index') }}"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition">
                    Explore Products
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($wishlistItems as $item)
                    @php $product = $item->product; @endphp
                    <div class="relative bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 group transform hover:-translate-y-1">

                        {{-- Remove from Wishlist --}}
                        <div class="absolute top-3 right-3 z-10">
                            <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        title="Remove from wishlist"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-red-500 hover:bg-red-600 shadow-md transition">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </button>
                            </form>
                        </div>

                        {{-- Product Image --}}
                        <a href="{{ route('products.show', $product->id) }}" class="block">
                            <div class="h-52 bg-gray-100 overflow-hidden">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}"
                                         alt="{{ $product->title }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                @else
                                    <div class="flex items-center justify-center h-full text-gray-300">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 
                                                  012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 
                                                  00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 truncate mb-1">{{ $product->title }}</h3>
                                <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ $product->description }}</p>

                                <div class="flex flex-wrap gap-1 mb-3">
                                    @if(in_array('sell', $product->type ?? []))
                                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-semibold">Sale</span>
                                    @endif
                                    @if(in_array('rent', $product->type ?? []))
                                        <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-0.5 rounded-full font-semibold">Rent</span>
                                    @endif
                                    @if(in_array('swap', $product->type ?? []))
                                        <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full font-semibold">Swap</span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between">
                                    @if(in_array('sell', $product->type ?? []))
                                        <span class="text-xl font-bold text-blue-700">Rs. {{ number_format($product->price, 2) }}</span>
                                    @else
                                        <span class="text-sm text-gray-400">No sale price</span>
                                    @endif
                                    <span class="text-xs
                                        {{ $product->status === 'available' ? 'text-green-600 font-semibold' : 'text-red-500' }}">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                </div>

                                <p class="text-xs text-gray-400 mt-2">
                                    By {{ $product->user?->name ?? 'Unknown' }}
                                    · Saved {{ $item->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
