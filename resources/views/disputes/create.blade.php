@extends('layouts.app')
@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-2xl mx-auto px-4">
        <a href="{{ route('products.myPurchases') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6 text-sm">
            ← Back to My Purchases
        </a>

        <div class="bg-white rounded-2xl shadow-md p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-1">Report a Dispute</h2>
            <p class="text-gray-500 text-sm mb-6">
                Transaction type: <span class="font-semibold text-gray-800">{{ ucfirst($type) }}</span>
                (ref #{{ $id }})
            </p>

            @if($existing)
                <div class="mb-4 rounded-lg border px-4 py-3 text-sm
                    {{ $existing->status === 'open' ? 'bg-yellow-50 border-yellow-200 text-yellow-800' : '' }}
                    {{ $existing->status === 'in_review' ? 'bg-blue-50 border-blue-200 text-blue-800' : '' }}
                    {{ in_array($existing->status, ['resolved','dismissed']) ? 'bg-green-50 border-green-200 text-green-800' : '' }}">
                    <p class="font-semibold">You already filed a dispute for this transaction.</p>
                    <p class="mt-0.5">Status: <strong>{{ ucfirst(str_replace('_',' ', $existing->status)) }}</strong></p>
                    @if($existing->admin_notes)
                        <p class="mt-1 text-xs">Admin note: {{ $existing->admin_notes }}</p>
                    @endif
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
                    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('dispute.store') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="type"   value="{{ $type }}">
                <input type="hidden" name="ref_id" value="{{ $id }}">

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject <span class="text-red-500">*</span></label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject', $existing?->subject) }}"
                           placeholder="Brief description of the issue"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('subject')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                    <textarea id="description" name="description" rows="5"
                              placeholder="Provide as much detail as possible — what happened, when, and what resolution you expect."
                              class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none">{{ old('description', $existing?->description) }}</textarea>
                    @error('description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-xl transition">
                        {{ $existing ? 'Update Dispute' : 'Submit Dispute' }}
                    </button>
                    <a href="{{ route('products.myPurchases') }}"
                       class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-xl transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- My Disputes link --}}
        <div class="mt-4 text-center">
            <a href="{{ route('dispute.my') }}" class="text-sm text-blue-600 hover:underline">View all my disputes →</a>
        </div>
    </div>
</div>
@endsection
