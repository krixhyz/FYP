@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <div class="flex items-start gap-5">
                <div class="w-24 h-24 rounded-full bg-green-600 flex items-center justify-center text-white text-4xl font-semibold shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-3 mb-2">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                        @if($user->email_verified_at)
                            <span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-3 py-1 text-sm font-medium">Verified</span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-gray-600 mb-3">
                        @php $roundedStars = $avgRating ? round($avgRating) : 0; @endphp
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= $roundedStars ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="font-semibold text-gray-800">{{ $avgRating ? number_format($avgRating, 1) : 'No rating yet' }}</span>
                        <span class="text-sm">({{ $reviewsCount }} {{ \Illuminate\Support\Str::plural('review', $reviewsCount) }})</span>
                    </div>

                    <p class="text-gray-600 text-sm">
                        @if($location)
                            {{ $location }}
                        @else
                            Location not specified
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-3xl font-bold text-gray-900">{{ $activeListingsCount }}</p>
                <p class="text-gray-600 mt-1">Active Listings</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-3xl font-bold text-gray-900">{{ $completedDeals }}</p>
                <p class="text-gray-600 mt-1">Completed Deals</p>
                <p class="text-xs text-gray-500 mt-2">
                    Sales {{ $completedSales }} · Rentals {{ $completedRentals }} · Swaps {{ $completedSwaps }}
                </p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-3xl font-bold text-gray-900">{{ $avgRating ? number_format($avgRating, 1) : '—' }}</p>
                <p class="text-gray-600 mt-1">Average Rating</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-3xl font-bold text-gray-900">{{ $user->created_at?->format('Y') }}</p>
                <p class="text-gray-600 mt-1">Member Since</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-3xl font-bold text-gray-900">Active Listings</h2>
                <span class="text-sm text-gray-500">{{ $activeListingsCount }} {{ \Illuminate\Support\Str::plural('item', $activeListingsCount) }}</span>
            </div>

            @if($activeListings->isEmpty())
                <p class="text-gray-500">No active listings.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($activeListings as $listing)
                        <a href="{{ route('products.show', $listing->id) }}" class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow transition block">
                            <div class="h-44 bg-gray-100">
                                @if($listing->image)
                                    <img src="{{ asset('storage/' . $listing->image) }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $listing->title }}</h3>
                                <p class="text-sm text-gray-600 mt-2">{{ \Illuminate\Support\Str::limit($listing->description, 70) }}</p>
                                <div class="mt-3 flex items-center justify-between text-sm">
                                    <span class="text-purple-700 font-semibold">Rs. {{ number_format((float) $listing->price, 2) }}</span>
                                    <span class="text-gray-500">Qty: {{ $listing->quantity }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-3xl font-bold text-gray-900 mb-5">Recent Reviews</h2>

            @if($recentReviews->isEmpty())
                <p class="text-gray-500">No reviews yet.</p>
            @else
                <div class="space-y-5">
                    @foreach($recentReviews as $review)
                        <div class="border-b border-gray-100 pb-5 last:border-b-0 last:pb-0">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $review->reviewer?->name ?? 'User' }}</p>
                                    <div class="flex mt-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                </div>

                                <span class="text-sm text-gray-500">{{ $review->created_at->format('M j, Y') }}</span>
                            </div>

                            @if($review->body)
                                <p class="mt-2 text-gray-700">{{ $review->body }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection