@extends('layouts.app')
@section('content')
<div class="max-w-2xl mx-auto px-8 md:px-16 py-12 space-y-8">
    <a href="{{ route('products.myPurchases') }}" class="inline-block border-2 border-[#006a38] text-[#006a38] px-4 py-2 font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)]">Back to My Purchases</a>

    <section class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-6 md:p-8">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Support</p>
        <h1 class="font-space font-bold text-3xl text-[#1a1c1c] mt-1 mb-3">Report a Dispute</h1>
        <p class="font-manrope text-sm text-[#444746]">Transaction type: <span class="font-medium">{{ ucfirst($type) }}</span> (ref #{{ $id }})</p>

        @if($existing)
            <div class="mt-5 border-2 px-4 py-3 text-sm
                {{ $existing->status === 'open' ? 'border-[#f59e0b] bg-[#fffbeb] text-[#92400e]' : '' }}
                {{ $existing->status === 'in_review' ? 'border-[#3b82f6] bg-[#eff6ff] text-[#1e3a8a]' : '' }}
                {{ in_array($existing->status, ['resolved','dismissed']) ? 'border-[#10b981] bg-[#d1fae5] text-[#065f46]' : '' }}
                font-space">
                <p class="font-bold uppercase">You already filed a dispute for this transaction.</p>
                <p class="mt-1 font-manrope">Status: <strong>{{ ucfirst(str_replace('_',' ', $existing->status)) }}</strong></p>
                @if($existing->admin_notes)
                    <p class="mt-1 font-manrope text-xs">Admin note: {{ $existing->admin_notes }}</p>
                @endif
            </div>
        @endif

        @if($errors->any())
            <div class="mt-5 border-2 border-[#ba1a1a] bg-[#fee2e2] px-4 py-3 text-sm text-[#7f1d1d] font-manrope">
                <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('dispute.store') }}" method="POST" class="mt-6 space-y-6">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="ref_id" value="{{ $id }}">

            <div>
                <label for="subject" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-2">Subject <span class="text-[#ba1a1a]">*</span></label>
                <input type="text" id="subject" name="subject" value="{{ old('subject', $existing?->subject) }}" placeholder="Brief description of the issue" 
                    class="w-full bg-white px-4 py-3 font-manrope border-b-2 border-gray-400 focus:border-[#006a38] focus:outline-none">
                @error('subject')<p class="mt-1 font-manrope text-xs text-[#ba1a1a]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-2">Description <span class="text-[#ba1a1a]">*</span></label>
                <textarea id="description" name="description" rows="5" placeholder="Provide details of what happened and the resolution you expect." 
                    class="w-full bg-white px-4 py-3 font-manrope border-b-2 border-gray-400 focus:border-[#006a38] focus:outline-none">{{ old('description', $existing?->description) }}</textarea>
                @error('description')<p class="mt-1 font-manrope text-xs text-[#ba1a1a]">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-wrap gap-3 pt-3">
                <button type="submit" class="flex-1 bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-6 py-3 text-center font-space font-bold text-sm uppercase tracking-wider hover:brightness-110">
                    {{ $existing ? 'Update Dispute' : 'Submit Dispute' }}
                </button>
                <a href="{{ route('products.myPurchases') }}" class="flex-1 bg-white border-2 border-[#006a38] text-[#006a38] px-6 py-3 text-center font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)]">
                    Cancel
                </a>
            </div>
        </form>
    </section>

    <div class="text-center">
        <a href="{{ route('dispute.my') }}" class="font-manrope text-sm font-bold text-[#006a38] hover:underline">View all my disputes</a>
    </div>
</div>
@endsection
