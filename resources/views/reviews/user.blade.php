@extends('layouts.app')
@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-3xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-2xl font-bold text-indigo-600">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        @if($avgRating)
                            @php $stars = round($avgRating); @endphp
                            <div class="flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $stars ? 'text-yellow-400' : 'text-gray-300' }}"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-sm font-semibold text-gray-700">{{ number_format($avgRating, 1) }}</span>
                            <span class="text-sm text-gray-500">({{ $reviews->total() }} {{ Str::plural('review', $reviews->total()) }})</span>
                        @else
                            <span class="text-sm text-gray-400">No reviews yet</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($reviews as $review)
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center font-semibold text-gray-600 text-sm shrink-0">
                                {{ strtoupper(substr($review->reviewer->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ $review->reviewer->name }}</p>
                                <p class="text-xs text-gray-400">{{ $review->created_at->diffForHumans() }} · {{ ucfirst($review->transaction_type) }}</p>
                            </div>
                        </div>
                        <div class="flex shrink-0">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-200' }}"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                    </div>
                    @if($review->body)
                        <p class="mt-3 text-gray-700 text-sm leading-relaxed">{{ $review->body }}</p>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
                    No reviews yet for this user.
                </div>
            @endforelse
        </div>

        <div class="mt-6">{{ $reviews->links() }}</div>
    </div>
</div>
@endsection
