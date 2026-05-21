@extends('layouts.admin')

@section('title', 'Transactions')

@section('content')
<div class="surface-card-strong p-6 md:p-8">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="section-kicker">Admin Finance</p>
            <h2 class="section-title mt-1">Monitor Transactions</h2>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <select name="type" class="input-field !py-2 text-sm">
                <option value="">Type</option>
                <option value="buy" @selected(request('type') === 'buy')>Buy</option>
                <option value="rent" @selected(request('type') === 'rent')>Rent</option>
                <option value="swap" @selected(request('type') === 'swap')>Swap</option>
            </select>
            <select name="status" class="input-field !py-2 text-sm">
                <option value="">Status</option>
                <option value="pending" @selected(request('status') === 'pending')>pending</option>
                <option value="active" @selected(request('status') === 'active')>active</option>
                <option value="completed" @selected(request('status') === 'completed')>completed</option>
                <option value="failed" @selected(request('status') === 'failed')>failed</option>
            </select>
            <button class="btn-pill btn-pill-soft !px-4 !py-2 text-sm">Filter</button>
        </form>
    </div>

    <!-- @if(!auth()->user()->isSuperAdmin())
        <div class="mb-5 border-2 border-blue-300 bg-blue-50 px-5 py-4 font-manrope text-sm text-blue-700">
            <p class="font-space font-bold">Transaction History</p>
            <p class="font-semibold">Limited Access</p>
            <p class="text-sm mt-1">You can monitor transactions and resolve disputes. Cannot configure payment gateways or access raw financial data.</p>
        </div>
    @endif -->

    @if($financialSummary)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div class="surface-card p-4">
                <p class="meta-text">Payments Total</p>
                <p class="text-3xl font-extrabold mt-2">Rs. {{ number_format($financialSummary['payments_total'], 2) }}</p>
            </div>
            <div class="surface-card p-4">
                <p class="meta-text">Successful Revenue</p>
                <p class="text-3xl font-extrabold mt-2">Rs. {{ number_format($financialSummary['payments_successful'], 2) }}</p>
            </div>
            <div class="surface-card p-4">
                <p class="meta-text">Completed Buy Orders</p>
                <p class="text-3xl font-extrabold mt-2">{{ $financialSummary['orders_completed'] }}</p>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="editorial-table">
            <thead>
                <tr>
                    <th class="p-3 text-left">ID</th>
                    <th class="p-3 text-left">Buyer</th>
                    <th class="p-3 text-left">Seller</th>
                    <th class="p-3 text-left">Item</th>
                    <th class="p-3 text-left">Type</th>
                    <th class="p-3 text-left">Amount</th>
                    <th class="p-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                    <tr>
                        <td class="p-3">{{ $txn['ref'] }}</td>
                        <td class="p-3">{{ $txn['buyer'] }}</td>
                        <td class="p-3">{{ $txn['seller'] }}</td>
                        <td class="p-3">{{ $txn['item'] }}</td>
                        <td class="p-3">
                            <span class="status-chip {{ $txn['type'] === 'buy' ? 'status-info' : ($txn['type'] === 'rent' ? 'status-neutral' : 'status-warning') }}">{{ $txn['type'] }}</span>
                        </td>
                        <td class="p-3">Rs. {{ number_format((float)$txn['amount'], 2) }}</td>
                        <td class="p-3">
                            <span class="status-chip {{ in_array($txn['status'], ['completed','resolved']) ? 'status-success' : (in_array($txn['status'], ['failed','cancelled']) ? 'status-error' : 'status-neutral') }}">{{ $txn['status'] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-5 text-center font-manrope text-[#444746]">No transactions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</div>
@endsection
