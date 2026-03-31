@extends('layouts.admin')

@section('title', 'Overview')

@section('content')
@if($isSuperAdmin)
    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-5">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Total Users</p>
            <p class="mt-3 font-space font-bold text-4xl text-[#1a1c1c]">{{ number_format($totalUsers) }}</p>
            <p class="mt-1 font-manrope text-sm text-[#006a38]">{{ number_format($activeUsers) }} active</p>
        </article>
        <article class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-5">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Total Listings</p>
            <p class="mt-3 font-space font-bold text-4xl text-[#1a1c1c]">{{ number_format($totalProducts) }}</p>
            <p class="mt-1 font-manrope text-sm text-[#ba1a1a]">{{ number_format($flaggedProducts) }} flagged</p>
        </article>
        <article class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-5">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Monthly Revenue</p>
            <p class="mt-3 font-space font-bold text-4xl text-[#006a38]">Rs. {{ number_format($monthlyRevenue, 1) }}</p>
            <p class="mt-1 font-manrope text-sm text-[#444746]">{{ number_format($completedTransactions) }} completed</p>
        </article>
        <article class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-5">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Pending Disputes</p>
            <p class="mt-3 font-space font-bold text-4xl text-[#1a1c1c]">{{ number_format($openDisputes) }}</p>
            <p class="mt-1 font-manrope text-sm text-[#444746]">{{ number_format($reportedItems) }} reported</p>
        </article>
    </section>

    <section class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] mt-6 p-5">
        <h3 class="font-space font-bold text-2xl text-[#1a1c1c]">System Health</h3>
        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="bg-[#f3f3f3] p-4">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Server Uptime</p>
                <p class="mt-2 font-space font-bold text-3xl text-[#1a1c1c]">99.9%</p>
            </div>
            <div class="bg-[#f3f3f3] p-4">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">API Response</p>
                <p class="mt-2 font-space font-bold text-3xl text-[#1a1c1c]">120ms</p>
            </div>
            <div class="bg-[#f3f3f3] p-4">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Database Load</p>
                <p class="mt-2 font-space font-bold text-3xl text-[#1a1c1c]">45%</p>
            </div>
            <div class="bg-[#f3f3f3] p-4">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Storage Used</p>
                <p class="mt-2 font-space font-bold text-3xl text-[#1a1c1c]">67%</p>
            </div>
        </div>
    </section>

    <section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-5">
            <h3 class="font-space font-bold text-2xl text-[#1a1c1c]">Pending Moderation</h3>
            <div class="mt-4 space-y-3">
                @forelse($products->take(3) as $product)
                    <article class="{{ $product->flagged ? 'bg-[#ffe2e2]' : 'bg-[#f3f3f3]' }} p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-manrope font-bold text-lg text-[#1a1c1c]">{{ $product->title }}</p>
                                <p class="font-manrope text-sm text-[#888888]">by {{ $product->user?->name ?? 'N/A' }}</p>
                            </div>
                            <span class="font-space font-bold text-xs px-2 py-1 {{ $product->flagged ? 'bg-[#ba1a1a] text-white' : 'bg-[#f59e0b] text-white' }}">{{ $product->flagged ? 'Flagged' : 'Pending' }}</span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <form method="POST" action="{{ route('admin.content.decision', $product) }}" class="w-full">@csrf @method('PATCH')<input type="hidden" name="decision" value="approve"><button class="w-full bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-4 py-2 font-space font-bold text-xs uppercase tracking-wider hover:brightness-110">Approve</button></form>
                            <form method="POST" action="{{ route('admin.content.decision', $product) }}" class="w-full">@csrf @method('PATCH')<input type="hidden" name="decision" value="reject"><button class="w-full border-2 border-[#006a38] text-[#006a38] px-4 py-2 font-space font-bold text-xs uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)]">Reject</button></form>
                        </div>
                    </article>
                @empty
                    <p class="bg-[#f3f3f3] p-4 font-manrope text-sm text-[#888888]">No pending moderation items.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-5">
            <h3 class="font-space font-bold text-2xl text-[#1a1c1c]">Recent Disputes</h3>
            <div class="mt-4 space-y-3">
                @forelse($recentDisputes as $dispute)
                    <article class="bg-[#ffe2e2] p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-manrope font-bold text-lg text-[#1a1c1c]">{{ $dispute->subject }}</p>
                                <p class="font-manrope text-sm text-[#888888]">{{ $dispute->reporter?->name ?? 'Unknown user' }}</p>
                                <p class="mt-1 font-manrope text-sm text-[#1a1c1c]">Amount: {{ $dispute->order?->total_price ? 'Rs. ' . number_format((float)$dispute->order->total_price,2) : 'N/A' }}</p>
                            </div>
                            <span class="font-space font-bold text-xs px-2 py-1 bg-[#ba1a1a] text-white">Disputed</span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="block bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-4 py-2 text-center font-space font-bold text-xs uppercase tracking-wider hover:brightness-110">Review</a>
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="block border-2 border-[#006a38] text-[#006a38] px-4 py-2 text-center font-space font-bold text-xs uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)]">Details</a>
                        </div>
                    </article>
                @empty
                    <p class="bg-[#f3f3f3] p-4 font-manrope text-sm text-[#888888]">No disputes available.</p>
                @endforelse
            </div>
        </div>
    </section>
@else
    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-5">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Pending Users</p>
            <p class="mt-3 font-space font-bold text-4xl text-[#1a1c1c]">{{ $pendingUsers }}</p>
            <p class="mt-1 font-manrope text-sm text-[#444746]">verification queue</p>
        </article>
        <article class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-5">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Flagged Listings</p>
            <p class="mt-3 font-space font-bold text-4xl text-[#1a1c1c]">{{ $flaggedProducts }}</p>
            <p class="mt-1 font-manrope text-sm text-[#444746]">needs moderation</p>
        </article>
        <article class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-5">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Active Disputes</p>
            <p class="mt-3 font-space font-bold text-4xl text-[#1a1c1c]">{{ $openDisputes }}</p>
            <p class="mt-1 font-manrope text-sm text-[#444746]">in queue</p>
        </article>
        <article class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-5">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Reports to Review</p>
            <p class="mt-3 font-space font-bold text-4xl text-[#1a1c1c]">{{ $reportedItems }}</p>
            <p class="mt-1 font-manrope text-sm text-[#444746]">content/dispute reports</p>
        </article>
    </section>

    <section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-5">
            <h3 class="font-space font-bold text-2xl text-[#1a1c1c]">Pending Verifications</h3>
            <div class="mt-4 space-y-3">
                @forelse($pendingVerifications as $candidate)
                    <article class="bg-[#f3f3f3] p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-manrope font-bold text-lg text-[#1a1c1c]">{{ $candidate->name }}</p>
                                <p class="font-manrope text-sm text-[#888888]">{{ $candidate->email }}</p>
                                <p class="mt-1 font-manrope text-xs text-[#444746]">Email verification pending</p>
                            </div>
                            <span class="font-space font-bold text-xs px-2 py-1 bg-[#f59e0b] text-white">Pending</span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <form method="POST" action="{{ route('admin.users.status', $candidate) }}" class="w-full">@csrf @method('PATCH')<input type="hidden" name="account_status" value="active"><button class="w-full bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-4 py-2 font-space font-bold text-xs uppercase tracking-wider hover:brightness-110">Verify</button></form>
                            <form method="POST" action="{{ route('admin.users.status', $candidate) }}" class="w-full">@csrf @method('PATCH')<input type="hidden" name="account_status" value="suspended"><button class="w-full border-2 border-[#006a38] text-[#006a38] px-4 py-2 font-space font-bold text-xs uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)]">Reject</button></form>
                        </div>
                    </article>
                @empty
                    <p class="bg-[#f3f3f3] p-4 font-manrope text-sm text-[#888888]">No pending user verifications.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-5">
            <h3 class="font-space font-bold text-2xl text-[#1a1c1c]">Priority Disputes</h3>
            <div class="mt-4 space-y-3">
                @forelse($recentDisputes as $dispute)
                    <article class="bg-[#f3f3f3] p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-manrope font-bold text-lg text-[#1a1c1c]">{{ $dispute->subject }}</p>
                                <p class="font-manrope text-sm text-[#888888]">Reporter: {{ $dispute->reporter?->name ?? 'Unknown' }}</p>
                                <p class="mt-1 font-manrope text-xs text-[#444746]">Type: {{ $dispute->transaction_type }} • Opened {{ $dispute->created_at->format('M j') }}</p>
                            </div>
                            <span class="font-space font-bold text-xs px-2 py-1 {{ $dispute->status === 'in_review' ? 'bg-[#3b82f6] text-white' : 'bg-[#f59e0b] text-white' }}">{{ str_replace('_',' ', $dispute->status) }}</span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="block bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-4 py-2 text-center font-space font-bold text-xs uppercase tracking-wider hover:brightness-110">Review</a>
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="block border-2 border-[#006a38] text-[#006a38] px-4 py-2 text-center font-space font-bold text-xs uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)]">Contact Parties</a>
                        </div>
                    </article>
                @empty
                    <p class="bg-[#f3f3f3] p-4 font-manrope text-sm text-[#888888]">No disputes in queue.</p>
                @endforelse
            </div>
        </div>
    </section>
@endif
@endsection
