<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @auth
    <script>
        window.Laravel = {
            userId: {{ auth()->id() }},
            csrfToken: '{{ csrf_token() }}'
        };
    </script>
    @endauth
</head>
<body class="bg-[#f9f9f9] font-manrope text-[#1a1c1c]">
    @include('layouts.navigation')

    <main class="mx-auto mt-8 w-full max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
        <div class="surface-card p-4 sm:p-6 lg:p-8">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>

</html>
