@extends('layouts.guest')

@section('guest-card-class', 'bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-6 md:p-8 w-full max-w-3xl')

@section('content')
    <div class="mb-5">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Email Verification</p>
        <h1 class="font-space font-bold text-3xl md:text-4xl text-[#1a1c1c] mb-1">Verify Your Email</h1>
        <p class="font-manrope text-sm text-[#444746]">Check your inbox and click the verification link to activate your account.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="bg-green-50 px-4 py-3 mb-6 text-sm font-manrope text-green-700">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    @if ($errors->has('email'))
        <div class="bg-red-50 border border-red-200 px-4 py-3 mb-6 text-sm font-manrope text-red-700">
            {{ $errors->first('email') }}
        </div>
    @endif

    <div class="grid gap-5 md:grid-cols-2">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="font-space font-bold text-[#006a38] mb-2 text-sm uppercase tracking-wider">What You Need to Know</p>
            <ul class="font-manrope text-sm text-[#444746] space-y-2 list-disc list-inside marker:text-[#006a38]">
                <li>Email verification is required for full platform access.</li>
                <li>Your email is used only for account security and alerts.</li>
                <li>You can resend the link if it has expired or not arrived.</li>
                <li>Some features stay limited until verification is complete.</li>
            </ul>
        </div>

        <div class="bg-[#f6faf7] border border-[rgba(189,202,189,0.5)] rounded-lg p-4 flex flex-col justify-between gap-4">
            <p class="font-manrope text-sm text-[#444746]">Use the actions below to resend your verification link or continue browsing while you wait.</p>

            <div class="space-y-2">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-4 py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all rounded-md">
                        {{ __('Resend Verification Email') }}
                    </button>
                </form>

                <a href="{{ route('products.index') }}" class="w-full inline-flex items-center justify-center bg-transparent border-2 border-[#006a38] text-[#006a38] px-4 py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] transition-all rounded-md">
                    {{ __('Go to Homepage') }}
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-transparent border-2 border-red-500 text-red-500 px-4 py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-red-50 transition-all rounded-md">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection