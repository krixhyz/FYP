@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto px-8 md:px-16 py-12 space-y-8">
    <section class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-6 md:p-8">
        <div class="flex items-center gap-6">
            <div class="flex h-16 w-16 items-center justify-center bg-[#e8f5e9] font-space font-bold text-2xl text-[#1b5e20]">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div>
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-1">User Reputation</p>
                <h1 class="font-space font-bold text-2xl text-[#1a1c1c]">{{ $user->name }}</h1>
                <div class="mt-2 flex items-center gap-3">
                    @if($avgRating)
                        @php $stars = round($avgRating); @endphp
                        <div class="flex gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="h-5 w-5 {{ $i <= $stars ? 'text-amber-400' : 'text-slate-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <span class="font-space font-bold text-[#1a1c1c]">{{ number_format($avgRating, 1) }}</span>
                        <span class="font-manrope text-sm text-[#888888]">({{ $reviews->total() }} {{ Str::plural('review', $reviews->total()) }})</span>
                    @else
                        <span class="font-manrope text-sm text-[#888888]">No reviews yet</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="space-y-4">
        @forelse ($reviews as $review)
            <article class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center bg-[#e8f5e9] font-space font-bold text-sm text-[#1b5e20]">{{ strtoupper(substr($review->reviewer->name, 0, 1)) }}</div>
                        <div>
                            <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ $review->reviewer->name }}</p>
                            <p class="font-manrope text-xs text-[#888888]">{{ $review->created_at->diffForHumans() }} · {{ ucfirst($review->transaction_type) }}</p>
                        </div>
                    </div>
                    <div class="flex shrink-0 gap-1">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                </div>
                @if($review->body)
                    <p class="mt-3 font-manrope text-sm leading-relaxed text-[#444746]">{{ $review->body }}</p>
                @endif
            </article>
        @empty
            <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-10 text-center">
                <p class="font-manrope text-sm text-[#888888]">No reviews yet for this user.</p>
            </div>
        @endforelse
    </div>

    <div>{{ $reviews->links() }}</div>
</div>
@endsection
