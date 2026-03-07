@extends('layouts.app')
@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">My Disputes</h1>
            <a href="{{ route('products.myPurchases') }}" class="text-sm text-blue-600 hover:underline">← My Purchases</a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            @forelse ($disputes as $dispute)
                <div class="p-6 border-b border-gray-100 last:border-b-0">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $dispute->subject }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ ucfirst($dispute->transaction_type) }} #{{ $dispute->{$dispute->transaction_type === 'order' ? 'order_id' : ($dispute->transaction_type === 'rental' ? 'rental_request_id' : 'swap_id')} }}
                                · Filed {{ $dispute->created_at->diffForHumans() }}
                            </p>
                            <p class="mt-2 text-sm text-gray-600 line-clamp-2">{{ $dispute->description }}</p>
                            @if($dispute->admin_notes)
                                <div class="mt-2 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2 text-sm text-blue-700">
                                    <span class="font-medium">Admin note:</span> {{ $dispute->admin_notes }}
                                </div>
                            @endif
                        </div>
                        <span class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold {{ $dispute->statusBadge() }}">
                            {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-gray-400">You have not filed any disputes.</div>
            @endforelse
        </div>

        <div class="mt-4">{{ $disputes->links() }}</div>
    </div>
</div>
@endsection
