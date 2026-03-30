<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-neutral-100 text-neutral-900 antialiased">
    <div class="relative min-h-screen overflow-hidden px-4 py-12 sm:px-8">
        <div class="pointer-events-none absolute -left-16 top-16 h-64 w-64 bg-primary-200/40 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 bottom-10 h-72 w-72 bg-accent-400/45 blur-3xl"></div>

        <div class="mx-auto grid min-h-[85vh] w-full max-w-5xl grid-cols-1 gap-6 md:grid-cols-[1.05fr_0.95fr]">
            <section class="surface-card-strong relative hidden overflow-hidden p-8 md:block">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-800">Sustainable Curator</p>
                <h1 class="mt-6 text-4xl font-bold leading-[0.95] text-neutral-900">Circular Fashion,<br>Curated Like Art.</h1>
                <p class="mt-5 max-w-sm text-sm text-neutral-700">Buy, rent, and swap in a digital gallery built for conscious style and lasting quality.</p>
                <div class="mt-10 bg-neutral-100 p-4 text-xs font-semibold uppercase tracking-[0.08em] text-neutral-700">Reloop Marketplace</div>
            </section>

            <section class="surface-card flex items-center p-6 sm:p-8">
                <div class="w-full">
                    @yield('content')
                </div>
            </section>
        </div>
    </div>
</body>
</html>
