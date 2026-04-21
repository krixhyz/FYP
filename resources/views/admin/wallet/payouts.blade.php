@extends('layouts.admin')

@section('title', 'Wallet Payouts')

@section('content')
<div class="surface-card-strong p-6 md:p-8 space-y-5">
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <div>
            <p class="section-kicker">Admin Finance</p>
            <h2 class="section-title mt-1">Wallet Payout Requests</h2>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <select name="status" class="input-field !py-2 text-sm">
                <option value="">All Statuses</option>
                <option value="pending" @selected(request('status') === 'pending')>pending</option>
                <option value="approved" @selected(request('status') === 'approved')>approved</option>
                <option value="rejected" @selected(request('status') === 'rejected')>rejected</option>
                <option value="paid" @selected(request('status') === 'paid')>paid</option>
            </select>
            <button class="btn-pill btn-pill-soft !px-4 !py-2 text-sm">Filter</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="editorial-table">
            <thead>
                <tr>
                    <th class="p-3 text-left">ID</th>
                    <th class="p-3 text-left">User</th>
                    <th class="p-3 text-left">Amount</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Requested</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payoutRequests as $payout)
                    <tr>
                        <td class="p-3">#{{ $payout->id }}</td>
                        <td class="p-3">{{ $payout->user?->name }}</td>
                        <td class="p-3">Rs. {{ number_format((float) $payout->amount, 2) }}</td>
                        <td class="p-3 uppercase">{{ $payout->status }}</td>
                        <td class="p-3">{{ optional($payout->requested_at)->diffForHumans() ?? '-' }}</td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-2">
                                @if($payout->status === 'pending')
                                    <form method="POST" action="{{ route('admin.wallet.payouts.approve', $payout) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn-pill btn-pill-soft !px-3 !py-1 text-xs">Approve</button>
                                    </form>
                                @endif

                                @if(in_array($payout->status, ['pending', 'approved'], true))
                                    <form method="POST" action="{{ route('admin.wallet.payouts.paid', $payout) }}" class="flex gap-2 items-center">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="payout_reference" placeholder="Ref" class="input-field !py-1 text-xs w-24" required>
                                        <button class="btn-pill btn-pill-dark !px-3 !py-1 text-xs">Mark Paid</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.wallet.payouts.reject', $payout) }}" class="flex gap-2 items-center">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="rejection_reason" placeholder="Reason" class="input-field !py-1 text-xs w-28" required>
                                        <button class="btn-pill !px-3 !py-1 text-xs" style="background:#ba1a1a;color:#fff;">Reject</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-4 text-[#888]">No payout requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $payoutRequests->links() }}</div>
</div>
@endsection
