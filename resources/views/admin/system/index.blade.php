@extends('layouts.admin')

@section('title', 'System Config')

@section('content')
<div class="surface-card-strong p-6 md:p-8">
    <p class="section-kicker">Platform Controls</p>
    <h2 class="section-title mt-1 mb-6">System Configuration</h2>

    <form method="POST" action="{{ route('admin.system.config.update') }}" class="space-y-4">
        @csrf

        <div class="surface-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-extrabold">Manage Categories</h3>
                    <p class="meta-text text-sm">Create, edit, or delete parent categories and subcategories with eco metrics.</p>
                    <p class="font-manrope text-xs text-[#888] mt-1">Currently: {{ $categories->join(', ') ?: 'no categories' }}</p>
                </div>
                <a href="{{ route('admin.categories') }}" class="btn-pill btn-pill-dark !px-4 !py-2 text-sm inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                    Manage Categories
                </a>
            </div>
        </div>

        <div class="surface-card p-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-xl font-extrabold">Notification Settings</h3>
                    <p class="meta-text text-sm">Configure email and push notification policy.</p>
                </div>
            </div>
            <textarea name="notification_policy" rows="3" class="input-field text-sm">{{ old('notification_policy', $settings['notification_policy']) }}</textarea>
        </div>

        <div class="surface-card p-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-xl font-extrabold">Security Policies</h3>
                    <p class="meta-text text-sm">MFA, API keys, and access control policy.</p>
                </div>
            </div>
            <textarea name="security_policy" rows="3" class="input-field text-sm">{{ old('security_policy', $settings['security_policy']) }}</textarea>
        </div>

        <div class="surface-card p-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-xl font-extrabold">Payment Gateways</h3>
                    <p class="meta-text text-sm">Set fee/commission and escrow/deposit policy.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <input type="number" step="0.01" min="0" max="100" name="payment_fee_percent"
                       value="{{ old('payment_fee_percent', $settings['payment_fee_percent']) }}"
                       class="input-field text-sm" placeholder="Fee %">
                <textarea name="escrow_policy" rows="3" class="input-field text-sm">{{ old('escrow_policy', $settings['escrow_policy']) }}</textarea>
            </div>
        </div>

        <div class="surface-card p-4">
            <h3 class="text-xl font-extrabold">Sustainability Guidelines</h3>
            <p class="meta-text text-sm mb-2">Global moderation guidance for eco-claims.</p>
            <textarea name="sustainability_guidelines" rows="3" class="input-field text-sm">{{ old('sustainability_guidelines', $settings['sustainability_guidelines']) }}</textarea>
        </div>

        <div class="flex justify-end">
            <button class="btn-pill btn-pill-dark !px-5 !py-2">Save Configuration</button>
        </div>
    </form>
</div>
@endsection
