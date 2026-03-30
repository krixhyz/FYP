<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-neutral-100 text-neutral-900">
@php
    $adminUser = auth()->user();
    $isSuper = $adminUser->isSuperAdmin();

    $tabs = $isSuper
        ? [
            ['label' => 'Overview', 'route' => 'admin.dashboard', 'active' => ['admin.dashboard']],
            ['label' => 'User Management', 'route' => 'admin.users', 'active' => ['admin.users*']],
            ['label' => 'Content Moderation', 'route' => 'admin.content', 'active' => ['admin.content*', 'admin.products*']],
            ['label' => 'Transactions', 'route' => 'admin.transactions', 'active' => ['admin.transactions*']],
            ['label' => 'Analytics', 'route' => 'admin.analytics', 'active' => ['admin.analytics*']],
            ['label' => 'System Config', 'route' => 'admin.system.config', 'active' => ['admin.system.config*']],
        ]
        : [
            ['label' => 'Overview', 'route' => 'admin.dashboard', 'active' => ['admin.dashboard']],
            ['label' => 'User Management', 'route' => 'admin.users', 'active' => ['admin.users*']],
            ['label' => 'Content Moderation', 'route' => 'admin.content', 'active' => ['admin.content*', 'admin.products*']],
            ['label' => 'Transactions', 'route' => 'admin.transactions', 'active' => ['admin.transactions*']],
            ['label' => 'Disputes', 'route' => 'admin.disputes', 'active' => ['admin.disputes*']],
            ['label' => 'Reports', 'route' => 'admin.reports', 'active' => ['admin.reports*']],
        ];
@endphp

<div class="min-h-screen pb-16">
    <header class="mx-auto mt-6 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="surface-card-strong flex flex-wrap items-start justify-between gap-4 p-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-primary-800">Control Room</p>
                <h1 class="mt-3 text-4xl font-bold text-neutral-900">{{ $isSuper ? 'Super Admin Dashboard' : 'Admin Dashboard' }}</h1>
                <p class="mt-2 text-sm text-neutral-700">{{ $isSuper ? 'Full platform oversight and control' : 'Operational moderation and user management' }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="bg-primary-800 px-5 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-white">
                    {{ $isSuper ? 'Super Admin' : 'Admin' }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-pill btn-pill-dark !px-4 !py-2">Logout</button>
                </form>
            </div>
        </div>

        <div class="surface-card mt-4 p-3">
            <nav class="flex flex-wrap gap-2">
                @foreach($tabs as $tab)
                    @php
                        $isActive = false;
                        foreach ($tab['active'] as $pattern) {
                            if (request()->routeIs($pattern)) {
                                $isActive = true;
                                break;
                            }
                        }
                    @endphp
                    <a href="{{ route($tab['route']) }}"
                       class="px-4 py-3 text-sm font-semibold uppercase tracking-[0.06em] {{ $isActive ? 'bg-primary-800 text-white' : 'bg-accent-100 text-neutral-700 hover:bg-accent-200' }}">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </header>

    <main class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="surface-card p-4 sm:p-6 lg:p-8">
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
