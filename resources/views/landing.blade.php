<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reloop | Circular Marketplace</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .hero-fade {
            animation: heroFade 0.75s ease-out both;
        }

        .hero-rise {
            animation: heroRise 0.7s ease-out both;
        }

        .hero-rise.delay-1 { animation-delay: 0.1s; }
        .hero-rise.delay-2 { animation-delay: 0.2s; }
        .hero-rise.delay-3 { animation-delay: 0.3s; }

        @keyframes heroFade {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes heroRise {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="min-h-screen bg-[#f4f7f2] font-manrope text-[#1a1c1c]">
    @include('layouts.navigation')

    <main class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -left-24 top-16 h-72 w-72 rounded-full bg-[radial-gradient(circle,_rgba(0,106,56,0.2)_0%,_rgba(0,106,56,0)_70%)]"></div>
            <div class="absolute right-[-5rem] top-40 h-80 w-80 rounded-full bg-[radial-gradient(circle,_rgba(255,170,0,0.16)_0%,_rgba(255,170,0,0)_70%)]"></div>
            <div class="absolute bottom-[-7rem] left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-[radial-gradient(circle,_rgba(11,141,80,0.18)_0%,_rgba(11,141,80,0)_72%)]"></div>
        </div>

        <section class="relative mx-auto max-w-7xl px-4 pb-20 pt-14 sm:px-6 lg:px-8 lg:pt-20">
            <div class="grid items-center gap-10 lg:grid-cols-2">
                <div class="hero-fade">
                    <p class="hero-rise inline-flex items-center gap-2 rounded-full border border-[#bed2c1] bg-white/75 px-4 py-1.5 font-space text-[11px] font-bold uppercase tracking-[0.18em] text-[#006a38]">
                        Sustainable Commerce Platform
                    </p>
                    <h1 class="hero-rise delay-1 mt-5 font-space text-4xl font-bold uppercase leading-tight tracking-[0.02em] text-[#133123] sm:text-5xl lg:text-6xl">
                        Buy Smart.
                        <span class="block text-[#006a38]">Rent Better. Swap Freely.</span>
                    </h1>
                    <p class="hero-rise delay-2 mt-5 max-w-xl text-base leading-relaxed text-[#30423a] sm:text-lg">
                        Reloop connects people around circular shopping. Turn unused products into value,
                        discover trusted listings, and reduce waste with every order.
                    </p>

                    <div class="hero-rise delay-3 mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('products.index') }}" class="rounded-full bg-[#006a38] px-6 py-3 font-space text-xs font-bold uppercase tracking-[0.16em] text-white transition hover:bg-[#005a2f]">
                            Explore Marketplace
                        </a>
                        @auth
                            <a href="{{ auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="rounded-full border border-[#acc2b0] bg-white px-6 py-3 font-space text-xs font-bold uppercase tracking-[0.16em] text-[#1a1c1c] transition hover:border-[#006a38] hover:text-[#006a38]">
                                Open Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-full border border-[#acc2b0] bg-white px-6 py-3 font-space text-xs font-bold uppercase tracking-[0.16em] text-[#1a1c1c] transition hover:border-[#006a38] hover:text-[#006a38]">
                                Create Account
                            </a>
                        @endauth
                    </div>
                </div>

                <div class="hero-fade lg:justify-self-end">
                    <div class="relative rounded-[2rem] border border-[#d8e4da] bg-white/80 p-6 shadow-[0_30px_60px_rgba(19,49,35,0.12)] backdrop-blur-xl sm:p-8">
                        <div class="grid gap-4">
                            <div class="rounded-2xl bg-gradient-to-r from-[#006a38] to-[#0b8d50] p-5 text-white">
                                <p class="font-space text-[11px] font-bold uppercase tracking-[0.18em] text-white/80">Community Trust</p>
                                <p class="mt-2 text-2xl font-semibold">Verified Buyers & Sellers</p>
                                <p class="mt-2 text-sm text-white/90">Transparent transactions with in-app notifications and tracked payments.</p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl border border-[#d5e4d8] bg-[#f8fbf8] p-4">
                                    <p class="font-space text-[11px] font-bold uppercase tracking-[0.12em] text-[#006a38]">Buy</p>
                                    <p class="mt-2 text-sm text-[#30423a]">Discover quality second-hand products at accessible prices.</p>
                                </div>
                                <div class="rounded-2xl border border-[#d5e4d8] bg-[#f8fbf8] p-4">
                                    <p class="font-space text-[11px] font-bold uppercase tracking-[0.12em] text-[#006a38]">Rent</p>
                                    <p class="mt-2 text-sm text-[#30423a]">Borrow what you need for short periods without buying new.</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-[#d5e4d8] bg-[#f8fbf8] p-4">
                                <p class="font-space text-[11px] font-bold uppercase tracking-[0.12em] text-[#006a38]">Swap</p>
                                <p class="mt-2 text-sm text-[#30423a]">Exchange products directly and keep useful items in circulation.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] border border-[#d6e3d9] bg-white/75 p-7 shadow-[0_18px_35px_rgba(19,49,35,0.08)] backdrop-blur-xl sm:p-10">
                <div class="grid gap-6 sm:grid-cols-3">
                    <div>
                        <p class="font-space text-[11px] font-bold uppercase tracking-[0.16em] text-[#006a38]">Fast Discovery</p>
                        <p class="mt-2 text-sm leading-relaxed text-[#30423a]">Filter listings by category, mode, condition, and location to find what matters quickly.</p>
                    </div>
                    <div>
                        <p class="font-space text-[11px] font-bold uppercase tracking-[0.16em] text-[#006a38]">Secure Flow</p>
                        <p class="mt-2 text-sm leading-relaxed text-[#30423a]">Clear checkout paths and payment status updates keep users informed end-to-end.</p>
                    </div>
                    <div>
                        <p class="font-space text-[11px] font-bold uppercase tracking-[0.16em] text-[#006a38]">Impact-Driven</p>
                        <p class="mt-2 text-sm leading-relaxed text-[#30423a]">Every reused item reduces waste and helps build a more sustainable campus economy.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('layouts.footer')
</body>
</html>
