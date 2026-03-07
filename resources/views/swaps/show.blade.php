@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-10 text-gray-200">
        <h2 class="text-xl font-semibold mb-6">Swap Request Details</h2>

        <div class="bg-gray-800 p-6 rounded-xl shadow space-y-3">
            <p><strong>Requester:</strong> {{ $swapRequest->requester->name }}</p>
            <p><strong>Product:</strong> {{ $swapRequest->product->title }}</p>
            <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $swapRequest->status)) }}</p>
            @if($swapRequest->offeredProduct)
                <p><strong>Offered Product:</strong> {{ $swapRequest->offeredProduct->title }}</p>
            @endif
            @if($swapRequest->offered_amount)
                <p><strong>Offered Amount:</strong> ${{ $swapRequest->offered_amount }}</p>
            @endif
            @if($swapRequest->message)
                <p><strong>Message:</strong> "{{ $swapRequest->message }}"</p>
            @endif

            @if($swapRequest->status === 'countered')
                <div class="border-t border-gray-700 pt-3">
                    <p class="text-yellow-300 font-semibold">Counter Offer</p>
                    @if($swapRequest->counter_amount)
                        <p><strong>Counter Amount:</strong> ${{ $swapRequest->counter_amount }}</p>
                    @endif
                    @if($swapRequest->counter_message)
                        <p><strong>Counter Message:</strong> "{{ $swapRequest->counter_message }}"</p>
                    @endif
                </div>
            @endif
        </div>

        @if(auth()->id() === $swapRequest->owner_id && $swapRequest->status === 'requested')
            <div class="bg-gray-800 p-6 rounded-xl shadow mt-6">
                <h3 class="text-lg font-semibold mb-4">Respond to Request</h3>
                <div class="flex gap-3 mb-6">
                    <form method="POST" action="{{ route('swap.request.accept', $swapRequest) }}">
                        @csrf
                        <button type="submit" class="bg-green-500 hover:bg-green-400 text-gray-900 px-4 py-2 rounded">Accept Offer</button>
                    </form>
                    <form method="POST" action="{{ route('swap.request.reject', $swapRequest) }}">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-400 text-gray-900 px-4 py-2 rounded">Reject Offer</button>
                    </form>
                </div>

                <h3 class="text-lg font-semibold mb-4">Send Counter Offer</h3>
                <form method="POST" action="{{ route('swap.request.counter', $swapRequest) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Counter Amount (optional)</label>
                        <input type="number" step="0.01" name="counter_amount" class="w-full rounded bg-gray-900 border border-gray-700 text-gray-200 px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Message</label>
                        <textarea name="counter_message" rows="3" class="w-full rounded bg-gray-900 border border-gray-700 text-gray-200 px-3 py-2"></textarea>
                    </div>
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-400 text-gray-900 px-4 py-2 rounded">Send Counter</button>
                </form>
            </div>
        @endif

        @if(auth()->id() === $swapRequest->requester_id && $swapRequest->status === 'countered')
            <div class="flex gap-3 mt-6">
                <form method="POST" action="{{ route('swap.request.counter.accept', $swapRequest) }}">
                    @csrf
                    <button type="submit" class="bg-green-500 hover:bg-green-400 text-gray-900 px-4 py-2 rounded">Accept Counter</button>
                </form>
                <form method="POST" action="{{ route('swap.request.counter.reject', $swapRequest) }}">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-400 text-gray-900 px-4 py-2 rounded">Reject Counter</button>
                </form>
            </div>
        @endif

        @if(auth()->id() === $swapRequest->requester_id && $swapRequest->status === 'awaiting_payment')
            <div class="mt-6">
                <a href="{{ route('swap.checkout', $swapRequest) }}"
                   class="bg-blue-500 hover:bg-blue-400 text-gray-900 px-4 py-2 rounded">
                    Proceed to Checkout
                </a>
            </div>
        @endif
    </div>
@endsection
