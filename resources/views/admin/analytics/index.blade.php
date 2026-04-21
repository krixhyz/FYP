@extends('layouts.admin')

@section('title', 'Analytics')

@section('content')
<div class="surface-card-strong p-6 md:p-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="section-kicker">Admin Insights</p>
            <h2 class="section-title mt-1">Platform Analytics</h2>
        </div>
        <div class="flex items-center gap-2">
            <button class="btn-pill btn-pill-soft !px-4 !py-2 text-sm">Date Range</button>
            <a href="{{ route('admin.reports', ['export' => 'csv']) }}" class="btn-pill btn-pill-dark !px-4 !py-2 text-sm">Generate Custom Report</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="surface-card p-5">
            <p class="meta-text">User Growth</p>
            <p class="mt-2 text-4xl font-extrabold">{{ $userGrowthThisMonth }}</p>
            <p class="mt-1 font-manrope text-xs text-[#888888]">vs last month {{ $userGrowthLastMonth }}</p>
        </div>
        <div class="surface-card p-5">
            <p class="meta-text">Revenue Growth</p>
            <p class="mt-2 text-4xl font-extrabold">Rs. {{ number_format($revenueThisMonth, 2) }}</p>
            <p class="mt-1 font-manrope text-xs text-[#888888]">vs last month Rs. {{ number_format($revenueLastMonth, 2) }}</p>
        </div>
        <div class="surface-card p-5">
            <p class="meta-text">Listing Growth</p>
            <p class="mt-2 text-4xl font-extrabold">{{ $listingGrowthThisMonth }}</p>
            <p class="mt-1 font-manrope text-xs text-[#888888]">vs last month {{ $listingGrowthLastMonth }}</p>
        </div>
    </div>

    <div class="surface-card p-6">
        <h3 class="text-2xl font-extrabold">Sustainability Impact</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div>
                <p class="text-4xl font-extrabold text-[#006a38]">{{ number_format($totalProducts * 0.0043, 1) }}</p>
                <p class="font-manrope text-sm text-[#444746]">tons CO2 Saved</p>
            </div>
            <div>
                <p class="text-4xl font-extrabold text-[#006a38]">{{ number_format($totalProducts) }}</p>
                <p class="font-manrope text-sm text-[#444746]">Items Reused</p>
            </div>
            <div>
                <p class="text-4xl font-extrabold text-[#006a38]">89%</p>
                <p class="font-manrope text-sm text-[#444746]">Waste Reduction</p>
            </div>
            <div>
                <p class="text-4xl font-extrabold text-[#006a38]">{{ number_format($activeUsers) }}</p>
                <p class="font-manrope text-sm text-[#444746]">Eco Champions</p>
            </div>
        </div>
    </div>

    <div class="surface-card p-10 text-center">
        <p class="text-xl font-semibold">Advanced charts and visualizations coming soon</p>
        <button class="btn-pill btn-pill-dark mt-4 !px-5 !py-2 text-sm">Configure Analytics Tools</button>
    </div>
</div>
@endsection
