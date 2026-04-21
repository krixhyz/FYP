@extends('layouts.guest')

@section('content')
    <div>
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Welcome Back</p>
        <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-1">Sign In To Continue</h1>
        <p class="font-manrope text-sm text-[#444746] mb-6" style="max-width: 480px">Access your marketplace dashboard, orders, and account controls.</p>

        @if ($errors->any())
            <div class="bg-[#ba1a1a] text-white p-4 mb-6 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-6">
        @csrf

        <div>
            <label for="email" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full">
            @error('email')<p class="font-manrope text-sm text-[#ba1a1a] mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Password</label>
                <a href="{{ route('password.request') }}" class="font-space text-xs font-bold uppercase tracking-widest text-[#006a38] hover:text-[#004a29]">Forgot password?</a>
            </div>
            <input id="password" name="password" type="password" required
                   class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full">
            @error('password')<p class="font-manrope text-sm text-[#ba1a1a] mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-2 text-sm text-[#444746]">
            <input id="remember" name="remember" type="checkbox" value="1" class="w-4 h-4 bg-white border-2 border-gray-400 text-[#006a38] focus:outline-none">
            <label for="remember" class="font-manrope text-sm">Keep me signed in</label>
        </div>

        <button type="submit" class="w-full bg-gradient-to-br from-[#006a38] to-[#09864a] text-white py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all">
            Sign In
        </button>

        <p class="text-center font-manrope text-sm text-[#444746]">
            New here?
            <a href="{{ route('register') }}" class="font-space font-bold uppercase text-[#006a38] hover:text-[#004a29] tracking-wider">Create an account</a>
        </p>
    </form>
@endsection
