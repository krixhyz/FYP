@extends('layouts.admin')

@section('title', 'Analytics')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Super Admin · Insights</p>
            <h2 class="font-space font-bold text-2xl text-[#1a1c1c] mt-1">Platform Analytics</h2>
        </div>
        <a href="{{ route('admin.reports', ['export' => 'csv']) }}" class="inline-flex items-center gap-2 bg-[#006a38] text-white px-4 py-2 font-space font-bold text-xs uppercase tracking-wider rounded hover:bg-[#09864a] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
    </div>

    {{-- KPI Cards --}}
    @php
        $revenueGrowthPct = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : ($revenueThisMonth > 0 ? 100 : 0);
        $userGrowthPct = $userGrowthLastMonth > 0
            ? round((($userGrowthThisMonth - $userGrowthLastMonth) / $userGrowthLastMonth) * 100, 1)
            : ($userGrowthThisMonth > 0 ? 100 : 0);
        $listingGrowthPct = $listingGrowthLastMonth > 0
            ? round((($listingGrowthThisMonth - $listingGrowthLastMonth) / $listingGrowthLastMonth) * 100, 1)
            : ($listingGrowthThisMonth > 0 ? 100 : 0);
    @endphp

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <article class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5 border-l-4 border-[#006a38]">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Revenue This Month</p>
            <p class="mt-2 font-space font-bold text-3xl text-[#006a38]">Rs. {{ number_format($revenueThisMonth, 0) }}</p>
            <div class="mt-3 flex items-center gap-1.5">
                @if($revenueGrowthPct >= 0)
                    <svg class="w-3.5 h-3.5 text-[#006a38]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    <span class="text-xs font-bold text-[#006a38]">+{{ $revenueGrowthPct }}%</span>
                @else
                    <svg class="w-3.5 h-3.5 text-[#ba1a1a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    <span class="text-xs font-bold text-[#ba1a1a]">{{ $revenueGrowthPct }}%</span>
                @endif
                <span class="text-xs text-[#888]">vs last month</span>
            </div>
        </article>

        <article class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5 border-l-4 border-[#3b82f6]">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">New Users This Month</p>
            <p class="mt-2 font-space font-bold text-3xl text-[#1a1c1c]">{{ number_format($userGrowthThisMonth) }}</p>
            <div class="mt-3 flex items-center gap-1.5">
                @if($userGrowthPct >= 0)
                    <svg class="w-3.5 h-3.5 text-[#006a38]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    <span class="text-xs font-bold text-[#006a38]">+{{ $userGrowthPct }}%</span>
                @else
                    <svg class="w-3.5 h-3.5 text-[#ba1a1a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    <span class="text-xs font-bold text-[#ba1a1a]">{{ $userGrowthPct }}%</span>
                @endif
                <span class="text-xs text-[#888]">vs last month</span>
            </div>
        </article>

        <article class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5 border-l-4 border-[#f59e0b]">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">New Listings This Month</p>
            <p class="mt-2 font-space font-bold text-3xl text-[#1a1c1c]">{{ number_format($listingGrowthThisMonth) }}</p>
            <div class="mt-3 flex items-center gap-1.5">
                @if($listingGrowthPct >= 0)
                    <svg class="w-3.5 h-3.5 text-[#006a38]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    <span class="text-xs font-bold text-[#006a38]">+{{ $listingGrowthPct }}%</span>
                @else
                    <svg class="w-3.5 h-3.5 text-[#ba1a1a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    <span class="text-xs font-bold text-[#ba1a1a]">{{ $listingGrowthPct }}%</span>
                @endif
                <span class="text-xs text-[#888]">vs last month</span>
            </div>
        </article>

        <article class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5 border-l-4 border-[#006a38]">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Total Transactions</p>
            <p class="mt-2 font-space font-bold text-3xl text-[#1a1c1c]">{{ number_format($txOrderCount + $txRentalCount + $txSwapCount) }}</p>
            <p class="mt-3 text-xs text-[#888]">all time completed</p>
        </article>
    </section>

    {{-- Revenue + Transaction donut --}}
    <section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Revenue Trend</p>
            <h3 class="font-space font-bold text-lg text-[#1a1c1c] mb-4">Service Fees Earned — Last 6 Months</h3>
            <div class="relative h-64">
                <canvas id="revenueLineChart"></canvas>
            </div>
        </div>

        <div class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Transaction Mix</p>
            <h3 class="font-space font-bold text-lg text-[#1a1c1c] mb-4">Completed Transactions</h3>
            <div class="relative h-48">
                <canvas id="txDonutChart"></canvas>
            </div>
            <div class="mt-4 space-y-2">
                @php $txTotal = $txOrderCount + $txRentalCount + $txSwapCount ?: 1; @endphp
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#006a38] inline-block"></span>Purchases</span>
                    <span class="font-bold">{{ number_format($txOrderCount) }} <span class="text-[#888] font-normal text-xs">({{ round($txOrderCount/$txTotal*100) }}%)</span></span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#3b82f6] inline-block"></span>Rentals</span>
                    <span class="font-bold">{{ number_format($txRentalCount) }} <span class="text-[#888] font-normal text-xs">({{ round($txRentalCount/$txTotal*100) }}%)</span></span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#f59e0b] inline-block"></span>Swaps</span>
                    <span class="font-bold">{{ number_format($txSwapCount) }} <span class="text-[#888] font-normal text-xs">({{ round($txSwapCount/$txTotal*100) }}%)</span></span>
                </div>
            </div>
        </div>
    </section>

    {{-- User + Listing growth charts --}}
    <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">User Growth</p>
            <h3 class="font-space font-bold text-lg text-[#1a1c1c] mb-4">New Registrations — Last 6 Months</h3>
            <div class="relative h-52">
                <canvas id="usersBarChart"></canvas>
            </div>
        </div>
        <div class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-5">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Listing Growth</p>
            <h3 class="font-space font-bold text-lg text-[#1a1c1c] mb-4">New Listings — Last 6 Months</h3>
            <div class="relative h-52">
                <canvas id="listingsBarChart"></canvas>
            </div>
        </div>
    </section>

    {{-- Sustainability --}}
    <section class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-6">
        <div class="mb-6">
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Environmental Impact</p>
            <h3 class="font-space font-bold text-lg text-[#1a1c1c] mt-1">Sustainability Metrics</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-[#f0f8f5] rounded-lg p-4 text-center">
                <p class="font-space font-bold text-3xl text-[#006a38]">{{ number_format($co2Saved, 1) }}</p>
                <p class="font-manrope text-xs text-[#444746] mt-1">tons CO₂ Saved</p>
                <div class="mt-2 w-full bg-[#c8e6d8] rounded-full h-1.5">
                    <div class="bg-[#006a38] h-1.5 rounded-full" style="width:{{ min(100, $co2Saved * 10) }}%"></div>
                </div>
            </div>
            <div class="bg-[#f0f8f5] rounded-lg p-4 text-center">
                <p class="font-space font-bold text-3xl text-[#006a38]">{{ number_format($totalProducts) }}</p>
                <p class="font-manrope text-xs text-[#444746] mt-1">Items Reused</p>
                <div class="mt-2 w-full bg-[#c8e6d8] rounded-full h-1.5">
                    <div class="bg-[#006a38] h-1.5 rounded-full" style="width:75%"></div>
                </div>
            </div>
            <div class="bg-[#f0f8f5] rounded-lg p-4 text-center">
                <p class="font-space font-bold text-3xl text-[#006a38]">89%</p>
                <p class="font-manrope text-xs text-[#444746] mt-1">Waste Reduction</p>
                <div class="mt-2 w-full bg-[#c8e6d8] rounded-full h-1.5">
                    <div class="bg-[#006a38] h-1.5 rounded-full" style="width:89%"></div>
                </div>
            </div>
            <div class="bg-[#f0f8f5] rounded-lg p-4 text-center">
                <p class="font-space font-bold text-3xl text-[#006a38]">{{ number_format($activeUsers) }}</p>
                <p class="font-manrope text-xs text-[#444746] mt-1">Eco Champions</p>
                <div class="mt-2 w-full bg-[#c8e6d8] rounded-full h-1.5">
                    <div class="bg-[#006a38] h-1.5 rounded-full" style="width:{{ $totalUsers > 0 ? min(100, round($activeUsers/$totalUsers*100)) : 0 }}%"></div>
                </div>
            </div>
        </div>
    </section>

</div>
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

    const labels = @json($chartLabels);

    // Revenue line
    new Chart(document.getElementById('revenueLineChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Service Fee (Rs.)',
                data: @json($revenueChart),
                borderColor: green,
                backgroundColor: 'rgba(0,106,56,0.07)',
                borderWidth: 2.5,
                pointBackgroundColor: green,
                pointRadius: 4,
                fill: true,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' Rs. ' + ctx.parsed.y.toLocaleString() } } },
            scales: {
                x: { grid: { color: gridColor } },
                y: { grid: { color: gridColor }, beginAtZero: true,
                     ticks: { callback: v => 'Rs.' + v.toLocaleString() } }
            }
        }
    });

    // Transaction donut
    new Chart(document.getElementById('txDonutChart'), {
        type: 'doughnut',
        data: {
            labels: ['Purchases', 'Rentals', 'Swaps'],
            datasets: [{
                data: [{{ $txOrderCount }}, {{ $txRentalCount }}, {{ $txSwapCount }}],
                backgroundColor: [green, blue, amber],
                borderWidth: 0, hoverOffset: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '68%',
            plugins: { legend: { display: false } }
        }
    });

    // Users bar
    new Chart(document.getElementById('usersBarChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'New Users',
                data: @json($usersChart),
                backgroundColor: 'rgba(59,130,246,0.15)',
                borderColor: blue,
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

    // Listings bar
    new Chart(document.getElementById('listingsBarChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'New Listings',
                data: @json($listingsChart),
                backgroundColor: 'rgba(245,158,11,0.15)',
                borderColor: amber,
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
});
</script>
@endpush
