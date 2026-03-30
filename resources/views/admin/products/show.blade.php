@extends('layouts.admin')

@section('title', 'Product Details - ' . $product->title)

@section('content')
<div class="space-y-6">
    <!-- Header with back button -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products') }}" class="btn-pill btn-pill-soft !px-3 !py-1">← Back</a>
            <h1 class="text-3xl font-bold">{{ $product->title }}</h1>
        </div>
        <span class="badge {{ $product->flagged ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
            {{ $product->flagged ? 'Flagged' : 'Not Flagged' }}
        </span>
    </div>

    <!-- Product Details Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Product Info Card -->
            <div class="surface-card p-6">
                <h2 class="mb-4 text-xl font-bold">Product Information</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-neutral-500 uppercase tracking-wide">Product ID</p>
                            <p class="mt-1 font-monospace text-lg font-semibold">{{ $product->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-500 uppercase tracking-wide">Owner</p>
                            <p class="mt-1">
                                <a href="{{ route('admin.users.show', $product->user) }}" class="text-blue-600 hover:underline">
                                    {{ $product->user->name ?? 'Unknown User' }}
                                </a>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-500 uppercase tracking-wide">Category</p>
                            <p class="mt-1 capitalize font-semibold">{{ $product->category }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-500 uppercase tracking-wide">Status</p>
                            <p class="mt-1 capitalize font-semibold text-green-600">{{ $product->status }}</p>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <p class="text-sm text-neutral-500 uppercase tracking-wide">Description</p>
                        <p class="mt-2 text-neutral-700 leading-relaxed">{{ $product->description }}</p>
                    </div>

                    <div class="border-t pt-4">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-neutral-500 uppercase tracking-wide">Quantity</p>
                                <p class="mt-1 text-2xl font-bold">{{ $product->quantity ?? 1 }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-neutral-500 uppercase tracking-wide">Price</p>
                                <p class="mt-1 text-2xl font-bold text-primary-600">Rs. {{ number_format($product->price ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-neutral-500 uppercase tracking-wide">Posted</p>
                                <p class="mt-1 text-sm">{{ $product->created_at->format('M j, Y \a\t H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <p class="text-sm text-neutral-500 uppercase tracking-wide">Listing Type</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @if($product->type)
                                @foreach((array) $product->type as $type)
                                    <span class="badge bg-blue-100 text-blue-700">{{ ucfirst($type) }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Images -->
                    @if($product->images || $product->image)
                    <div class="border-t pt-4">
                        <p class="text-sm text-neutral-500 uppercase tracking-wide mb-3">Images</p>
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                            @if($product->image)
                            <div class="overflow-hidden rounded border border-neutral-200">
                                <img src="{{ asset('storage/' . $product->image) }}" alt="Cover" class="h-32 w-full object-cover">
                            </div>
                            @endif
                            @if($product->images)
                                @foreach((array) $product->images as $image)
                                <div class="overflow-hidden rounded border border-neutral-200">
                                    <img src="{{ asset('storage/' . $image) }}" alt="Product" class="h-32 w-full object-cover">
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Rental Information (if applicable) -->
            @php
                $rentalInfo = $product->rentals;
            @endphp
            @if(in_array('rent', (array)($product->type ?? [])) && $rentalInfo)
            <div class="surface-card p-6">
                <h2 class="mb-4 text-xl font-bold">Rental Information</h2>
                <div class="space-y-3 pb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-neutral-500 uppercase tracking-wide">Rent Fare</p>
                            <p class="mt-1 font-semibold">Rs. {{ number_format($rentalInfo->rent_fare ?? 0, 2) }}/{{ $rentalInfo->rent_type ?? 'day' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-500 uppercase tracking-wide">Security Deposit</p>
                            <p class="mt-1 font-semibold">Rs. {{ number_format($rentalInfo->rent_deposit ?? 0, 2) }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-neutral-500 uppercase tracking-wide">From</p>
                            <p class="mt-1">{{ $rentalInfo->available_from?->format('M j, Y') ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-500 uppercase tracking-wide">Available Duration</p>
                            <p class="mt-1">{{ $rentalInfo->available_duration ? $rentalInfo->available_duration . ' day(s)' : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Orders -->
            @if($product->orders->count() > 0)
            <div class="surface-card p-6">
                <h2 class="mb-4 text-xl font-bold">Buy Orders ({{ $product->orders->count() }})</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-neutral-100 text-neutral-700 uppercase text-xs tracking-[0.12em]">
                            <tr>
                                <th class="p-3 text-left">Buyer</th>
                                <th class="p-3 text-left">Quantity</th>
                                <th class="p-3 text-left">Amount</th>
                                <th class="p-3 text-left">Status</th>
                                <th class="p-3 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200">
                            @foreach($product->orders as $order)
                            <tr>
                                <td class="p-3 font-semibold">{{ $order->buyer?->name ?? 'Unknown' }}</td>
                                <td class="p-3">{{ $order->quantity ?? 1 }}</td>
                                <td class="p-3 font-semibold">Rs. {{ number_format($order->total_price ?? 0, 2) }}</td>
                                <td class="p-3">
                                    <span class="status-chip {{ $order->status === 'completed' ? 'status-success' : 'status-warning' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="p-3 text-sm text-neutral-600">{{ $order->created_at?->format('M j, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Reviews -->
            @if($reviews->count() > 0)
            <div class="surface-card p-6">
                <h2 class="mb-4 text-xl font-bold">Reviews ({{ $reviews->count() }})</h2>
                <div class="space-y-4">
                    @foreach($reviews as $review)
                    <div class="border-b py-3 last:border-b-0">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ $review->reviewer?->name ?? 'Anonymous' }}</p>
                                <p class="text-sm text-neutral-600">{{ $review->created_at?->format('M j, Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold">★ {{ $review->rating }}/5</p>
                            </div>
                        </div>
                        <p class="mt-2 text-neutral-700">{{ $review->body ?: 'No comment provided.' }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Disputes -->
            @if($disputes->count() > 0)
            <div class="surface-card border-2 border-red-200 p-6">
                <h2 class="mb-4 text-xl font-bold text-red-700">Disputes ({{ $disputes->count() }})</h2>
                <div class="space-y-4">
                    @foreach($disputes as $dispute)
                    <div class="border-b border-red-100 py-3 last:border-b-0">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ $dispute->reporter?->name ?? 'Unknown' }}</p>
                                <p class="text-sm text-neutral-600">{{ $dispute->subject }}</p>
                                <p class="mt-1 text-sm text-neutral-700">{{ $dispute->description }}</p>
                            </div>
                            <span class="badge bg-red-100 text-red-700">{{ ucfirst($dispute->status) }}</span>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn-pill btn-pill-soft text-xs">View</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Rental Requests -->
            @if($rentalRequests->count() > 0)
            <div class="surface-card p-6">
                <h2 class="mb-4 text-xl font-bold">Rental Requests ({{ $rentalRequests->count() }})</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-neutral-100 text-neutral-700 uppercase text-xs tracking-[0.12em]">
                            <tr>
                                <th class="p-3 text-left">Requester</th>
                                <th class="p-3 text-left">Duration</th>
                                <th class="p-3 text-left">Status</th>
                                <th class="p-3 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200">
                            @foreach($rentalRequests as $request)
                            <tr>
                                <td class="p-3 font-semibold">{{ $request->renter?->name ?? 'Unknown' }}</td>
                                <td class="p-3">{{ $request->rental_duration ?? 'N/A' }} days</td>
                                <td class="p-3">
                                    <span class="status-chip {{ $request->status === 'approved' ? 'status-success' : 'status-warning' }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td class="p-3 text-sm text-neutral-600">{{ $request->created_at?->format('M j, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Swap Requests -->
            @if($swapRequests->count() > 0)
            <div class="surface-card p-6">
                <h2 class="mb-4 text-xl font-bold">Swap Requests ({{ $swapRequests->count() }})</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-neutral-100 text-neutral-700 uppercase text-xs tracking-[0.12em]">
                            <tr>
                                <th class="p-3 text-left">Requester</th>
                                <th class="p-3 text-left">Status</th>
                                <th class="p-3 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200">
                            @foreach($swapRequests as $swap)
                            <tr>
                                <td class="p-3 font-semibold">{{ $swap->requester?->name ?? 'Unknown' }}</td>
                                <td class="p-3">
                                    <span class="status-chip {{ $swap->status === 'accepted' ? 'status-success' : 'status-warning' }}">
                                        {{ ucfirst(str_replace('_', ' ', $swap->status)) }}
                                    </span>
                                </td>
                                <td class="p-3 text-sm text-neutral-600">{{ $swap->created_at?->format('M j, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-4">
            <div class="surface-card p-4">
                <h3 class="mb-3 font-bold">Actions</h3>
                <div class="space-y-2">
                    @if(!$product->flagged)
                    <form method="POST" action="{{ route('admin.products.flag', $product) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-pill w-full justify-center !border-amber-600 !text-amber-600 hover:!bg-amber-600 hover:!text-white">
                            🚩 Flag Product
                        </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.products.unflag', $product) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-pill btn-pill-soft w-full justify-center">
                            ✓ Unflag Product
                        </button>
                    </form>
                    @endif

                    <form method="POST" action="{{ route('admin.products.delete', $product) }}" onsubmit="return confirm('Are you sure? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-pill w-full justify-center !border-red-600 !text-red-600 hover:!bg-red-600 hover:!text-white">
                            🗑️ Delete Product
                        </button>
                    </form>

                    <a href="{{ route('admin.users.show', $product->user) }}" class="btn-pill btn-pill-soft w-full justify-center text-center">
                        👤 View Seller
                    </a>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="surface-card p-4">
                <h3 class="mb-3 font-bold">Summary</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-neutral-600">Total Orders:</span>
                        <span class="font-semibold">{{ $product->orders->count() }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-neutral-600">Reviews:</span>
                        <span class="font-semibold">{{ $reviews->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-600">Disputes:</span>
                        <span class="font-semibold text-red-600">{{ $disputes->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-600">Rental Requests:</span>
                        <span class="font-semibold">{{ $rentalRequests->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-600">Swap Requests:</span>
                        <span class="font-semibold">{{ $swapRequests->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
