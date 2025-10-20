<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">
             Explore Available Products
        </h1>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @forelse ($products as $product)
                @if ($product->status === 'available' && Auth::id() !== $product->user_id)
                    <div class="bg-white shadow-md hover:shadow-lg rounded-lg overflow-hidden border border-gray-100 transition-transform transform hover:scale-105 duration-200">
                        
                        {{-- Product Image --}}
                        <div class="relative bg-gray-50 h-32 sm:h-36 flex items-center justify-center">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" 
                                     alt="{{ $product->title }}" 
                                     class="h-full w-auto object-contain p-2">
                            @else
                                <div class="text-gray-400 text-sm">No Image</div>
                            @endif
                        </div>

                        {{-- Product Info --}}
                        <div class="p-3">
                            <h2 class="text-sm font-semibold text-gray-800 truncate">{{ $product->title }}</h2>
                            <p class="text-xs text-gray-600 mt-1 truncate">{{ Str::limit($product->description, 40) }}</p>
                            <p class="text-xs text-gray-500 mt-1 capitalize"><strong>Category:</strong> {{ $product->category }}</p>
                            <p class="font-bold text-sm text-blue-700 mt-1">Rs. {{ number_format($product->price, 2) }}</p>

                            {{-- Actions --}}
                            @auth
                                <div class="mt-3 flex flex-wrap gap-1">
                                    {{-- Buy --}}
                                    @if(in_array('sell', $product->type))
                                        <form action="{{ route('order.store', $product->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="type" value="buy">
                                            <button class="btn btn-sm bg-blue-600 hover:bg-blue-700 text-white text-xs px-2 py-1 rounded">
                                                Buy
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Rent --}}
                                    @if(in_array('rent', $product->type))
                                        <a href="{{ route('rental.create', $product->id) }}" 
                                           class="btn btn-sm bg-yellow-500 hover:bg-yellow-600 text-black font-semibold text-xs px-2 py-1 rounded">
                                            Rent
                                        </a>
                                    @endif

                                    {{-- Swap --}}
                                    @if(in_array('swap', $product->type))
                                        <form action="{{ route('order.store', $product->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="type" value="swap">
                                            <button class="btn btn-sm bg-green-600 hover:bg-green-700 text-white text-xs px-2 py-1 rounded">
                                                Swap
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Add to Cart --}}
                                    <form action="{{ route('cart.store', $product->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="type" value="buy">
                                        <button class="btn btn-sm bg-gray-800 hover:bg-gray-900 text-white text-xs px-2 py-1 rounded">
                                            🛒
                                        </button>
                                    </form>
                                </div>
                            @else
                                <p class="text-xs text-red-500 mt-2 text-center">Login to Buy / Rent / Swap</p>
                            @endauth
                        </div>
                    </div>
                @endif
            @empty
                <div class="col-span-full text-center py-10 text-gray-500">
                    <p class="text-lg">No products available right now </p>
                    <p class="text-sm">Check back later for more listings.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
