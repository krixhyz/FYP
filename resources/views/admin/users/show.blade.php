@extends('layouts.admin')

@section('title', 'User Detail')

@section('content')
<div class="space-y-6">
    <div class="surface-card-strong p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="section-kicker">User Profile</p>
                <h2 class="text-2xl font-extrabold mt-1">{{ $user->name }}</h2>
                <p class="text-sm text-neutral-600">{{ $user->email }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="status-chip {{ $user->role === 'super_admin' ? 'status-info' : ($user->role === 'admin' ? 'status-success' : 'status-neutral') }}">{{ $user->role }}</span>
                <span class="status-chip {{ ($user->account_status ?? 'active') === 'active' ? 'status-success' : 'status-error' }}">{{ $user->account_status ?? 'active' }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="surface-card lg:col-span-2">
            <div class="border-b border-neutral-200 p-5"><h3 class="text-lg font-extrabold">Listings</h3></div>
            <ul class="divide-y divide-neutral-200">
                @forelse($products as $product)
                    <li class="p-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium">{{ $product->title }}</p>
                            <p class="text-xs text-neutral-500">{{ $product->category }} | {{ $product->status }}</p>
                        </div>
                        <span class="text-sm text-neutral-700">Rs. {{ number_format((float)$product->price, 2) }}</span>
                    </li>
                @empty
                    <li class="p-4 text-sm text-neutral-600">No listings.</li>
                @endforelse
            </ul>
        </div>

        <div class="surface-card">
            <div class="border-b border-neutral-200 p-5"><h3 class="text-lg font-extrabold">Recent Reviews</h3></div>
            <ul class="divide-y divide-neutral-200">
                @forelse($reviews as $review)
                    <li class="p-4">
                        <p class="text-sm font-medium">Rating: {{ $review->rating }}/5</p>
                        <p class="mt-1 text-xs text-neutral-500">{{ $review->body ?: 'No comment' }}</p>
                    </li>
                @empty
                    <li class="p-4 text-sm text-neutral-600">No reviews.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="surface-card">
        <div class="border-b border-neutral-200 p-5"><h3 class="text-lg font-extrabold">Transaction History</h3></div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-neutral-100 text-neutral-700 uppercase text-xs tracking-[0.12em]">
                    <tr>
                        <th class="p-3 text-left">Order</th>
                        <th class="p-3 text-left">Item</th>
                        <th class="p-3 text-left">Qty</th>
                        <th class="p-3 text-left">Total</th>
                        <th class="p-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    @forelse($orders as $order)
                        <tr>
                            <td class="p-3">#{{ $order->id }}</td>
                            <td class="p-3">{{ $order->product?->title ?? 'N/A' }}</td>
                            <td class="p-3">{{ $order->quantity }}</td>
                            <td class="p-3">Rs. {{ number_format((float)($order->total_price ?? 0), 2) }}</td>
                            <td class="p-3">{{ $order->status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-4 text-neutral-600">No orders.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($admin->isSuperAdmin())
        <div class="surface-card">
            <div class="border-b border-neutral-200 p-5"><h3 class="text-lg font-extrabold">Sensitive Payment Data (Super Admin)</h3></div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-neutral-100 text-neutral-700 uppercase text-xs tracking-[0.12em]">
                        <tr>
                            <th class="p-3 text-left">Payment ID</th>
                            <th class="p-3 text-left">Provider</th>
                            <th class="p-3 text-left">Amount</th>
                            <th class="p-3 text-left">Status</th>
                            <th class="p-3 text-left">Reference</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200">
                        @forelse($payments as $payment)
                            <tr>
                                <td class="p-3">#{{ $payment->id }}</td>
                                <td class="p-3">{{ $payment->provider }}</td>
                                <td class="p-3">Rs. {{ number_format((float) $payment->total_amount, 2) }}</td>
                                <td class="p-3">{{ $payment->status }}</td>
                                <td class="p-3">{{ $payment->transaction_uuid }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4 text-neutral-600">No payments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
