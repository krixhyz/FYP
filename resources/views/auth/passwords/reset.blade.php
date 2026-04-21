@extends('layouts.guest')

@section('content')
    <div class="space-y-5">
        <div>
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Recovery</p>
            <h1 class="mt-2 font-space text-4xl font-bold text-[#1a1c1c]">Reset Password</h1>
            <p class="mt-2 font-manrope text-sm text-[#444746]">Set a new password to regain access to your account.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" type="email" name="email" :value="$email ?? old('email')" required autofocus autocomplete="email" class="mt-1 w-full" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" class="mt-1 w-full" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password-confirm" :value="__('Confirm Password')" />
            <x-text-input id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password" class="mt-1 w-full" />
        </div>

        <div class="pt-1">
            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
@endsection
