@extends('layouts.app')
@section('content')
<div class="max-w-2xl mx-auto px-8 md:px-16 py-12 space-y-8">
    <a href="{{ route('products.myPurchases') }}" class="inline-block border-2 border-[#006a38] text-[#006a38] px-4 py-2 font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)]">Back to My Purchases</a>

    <section class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-6 md:p-8">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Feedback</p>
        <h1 class="font-space font-bold text-3xl text-[#1a1c1c] mt-1 mb-3">Leave a Review</h1>
        <p class="font-manrope text-sm text-[#444746]">Reviewing <span class="font-medium">{{ $reviewee?->name ?? 'Unknown' }}</span> for {{ ucfirst($type) }} transaction.</p>

        @if($existingReview)
            <div class="mt-5 inline-flex bg-[#e0e7ff] border-2 border-[#4f46e5] text-[#3730a3] px-4 py-2 font-manrope text-sm">You already submitted a review for this transaction. Submitting again will update it.</div>
        @endif

        @if($errors->any())
            <div class="mt-5 border-2 border-[#ba1a1a] bg-[#fee2e2] px-4 py-3 text-sm text-[#7f1d1d] font-manrope">
                <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('review.store') }}" method="POST" class="mt-6 space-y-6">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="ref_id" value="{{ $id }}">

            <div>
                <label class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-3">Rating <span class="text-[#ba1a1a]">*</span></label>
                <div class="flex gap-3" x-data="{ rating: {{ old('rating', $existingReview?->rating ?? 0) }} }">
                    @for($i = 1; $i <= 5; $i++)
                        <label class="cursor-pointer">
                            <input type="radio" name="rating" value="{{ $i }}" class="sr-only" x-on:change="rating = {{ $i }}" {{ old('rating', $existingReview?->rating) == $i ? 'checked' : '' }}>
                            <svg x-bind:class="rating >= {{ $i }} ? 'text-amber-400' : 'text-slate-300'" class="h-9 w-9 transition-colors" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </label>
                    @endfor
                </div>
                @error('rating')<p class="mt-1 font-manrope text-xs text-[#ba1a1a]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="body" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-2">Review (optional)</label>
                <textarea id="body" name="body" rows="4" placeholder="Share your experience..." 
                    class="w-full bg-white px-4 py-3 font-manrope border-b-2 border-gray-400 focus:border-[#006a38] focus:outline-none">{{ old('body', $existingReview?->body) }}</textarea>
                @error('body')<p class="mt-1 font-manrope text-xs text-[#ba1a1a]">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="w-full bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-6 py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110">
                {{ $existingReview ? 'Update Review' : 'Submit Review' }}
            </button>
        </form>
    </section>
</div>
@endsection
