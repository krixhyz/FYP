@extends('layouts.guest')

@section('content')
    <div>
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Email Verification</p>
        <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-1">Verify Your Email</h1>
        <p class="font-manrope text-sm text-[#444746] mb-6">Check your inbox and click the verification link to activate your account.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="bg-green-50 px-4 py-3 mb-6 text-sm font-manrope text-green-700">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <!-- System Policy Section -->
    <div class="mt-8 bg-blue-50 border-2 border-blue-200 p-6 rounded-lg mb-8">
        <p class="font-space font-bold text-[#006a38] mb-3">System Policy</p>
        <ul class="font-manrope text-sm text-[#444746] space-y-2">
            <li class="flex gap-2">
                <span class="text-[#006a38] font-bold">•</span>
                <span>Email verification is required to access all platform features and ensure account security.</span>
            </li>
            <li class="flex gap-2">
                <span class="text-[#006a38] font-bold">•</span>
                <span>Your email address will be kept confidential and used only for account verification and important notifications.</span>
            </li>
            <li class="flex gap-2">
                <span class="text-[#006a38] font-bold">•</span>
                <span>You can postpone email verification and complete it later from your account settings.</span>
            </li>
            <li class="flex gap-2">
                <span class="text-[#006a38] font-bold">•</span>
                <span>Unverified accounts may have limited access to certain features until verification is completed.</span>
            </li>
        </ul>
    </div>

    <!-- Action Buttons -->
    <div class="mt-8 flex flex-wrap items-center gap-3">
        <form method="POST" action="{{ route('verification.send') }}" class="inline">
            @csrf

            <button type="submit" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-6 py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <a href="{{ route('products.index') }}" class="inline-block bg-transparent border-2 border-[#006a38] text-[#006a38] px-6 py-2 font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] transition-all">
            {{ __('Go to Homepage') }}
        </a>

        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf

            <button type="submit" class="bg-transparent border-2 border-red-500 text-red-500 px-[22px] py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-red-50 transition-all">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>

    <!-- Verification Can Be Done Later Notice -->
    <div class="mt-6 p-4 bg-amber-50 border-l-4 border-amber-400">
        <p class="font-manrope text-sm text-[#444746]">
            <span class="font-semibold text-amber-700">Note:</span> You can verify your email later from your profile settings. Continue browsing the platform while you wait for the verification email.
        </p>
    </div>
@endsection