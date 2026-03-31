@extends('layouts.guest')

@section('content')
    <div class="space-y-5">
        <div>
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Email Verification</p>
            <h1 class="mt-2 font-space text-4xl font-bold text-[#1a1c1c]">Verify Your Email Address</h1>
            <p class="mt-2 font-manrope text-sm text-[#444746]">Please check your inbox for the verification link before continuing.</p>
        </div>
    </div>

    @if (session('resent'))
        <div class="mt-4 border-2 border-[#10b981] bg-[#d1fae5] px-4 py-3 font-manrope text-sm text-[#065f46]" role="alert">
            {{ __('A fresh verification link has been sent to your email address.') }}
        </div>
    @endif

    <form class="mt-6" method="POST" action="{{ route('verification.send') }}">
        @csrf
        <x-primary-button>
            {{ __('Resend Verification Email') }}
        </x-primary-button>
    </form>
@endsection
