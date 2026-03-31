@extends('layouts.app')

@section('content')
    <section class="space-y-5">
        <div>
            <p class="section-kicker">Workspace</p>
            <h1 class="mt-2 font-space text-4xl font-bold text-[#1a1c1c]">Dashboard</h1>
            <p class="mt-2 font-manrope text-sm text-[#444746]">You are logged in and ready to continue.</p>
        </div>

        @if (session('status'))
            <div class="bg-green-50 px-4 py-3 text-sm font-semibold text-green-700" role="alert">
                {{ session('status') }}
            </div>
        @endif
    </section>
@endsection
