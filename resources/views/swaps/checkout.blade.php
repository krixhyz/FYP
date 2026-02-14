@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-10 text-gray-200">
        <h2 class="text-xl font-semibold mb-6">Swap Checkout</h2>

        <div class="bg-gray-800 p-6 rounded-xl shadow space-y-4">
            <div>
                <p class="text-sm text-gray-400">Requested Product</p>
                <p class="text-lg font-semibold">{{ $swapRequest->product->title }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Offered Product</p>
                <p class="text-lg font-semibold">{{ $swapRequest->offeredProduct?->title ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Cash Top-up</p>
                <p class="text-lg font-semibold">Rs. {{ number_format($swapRequest->offered_amount ?? 0, 2) }}</p>
            </div>

            @if($swapRequest->message)
                <div>
                    <p class="text-sm text-gray-400">Notes</p>
                    <p class="text-sm">{{ $swapRequest->message }}</p>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('swap.pay', $swapRequest) }}" class="mt-6">
            @csrf
            <button type="submit" class="bg-blue-500 hover:bg-blue-400 text-gray-900 px-4 py-2 rounded">
                Pay with eSewa
            </button>
        </form>
    </div>
@endsection
