@extends('layouts.app')

@section('content')
    <div class="space-y-5">
        <div>
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Security Check</p>
            <h1 class="mt-2 font-space text-4xl font-bold text-[#1a1c1c]">Confirm Password</h1>
            <p class="mt-2 font-manrope text-sm text-[#444746]">Please confirm your password before continuing.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" class="mt-1 w-full" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-primary-button>
                {{ __('Confirm Password') }}
            </x-primary-button>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="btn-pill btn-pill-soft !px-4 !py-2">
                    {{ __('Forgot Your Password?') }}
                </a>
            @endif
        </div>
    </form>
@endsection
