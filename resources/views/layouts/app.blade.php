<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    {{-- Alpine is loaded via Vite in resources/js/app.js --}}
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
    {{-- Navbar --}}
    @include('layouts.navigation')

    <main class="max-w-7xl mx-auto p-6">
        @yield('content')
    </main>
    @stack('scripts')
</body>

</html>
