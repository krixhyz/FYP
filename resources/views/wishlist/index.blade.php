@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div>
                <p class="section-kicker">Personal</p>
                <h1 class="section-title mt-1">My Wishlist</h1>
            </div>
            <span class="status-chip status-error">
                {{ $wishlistItems->count() }} {{ Str::plural('item', $wishlistItems->count()) }}
            </span>
        </div>
        <a href="{{ route('products.index') }}" class="btn-pill btn-pill-soft">Browse Products</a>
    </div>

    @if(session('success'))
        <div class="border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if($wishlistItems->isEmpty())
        <div class="surface-card p-14 text-center">
            <h2 class="text-xl font-bold text-neutral-700">Your wishlist is empty</h2>
            <p class="mt-2 text-sm text-neutral-500">Save items you value to revisit them later.</p>
            <a href="{{ route('products.index') }}" class="btn-pill btn-pill-dark mt-6">Explore Products</a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($wishlistItems as $item)
                @php $product = $item->product; @endphp
                <article class="group surface-card overflow-hidden transition hover:-translate-y-1">
                    <div class="relative h-52 overflow-hidden bg-neutral-100">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @else
                            <div class="flex h-full items-center justify-center text-neutral-300">
                                <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.6-4.6a2 2 0 012.8 0L16 16m-2-2l1.6-1.6a2 2 0 012.8 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif

                        <div class="absolute right-3 top-3 z-10">
                            <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit" title="Remove from wishlist" class="flex h-8 w-8 items-center justify-center bg-red-500 text-white hover:bg-red-600">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <a href="{{ route('products.show', $product->id) }}" class="block p-4">
                        <h3 class="truncate text-lg font-bold">{{ $product->title }}</h3>
                        <p class="mt-1 line-clamp-2 text-sm text-neutral-600">{{ $product->description }}</p>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @if(in_array('sell', $product->type ?? []))
                                <span class="status-chip status-info">Buy</span>
                            @endif
                            @if(in_array('rent', $product->type ?? []))
                                <span class="status-chip status-warning">Rent</span>
                            @endif
                            @if(in_array('swap', $product->type ?? []))
                                <span class="status-chip status-success">Swap</span>
                            @endif
                        </div>

                        <div class="mt-4 flex items-end justify-between">
                            @if(in_array('sell', $product->type ?? []))
                                <p class="text-xl font-extrabold">Rs. {{ number_format($product->price, 2) }}</p>
                            @else
                                <p class="text-sm font-semibold text-neutral-500">Exchange only</p>
                            @endif
                            <span class="text-xs font-semibold {{ $product->status === 'available' ? 'text-emerald-700' : 'text-red-600' }}">
                                {{ ucfirst($product->status) }}
                            </span>
                        </div>

                        <p class="mt-2 text-xs text-neutral-500">By {{ $product->user?->name ?? 'Unknown' }} | Saved {{ $item->created_at->diffForHumans() }}</p>
                    </a>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
