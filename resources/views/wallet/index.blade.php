@extends('layouts.dashboard')

@section('content')
@php
    $payouts = $wallet->payoutRequests;
    $ledgerEntries = $wallet->ledgerEntries;
    $pendingPayouts = $payouts->whereIn('status', ['pending', 'approved']);
    $completedPayouts = $payouts->whereIn('status', ['paid', 'rejected', 'cancelled']);
@endphp

<div class="px-0 md:px-8 pb-8">
    <section class="py-8">
        <p class="font-space text-[12px] font-bold uppercase tracking-widest text-[#888] mb-2">Wallet Workspace</p>
        <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-2">My Wallet</h1>
        <p class="font-manrope text-base text-[#444746]">Track earnings, payout requests, and ledger movement.</p>
    </section>

    <section class="py-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Available</p>
            <p class="font-space font-bold text-2xl text-[#006a38]">Rs. {{ number_format((float) $wallet->available_balance, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Pending Hold</p>
            <p class="font-space font-bold text-2xl text-[#006a38]">Rs. {{ number_format((float) $wallet->pending_payout_balance, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Active Payouts</p>
            <p class="font-space font-bold text-3xl text-[#006a38]">{{ $pendingPayouts->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-1">Ledger Entries</p>
            <p class="font-space font-bold text-3xl text-[#006a38]">{{ $ledgerEntries->count() }}</p>
        </div>
    </section>

    <section class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 mb-6">
        <h2 class="font-space font-bold text-sm uppercase tracking-widest text-[#444746] mb-4">Request Payout</h2>
        <form method="POST" action="{{ route('wallet.payout.request') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @csrf
            <input id="payout-amount" type="text" inputmode="decimal" name="amount" placeholder="Amount" value="{{ old('amount') }}" data-max="{{ number_format((float) $wallet->available_balance, 2, '.', '') }}" class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm" required>
            <input type="text" name="note" placeholder="Note (optional)" class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm">
            <button type="submit" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white py-2.5 font-space font-bold text-xs uppercase tracking-wider" {{ (float) $wallet->available_balance <= 0 ? 'disabled' : '' }}>Submit Request</button>
        </form>
        <p class="font-manrope text-xs text-[#666]">Max payable amount: Rs. {{ number_format((float) $wallet->available_balance, 2) }}</p>
        @error('amount')
            <p class="font-manrope text-xs text-[#ba1a1a]">{{ $message }}</p>
        @enderror
    </section>

    <div id="wallet-tabs" class="py-6 border-b border-[rgba(189,202,189,0.1)] flex gap-8">
        <button class="wallet-tab-button active font-space font-bold text-sm uppercase tracking-widest text-[#1a1c1c] pb-3 border-b-2 border-[#006a38] cursor-pointer" data-tab="payouts-active">
            Active Payouts ({{ $pendingPayouts->count() }})
        </button>
        <button class="wallet-tab-button font-space font-bold text-sm uppercase tracking-widest text-[#888] pb-3 border-b-2 border-transparent hover:text-[#1a1c1c] cursor-pointer transition-colors" data-tab="ledger-entries">
            Ledger Entries ({{ $ledgerEntries->count() }})
        </button>
        <button class="wallet-tab-button font-space font-bold text-sm uppercase tracking-widest text-[#888] pb-3 border-b-2 border-transparent hover:text-[#1a1c1c] cursor-pointer transition-colors" data-tab="payouts-completed">
            Payout History ({{ $completedPayouts->count() }})
        </button>
    </div>

    <section id="payouts-active" class="wallet-tab-content py-6">
        @if($pendingPayouts->count() > 0)
            <div class="space-y-4">
                @foreach($pendingPayouts as $payout)
                    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-center">
                            <div>
                                <p class="text-xs text-[#888] mb-1">Amount</p>
                                <p class="font-space font-bold text-lg text-[#006a38]">Rs. {{ number_format((float) $payout->amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-[#888] mb-1">Status</p>
                                <span class="text-[11px] font-space font-bold px-3 py-1 rounded {{ $payout->status === 'approved' ? 'bg-[#d4edda] text-[#155724]' : 'bg-[#ffd580] text-[#664d03]' }} inline-block uppercase">{{ $payout->status }}</span>
                            </div>
                            <div>
                                <p class="text-xs text-[#888] mb-1">Requested</p>
                                <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ optional($payout->requested_at)->format('M d, Y') ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-[#888] mb-1">Reference</p>
                                <p class="font-manrope text-sm text-[#1a1c1c] break-all">{{ $payout->payout_reference ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-12 text-center">
                <p class="font-manrope text-base text-[#888]">No active payout requests.</p>
            </div>
        @endif
    </section>

    <section id="ledger-entries" class="wallet-tab-content hidden py-6">
        @if($ledgerEntries->count() > 0)
            <div class="space-y-4">
                @foreach($ledgerEntries as $entry)
                    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 items-center">
                            <div>
                                <p class="text-xs text-[#888] mb-1">Type</p>
                                <p class="font-space font-bold text-sm text-[#1a1c1c] uppercase">{{ str_replace('_', ' ', $entry->entry_type) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-[#888] mb-1">Direction</p>
                                <span class="text-[11px] font-space font-bold px-3 py-1 rounded {{ $entry->direction === 'credit' ? 'bg-[#d4edda] text-[#155724]' : 'bg-[#f8d7da] text-[#721c24]' }} inline-block uppercase">{{ $entry->direction }}</span>
                            </div>
                            <div>
                                <p class="text-xs text-[#888] mb-1">Amount</p>
                                <p class="font-space font-bold text-sm text-[#1a1c1c]">Rs. {{ number_format((float) $entry->amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-[#888] mb-1">Balance After</p>
                                <p class="font-space font-bold text-sm text-[#006a38]">Rs. {{ number_format((float) $entry->balance_after, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-[#888] mb-1">Time</p>
                                <p class="font-manrope text-sm text-[#1a1c1c]">{{ $entry->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-12 text-center">
                <p class="font-manrope text-base text-[#888]">No ledger entries yet.</p>
            </div>
        @endif
    </section>

    <section id="payouts-completed" class="wallet-tab-content hidden py-6">
        @if($completedPayouts->count() > 0)
            <div class="space-y-4">
                @foreach($completedPayouts as $payout)
                    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-center">
                            <div>
                                <p class="text-xs text-[#888] mb-1">Amount</p>
                                <p class="font-space font-bold text-lg text-[#006a38]">Rs. {{ number_format((float) $payout->amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-[#888] mb-1">Status</p>
                                <span class="text-[11px] font-space font-bold px-3 py-1 rounded {{ $payout->status === 'paid' ? 'bg-[#d4edda] text-[#155724]' : 'bg-[#f8d7da] text-[#721c24]' }} inline-block uppercase">{{ $payout->status }}</span>
                            </div>
                            <div>
                                <p class="text-xs text-[#888] mb-1">Requested</p>
                                <p class="font-space font-bold text-sm text-[#1a1c1c]">{{ optional($payout->requested_at)->format('M d, Y') ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-[#888] mb-1">Reference</p>
                                <p class="font-manrope text-sm text-[#1a1c1c] break-all">{{ $payout->payout_reference ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-12 text-center">
                <p class="font-manrope text-base text-[#888]">No completed payout history yet.</p>
            </div>
        @endif
    </section>

    <div class="h-8"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.wallet-tab-button');
    const tabContents = document.querySelectorAll('.wallet-tab-content');
    const payoutAmountInput = document.getElementById('payout-amount');

    if (payoutAmountInput) {
        const maxPayable = parseFloat(payoutAmountInput.dataset.max || '0');

        const sanitizeAmount = () => {
            let value = String(payoutAmountInput.value || '');

            // Allow only digits and one decimal separator.
            value = value.replace(/[^\d.]/g, '');
            const firstDotIndex = value.indexOf('.');
            if (firstDotIndex !== -1) {
                value = value.slice(0, firstDotIndex + 1) + value.slice(firstDotIndex + 1).replace(/\./g, '');
            }

            const parts = value.split('.');
            if (parts.length > 1) {
                parts[1] = parts[1].slice(0, 2);
                value = parts[0] + '.' + parts[1];
            }

            if (value !== '' && !Number.isNaN(maxPayable)) {
                const asNumber = parseFloat(value);
                if (!Number.isNaN(asNumber) && asNumber > maxPayable) {
                    value = maxPayable.toFixed(2);
                }
            }

            payoutAmountInput.value = value;
        };

        payoutAmountInput.addEventListener('keydown', (event) => {
            if (['-', '+', 'e', 'E'].includes(event.key)) {
                event.preventDefault();
            }
        });

        payoutAmountInput.addEventListener('input', sanitizeAmount);
        payoutAmountInput.addEventListener('blur', sanitizeAmount);
        sanitizeAmount();
    }

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            tabContents.forEach(content => content.classList.add('hidden'));
            tabButtons.forEach(btn => {
                btn.classList.remove('text-[#1a1c1c]', 'border-[#006a38]');
                btn.classList.add('text-[#888]', 'border-transparent');
            });

            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.remove('hidden');

            this.classList.remove('text-[#888]', 'border-transparent');
            this.classList.add('text-[#1a1c1c]', 'border-[#006a38]');
        });
    });
});
</script>
@endsection
