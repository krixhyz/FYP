@extends('layouts.app')

@section('content')
    <div class="space-y-5">
        <div>
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Recovery</p>
            <h1 class="mt-2 font-space text-4xl font-bold text-[#1a1c1c]">Reset Password</h1>
            <p class="mt-2 font-manrope text-sm text-[#444746]">Enter your email to receive a secure password reset link.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="mt-4 border-2 border-[#10b981] bg-[#d1fae5] px-4 py-3 font-manrope text-sm text-[#065f46]" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus class="mt-1 w-full" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="pt-1">
            <x-primary-button>
                {{ __('Send Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
@endsection
