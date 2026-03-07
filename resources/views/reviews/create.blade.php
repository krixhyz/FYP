@extends('layouts.app')
@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-2xl mx-auto px-4">
        <a href="{{ route('products.myPurchases') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6 text-sm">
            ← Back to My Purchases
        </a>

        <div class="bg-white rounded-2xl shadow-md p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-1">Leave a Review</h2>
            <p class="text-gray-500 text-sm mb-6">
                Reviewing <span class="font-semibold text-gray-800">{{ $reviewee?->name ?? 'Unknown' }}</span>
                for {{ ucfirst($type) }} transaction
            </p>

            @if($existingReview)
                <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-blue-800 text-sm">
                    You already submitted a review for this transaction. Submitting again will update it.
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
                    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('review.store') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="type"   value="{{ $type }}">
                <input type="hidden" name="ref_id" value="{{ $id }}">

                {{-- Star Rating --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating <span class="text-red-500">*</span></label>
                    <div class="flex gap-2" x-data="{ rating: {{ old('rating', $existingReview?->rating ?? 0) }} }">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="{{ $i }}" class="sr-only"
                                       x-on:change="rating = {{ $i }}"
                                       {{ old('rating', $existingReview?->rating) == $i ? 'checked' : '' }}>
                                <svg x-bind:class="rating >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300'"
                                     class="w-9 h-9 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </label>
                        @endfor
                    </div>
                    @error('rating')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Review Body --}}
                <div>
                    <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Review (optional)</label>
                    <textarea id="body" name="body" rows="4"
                              placeholder="Share your experience..."
                              class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none">{{ old('body', $existingReview?->body) }}</textarea>
                    @error('body')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition">
                    {{ $existingReview ? 'Update Review' : 'Submit Review' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
