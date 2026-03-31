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

    <div class="mt-8 flex flex-wrap items-center gap-3">
        <form method="POST" action="{{ route('verification.send') }}" class="inline">
            @csrf

            <button type="submit" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-6 py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf

            <button type="submit" class="bg-transparent border-2 border-[#006a38] text-[#006a38] px-[22px] py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] transition-all">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
@endsection