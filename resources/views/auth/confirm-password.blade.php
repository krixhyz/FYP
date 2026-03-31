@extends('layouts.guest')

@section('content')
    <div>
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Security Check</p>
        <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-1">Confirm Your Password</h1>
        <p class="font-manrope text-sm text-[#444746] mb-6">This protected action requires your current password.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-6">
        @csrf

        <div>
            <label for="password" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full">
            @error('password')<p class="font-manrope text-sm text-[#ba1a1a] mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="w-full bg-gradient-to-br from-[#006a38] to-[#09864a] text-white py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all">
            {{ __('Confirm') }}
        </button>
    </form>
@endsection