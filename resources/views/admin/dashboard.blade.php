@extends('layouts.admin')

@section('title', 'Overview')

@section('content')
@if($isSuperAdmin)
{{-- ── SUPER ADMIN OVERVIEW ─────────────────────────────────────── --}}

{{-- Stat cards --}}
<section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <article class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5 border-l-4 border-[#006a38]">
        <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Total Users</p>
        <p class="mt-2 font-space font-bold text-4xl text-[#1a1c1c]">{{ number_format($totalUsers) }}</p>
        <div class="mt-3 flex items-center gap-2">
            <span class="inline-flex items-center gap-1 text-xs font-semibold text-[#006a38] bg-[#e8f5ee] px-2 py-0.5 rounded-full">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ number_format($activeUsers) }} active
            </span>
        </div>
    </article>
    <article class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5 border-l-4 border-[#3b82f6]">
        <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Total Listings</p>
        <p class="mt-2 font-space font-bold text-4xl text-[#1a1c1c]">{{ number_format($totalProducts) }}</p>
        <div class="mt-3 flex items-center gap-2">
            @if($flaggedProducts > 0)
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-[#ba1a1a] bg-[#ffe2e2] px-2 py-0.5 rounded-full">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19H19a2 2 0 001.75-2.97L13.75 4a2 2 0 00-3.5 0L3.25 16.03A2 2 0 005.07 19z"/></svg>
                    {{ number_format($flaggedProducts) }} flagged
                </span>
            @else
                <span class="text-xs text-[#888]">No flagged items</span>
            @endif
        </div>
    </article>
    <article class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5 border-l-4 border-[#006a38]">
        <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Monthly Revenue</p>
        <p class="mt-2 font-space font-bold text-4xl text-[#006a38]">Rs. {{ number_format($monthlyRevenue, 0) }}</p>
        <div class="mt-3 flex items-center gap-2">
            <span class="text-xs text-[#888]">{{ number_format($completedTransactions) }} completed transactions</span>
        </div>
    </article>
    <article class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5 border-l-4 border-[#f59e0b]">
        <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Open Disputes</p>
        <p class="mt-2 font-space font-bold text-4xl text-[#1a1c1c]">{{ number_format($openDisputes) }}</p>
        <div class="mt-3 flex items-center gap-2">
            <span class="text-xs text-[#888]">{{ number_format($reportedItems) }} total reported</span>
        </div>
    </article>
</section>

{{-- Charts row --}}
<section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    {{-- Revenue trend --}}
    <div class="xl:col-span-2 bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Platform Revenue</p>
                <h3 class="font-space font-bold text-lg text-[#1a1c1c]">Service Fee Earned — Last 6 Months</h3>
            </div>
        </div>
        <div class="relative h-56">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Transaction type donut --}}
    <div class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5">
        <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Transaction Mix</p>
        <h3 class="font-space font-bold text-lg text-[#1a1c1c] mb-4">Completed by Type</h3>
        <div class="relative h-44">
            <canvas id="txDonut"></canvas>
        </div>
        <div class="mt-4 space-y-2">
            <div class="flex items-center justify-between text-sm">
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#006a38] inline-block"></span> Purchases</span>
                <span class="font-bold">{{ number_format($txOrderCount) }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#3b82f6] inline-block"></span> Rentals</span>
                <span class="font-bold">{{ number_format($txRentalCount) }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#f59e0b] inline-block"></span> Swaps</span>
                <span class="font-bold">{{ number_format($txSwapCount) }}</span>
            </div>
        </div>
    </div>
</section>

{{-- User registrations chart --}}
<section class="mt-6 bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5">
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">User Growth</p>
            <h3 class="font-space font-bold text-lg text-[#1a1c1c]">New Registrations — Last 6 Months</h3>
        </div>
        <span class="text-xs font-bold bg-[#e8f5ee] text-[#006a38] px-3 py-1 rounded-full">{{ number_format($totalUsers) }} total</span>
    </div>
    <div class="relative h-48">
        <canvas id="usersChart"></canvas>
    </div>
</section>

{{-- Moderation + disputes --}}
<section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-space font-bold text-lg text-[#1a1c1c]">Pending Moderation</h3>
            <a href="{{ route('admin.content') }}" class="text-xs font-bold text-[#006a38] hover:underline">View all →</a>
        </div>
        <div class="space-y-3">
            @forelse($products->take(3) as $product)
                <article class="{{ $product->flagged ? 'bg-[#fff5f5] border border-[#fecaca]' : 'bg-[#f9f9f9] border border-[#e5e5e5]' }} p-4 rounded-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-manrope font-bold text-sm text-[#1a1c1c]">{{ $product->title }}</p>
                            <p class="font-manrope text-xs text-[#888]">by {{ $product->user?->name ?? 'N/A' }}</p>
                        </div>
                        <span class="shrink-0 font-space font-bold text-[10px] px-2 py-1 rounded {{ $product->flagged ? 'bg-[#ba1a1a] text-white' : 'bg-[#f59e0b] text-white' }}">{{ $product->flagged ? 'Flagged' : 'Pending' }}</span>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <form method="POST" action="{{ route('admin.content.decision', $product) }}">@csrf @method('PATCH')<input type="hidden" name="decision" value="approve"><button class="w-full bg-[#006a38] text-white px-3 py-1.5 font-space font-bold text-[10px] uppercase tracking-wider rounded hover:bg-[#09864a]">Approve</button></form>
                        <form method="POST" action="{{ route('admin.content.decision', $product) }}">@csrf @method('PATCH')<input type="hidden" name="decision" value="reject"><button class="w-full border border-[#006a38] text-[#006a38] px-3 py-1.5 font-space font-bold text-[10px] uppercase tracking-wider rounded hover:bg-[#f0f8f5]">Reject</button></form>
                    </div>
                </article>
            @empty
                <div class="flex flex-col items-center justify-center py-8 text-center bg-[#f9f9f9] rounded-lg">
                    <svg class="w-10 h-10 text-[#ccc] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="font-manrope text-sm text-[#888]">Queue is clear</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-space font-bold text-lg text-[#1a1c1c]">Recent Disputes</h3>
            <a href="{{ route('admin.disputes') }}" class="text-xs font-bold text-[#006a38] hover:underline">View all →</a>
        </div>
        <div class="space-y-3">
            @forelse($recentDisputes as $dispute)
                <article class="bg-[#fff5f5] border border-[#fecaca] p-4 rounded-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-manrope font-bold text-sm text-[#1a1c1c]">{{ $dispute->subject }}</p>
                            <p class="font-manrope text-xs text-[#888]">{{ $dispute->reporter?->name ?? 'Unknown' }} · {{ $dispute->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="shrink-0 font-space font-bold text-[10px] px-2 py-1 rounded bg-[#ba1a1a] text-white">Open</span>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.disputes.show', $dispute) }}" class="inline-block w-full text-center bg-[#006a38] text-white px-3 py-1.5 font-space font-bold text-[10px] uppercase tracking-wider rounded hover:bg-[#09864a]">Review Dispute</a>
                    </div>
                </article>
            @empty
                <div class="flex flex-col items-center justify-center py-8 text-center bg-[#f9f9f9] rounded-lg">
                    <svg class="w-10 h-10 text-[#ccc] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="font-manrope text-sm text-[#888]">No open disputes</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

@else
{{-- ── REGULAR ADMIN OVERVIEW ───────────────────────────────────── --}}
<section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <article class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5 border-l-4 border-[#f59e0b]">
        <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Pending Verifications</p>
        <p class="mt-2 font-space font-bold text-4xl text-[#1a1c1c]">{{ $pendingProfileVerifications }}</p>
        <p class="mt-3 text-xs text-[#888]">admin review queue</p>
    </article>
    <article class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5 border-l-4 border-[#ba1a1a]">
        <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Flagged Listings</p>
        <p class="mt-2 font-space font-bold text-4xl text-[#1a1c1c]">{{ $flaggedProducts }}</p>
        <p class="mt-3 text-xs text-[#888]">needs moderation</p>
    </article>
    <article class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5 border-l-4 border-[#3b82f6]">
        <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Active Disputes</p>
        <p class="mt-2 font-space font-bold text-4xl text-[#1a1c1c]">{{ $openDisputes }}</p>
        <p class="mt-3 text-xs text-[#888]">in queue</p>
    </article>
    <article class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5 border-l-4 border-[#888]">
        <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Reports to Review</p>
        <p class="mt-2 font-space font-bold text-4xl text-[#1a1c1c]">{{ $reportedItems }}</p>
        <p class="mt-3 text-xs text-[#888]">content / dispute reports</p>
    </article>
</section>

{{-- Queue workload bar --}}
<section class="mt-6 bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5">
    <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">Workload at a Glance</p>
    <h3 class="font-space font-bold text-lg text-[#1a1c1c] mb-4">Queue Breakdown</h3>
    <div class="relative h-44">
        <canvas id="adminQueueChart"></canvas>
    </div>
</section>

<section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-space font-bold text-lg text-[#1a1c1c]">Pending Verifications</h3>
            <a href="{{ route('admin.users') }}" class="text-xs font-bold text-[#006a38] hover:underline">View all →</a>
        </div>
        <div class="space-y-3">
            @forelse($pendingVerifications as $candidate)
                <article class="bg-[#fffbeb] border border-[#fde68a] p-4 rounded-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-manrope font-bold text-sm text-[#1a1c1c]">{{ $candidate->name }}</p>
                            <p class="font-manrope text-xs text-[#888]">{{ $candidate->email }}</p>
                        </div>
                        <span class="shrink-0 font-space font-bold text-[10px] px-2 py-1 rounded bg-[#f59e0b] text-white">Pending</span>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <form method="POST" action="{{ route('admin.users.verify', $candidate) }}">@csrf<button class="w-full bg-[#006a38] text-white px-3 py-1.5 font-space font-bold text-[10px] uppercase tracking-wider rounded hover:bg-[#09864a]">Verify</button></form>
                        <form method="POST" action="{{ route('admin.users.status', $candidate) }}">@csrf @method('PATCH')<input type="hidden" name="account_status" value="suspended"><button class="w-full border border-[#ba1a1a] text-[#ba1a1a] px-3 py-1.5 font-space font-bold text-[10px] uppercase tracking-wider rounded hover:bg-[#fff5f5]">Suspend</button></form>
                    </div>
                </article>
            @empty
                <div class="flex flex-col items-center justify-center py-8 text-center bg-[#f9f9f9] rounded-lg">
                    <svg class="w-10 h-10 text-[#ccc] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="font-manrope text-sm text-[#888]">No pending verifications</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-space font-bold text-lg text-[#1a1c1c]">Priority Disputes</h3>
            <a href="{{ route('admin.disputes') }}" class="text-xs font-bold text-[#006a38] hover:underline">View all →</a>
        </div>
        <div class="space-y-3">
            @forelse($recentDisputes as $dispute)
                <article class="bg-[#fff5f5] border border-[#fecaca] p-4 rounded-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-manrope font-bold text-sm text-[#1a1c1c]">{{ $dispute->subject }}</p>
                            <p class="font-manrope text-xs text-[#888]">{{ $dispute->reporter?->name ?? 'Unknown' }} · {{ $dispute->created_at->format('M j') }}</p>
                        </div>
                        <span class="shrink-0 font-space font-bold text-[10px] px-2 py-1 rounded {{ $dispute->status === 'in_review' ? 'bg-[#3b82f6] text-white' : 'bg-[#f59e0b] text-white' }}">{{ str_replace('_',' ', $dispute->status) }}</span>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.disputes.show', $dispute) }}" class="block text-center w-full bg-[#006a38] text-white px-3 py-1.5 font-space font-bold text-[10px] uppercase tracking-wider rounded hover:bg-[#09864a]">Review</a>
                    </div>
                </article>
            @empty
                <div class="flex flex-col items-center justify-center py-8 text-center bg-[#f9f9f9] rounded-lg">
                    <svg class="w-10 h-10 text-[#ccc] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="font-manrope text-sm text-[#888]">No disputes in queue</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Chart.defaults.font.family = "'Space Grotesk', 'Manrope', sans-serif";
    Chart.defaults.color = '#888888';

    const gridColor = 'rgba(0,0,0,0.05)';
    const green = '#006a38';
    const blue  = '#3b82f6';
    const amber = '#f59e0b';
    const red   = '#ba1a1a';

    @if($isSuperAdmin)
    // Revenue line chart
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Service Fee (Rs.)',
                data: @json($revenueChart),
                borderColor: green,
                backgroundColor: 'rgba(0,106,56,0.08)',
                borderWidth: 2.5,
                pointBackgroundColor: green,
                pointRadius: 4,
                fill: true,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor } },
                y: { grid: { color: gridColor }, beginAtZero: true,
                     ticks: { callback: v => 'Rs.' + v.toLocaleString() } }
            }
        }
    });

    // Transaction donut
    new Chart(document.getElementById('txDonut'), {
        type: 'doughnut',
        data: {
            labels: ['Purchases', 'Rentals', 'Swaps'],
            datasets: [{
                data: [{{ $txOrderCount }}, {{ $txRentalCount }}, {{ $txSwapCount }}],
                backgroundColor: [green, blue, amber],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '68%',
            plugins: { legend: { display: false } }
        }
    });

    // User registrations bar chart
    new Chart(document.getElementById('usersChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'New Users',
                data: @json($usersChart),
                backgroundColor: 'rgba(0,106,56,0.15)',
                borderColor: green,
                borderWidth: 2,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: gridColor }, beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });

    @else
    // Admin queue horizontal bar
    new Chart(document.getElementById('adminQueueChart'), {
        type: 'bar',
        data: {
            labels: ['Pending Verifications', 'Flagged Listings', 'Open Disputes', 'Total Reports'],
            datasets: [{
                label: 'Count',
                data: [{{ $pendingProfileVerifications }}, {{ $flaggedProducts }}, {{ $openDisputes }}, {{ $reportedItems }}],
                backgroundColor: [amber, red, blue, 'rgba(136,136,136,0.5)'],
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor }, beginAtZero: true, ticks: { precision: 0 } },
                y: { grid: { display: false } }
            }
        }
    });
    @endif
});
</script>
@endpush
