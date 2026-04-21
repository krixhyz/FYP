@extends('layouts.admin')

@section('title', 'User Detail')

@section('content')
<div class="space-y-6">
    <div class="surface-card-strong p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="section-kicker">User Profile</p>
                <h2 class="text-2xl font-extrabold mt-1">{{ $user->name }}</h2>
                <p class="text-sm text-[#444746]">{{ $user->email }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="status-chip {{ $user->role === 'super_admin' ? 'status-info' : ($user->role === 'admin' ? 'status-success' : 'status-neutral') }}">{{ $user->role }}</span>
                <span class="status-chip {{ ($user->account_status ?? 'active') === 'active' ? 'status-success' : 'status-error' }}">{{ $user->account_status ?? 'active' }}</span>
                <span class="status-chip {{ $profileStatus === 'VERIFIED' ? 'status-success' : 'status-neutral' }}">{{ $profileStatus === 'VERIFIED' ? 'Verified Seller' : 'Unverified' }}</span>
            </div>
        </div>
    </div>

    <!-- Profile Verification Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="surface-card p-6">
            <p class="text-sm font-space font-bold uppercase tracking-widest text-[#888888]">Average Rating</p>
            <p class="text-3xl font-bold text-[#006a38] mt-2">{{ $averageRating > 0 ? number_format($averageRating, 2) : '-' }}/5</p>
            <p class="text-xs text-[#888888] mt-1">Based on product reviews</p>
        </div>
        <div class="surface-card p-6">
            <p class="text-sm font-space font-bold uppercase tracking-widest text-[#888888]">Total Disputes</p>
            <p class="text-3xl font-bold text-[#006a38] mt-2">{{ $totalDisputes }}</p>
            <p class="text-xs text-[#888888] mt-1">Open, in_review, resolved</p>
        </div>
        <div class="surface-card p-6">
            <p class="text-sm font-space font-bold uppercase tracking-widest text-[#888888]">Total Products</p>
            <p class="text-3xl font-bold text-[#006a38] mt-2">{{ $totalProducts }}</p>
            <p class="text-xs text-[#888888] mt-1">All listings</p>
        </div>
    </div>

    <!-- Profile Verification Actions -->
    @if($profileStatus === 'VERIFIED')
        <div class="surface-card p-6 border-l-4 border-[#10b981]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-extrabold text-[#006a38]">Profile Verified</p>
                    <p class="text-sm text-[#888888] mt-1">This user's profile has been verified and their new products are approved instantly.</p>
                </div>
                <form action="{{ route('admin.users.revokeVerification', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to revoke verification?')">
                    @csrf
                    <button type="submit" class="btn-pill !px-6 !border-[#ba1a1a] !text-[#ba1a1a] hover:!bg-[#ba1a1a] hover:!text-white whitespace-nowrap">Revoke</button>
                </form>
            </div>
        </div>
    @else
        <div class="surface-card p-6 border-l-4 border-[#d97706]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-extrabold text-[#ca8a04]">Profile Unverified</p>
                    <p class="text-sm text-[#888888] mt-1">This user needs verification. Their products require manual approval. Rating: {{ $averageRating > 0 ? number_format($averageRating, 2) : '—'  }}, Disputes: {{ $totalDisputes }}</p>
                </div>
                <form action="{{ route('admin.users.verify', $user->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-pill !px-6 !border-[#10b981] !text-[#10b981] hover:!bg-[#10b981] hover:!text-white whitespace-nowrap">Verify Now</button>
                </form>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="surface-card lg:col-span-2">
            <div class="bg-[#f3f3f3] p-5"><h3 class="text-lg font-extrabold">Listings</h3></div>
            <ul class="editorial-list p-4">
                @forelse($products as $product)
                    <li class="bg-white p-4 border-b border-[rgba(189,202,189,0.2)] flex items-center justify-between">
                        <div>
                            <p class="font-medium">{{ $product->title }}</p>
                            <p class="text-xs text-[#888888]">{{ $product->category?->name ?? 'General' }} | {{ $product->status }}</p>
                        </div>
                        <span class="text-sm text-[#1a1c1c] font-semibold">Rs. {{ number_format((float)$product->price, 2) }}</span>
                    </li>
                @empty
                    <li class="p-4 text-sm text-[#888888]">No listings.</li>
                @endforelse
            </ul>
        </div>

        <div class="surface-card">
            <div class="bg-[#f3f3f3] p-5"><h3 class="text-lg font-extrabold">Recent Reviews</h3></div>
            <ul class="editorial-list p-4">
                @forelse($reviews as $review)
                    <li class="bg-white p-4 border-b border-[rgba(189,202,189,0.2)]">
                        <p class="text-sm font-medium">Rating: {{ $review->rating }}/5</p>
                        <p class="mt-1 text-xs text-[#888888]">{{ $review->body ?: 'No comment' }}</p>
                    </li>
                @empty
                    <li class="p-4 text-sm text-[#888888]">No reviews.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="surface-card">
        <div class="bg-[#f3f3f3] p-5"><h3 class="text-lg font-extrabold">Transaction History</h3></div>
        <div class="overflow-x-auto">
            <table class="editorial-table">
                <thead>
                    <tr>
                        <th class="p-3 text-left">Order</th>
                        <th class="p-3 text-left">Item</th>
                        <th class="p-3 text-left">Qty</th>
                        <th class="p-3 text-left">Total</th>
                        <th class="p-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="p-3">#{{ $order->id }}</td>
                            <td class="p-3">{{ $order->product?->title ?? 'N/A' }}</td>
                            <td class="p-3">{{ $order->quantity }}</td>
                            <td class="p-3">Rs. {{ number_format((float)($order->total_price ?? 0), 2) }}</td>
                            <td class="p-3">{{ $order->status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-4 text-[#888888]">No orders.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($admin->isSuperAdmin())
        <div class="surface-card">
            <div class="bg-[#f3f3f3] p-5"><h3 class="text-lg font-extrabold">Sensitive Payment Data (Super Admin)</h3></div>
            <div class="overflow-x-auto">
                <table class="editorial-table">
                    <thead>
                        <tr>
                            <th class="p-3 text-left">Payment ID</th>
                            <th class="p-3 text-left">Provider</th>
                            <th class="p-3 text-left">Amount</th>
                            <th class="p-3 text-left">Status</th>
                            <th class="p-3 text-left">Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td class="p-3">#{{ $payment->id }}</td>
                                <td class="p-3">{{ $payment->provider }}</td>
                                <td class="p-3">Rs. {{ number_format((float) $payment->total_amount, 2) }}</td>
                                <td class="p-3">{{ $payment->status }}</td>
                                <td class="p-3">{{ $payment->transaction_uuid }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4 text-[#888888]">No payments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
