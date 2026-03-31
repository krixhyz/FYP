<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f9f9f9] text-[#1a1c1c]">
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

<div class="min-h-screen pb-16 bg-[#f9f9f9]">
    <header class="mx-auto mt-6 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="surface-card-strong flex flex-wrap items-start justify-between gap-4 p-6">
            <div>
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Control Room</p>
                <h1 class="mt-3 font-space font-bold text-4xl text-[#1a1c1c]">{{ $isSuper ? 'Super Admin Dashboard' : 'Admin Dashboard' }}</h1>
                <p class="mt-2 font-manrope text-sm text-[#444746]">{{ $isSuper ? 'Full platform oversight and control' : 'Operational moderation and user management' }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="bg-[#006a38] px-5 py-3 font-space text-xs font-bold uppercase tracking-widest text-white">
                    {{ $isSuper ? 'Super Admin' : 'Admin' }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-4 py-2 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110">Logout</button>
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
                       class="px-4 py-3 font-space text-sm font-bold uppercase tracking-widest {{ $isActive ? 'bg-[#006a38] text-white' : 'bg-[#f3f3f3] text-[#444746] hover:bg-[#e8e8e8]' }}">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </header>

    <main class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="border-2 border-[#10b981] bg-[#d1fae5] px-4 py-3 font-manrope text-sm text-[#065f46]">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="border-2 border-[#ba1a1a] bg-[#fee2e2] px-4 py-3 font-manrope text-sm text-[#7f1d1d]">
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
