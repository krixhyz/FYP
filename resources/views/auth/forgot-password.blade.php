@extends('layouts.guest')

@section('content')
    <div>
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Recovery</p>
        <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-1">Reset Password</h1>
        <p class="font-manrope text-sm text-[#444746] mb-6">Enter your account email and we will send a secure reset link.</p>
    </div>

    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-6">
        @csrf

        <div>
            <label for="email" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full">
            @error('email')<p class="font-manrope text-sm text-[#ba1a1a] mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="w-full bg-gradient-to-br from-[#006a38] to-[#09864a] text-white py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all">
            Email Password Reset Link
        </button>
    </form>
@endsection