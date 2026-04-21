@extends('layouts.admin')

@section('content')
<section class="px-0 md:px-8 py-8">
    <div>
        <p class="font-space text-[12px] font-bold uppercase tracking-widest text-[#888] mb-2">Account Management</p>
        <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-2">Admin Profile Settings</h1>
        <p class="font-manrope text-base text-[#444746]">Manage your admin account details, password, and security settings.</p>
    </div>
</section>

<section class="px-0 md:px-8 py-6">
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 md:p-8">
        <div class="mb-6 pb-6 border-b border-[rgba(189,202,189,0.1)]">
            <p class="font-space text-[12px] font-bold uppercase tracking-widest text-[#888] mb-2">Personal Information</p>
            <h2 class="font-space font-bold text-2xl text-[#1a1c1c]">Profile Information</h2>
        </div>
        <div class="max-w-2xl">
            @include('profile.partials.update-profile-information-form', [
                'profileUpdateAction' => route('admin.profile.update'),
            ])
        </div>
    </div>
</section>

<section class="px-0 md:px-8 py-6">
    <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 md:p-8">
        <div class="mb-6 pb-6 border-b border-[rgba(189,202,189,0.1)]">
            <p class="font-space text-[12px] font-bold uppercase tracking-widest text-[#888] mb-2">Security</p>
            <h2 class="font-space font-bold text-2xl text-[#1a1c1c]">Update Password</h2>
        </div>
        <div class="max-w-2xl">
            @include('profile.partials.update-password-form')
        </div>
    </div>
</section>

<div class="h-8"></div>
@endsection
