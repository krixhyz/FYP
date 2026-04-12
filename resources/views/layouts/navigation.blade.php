<nav class="bg-white/70 backdrop-blur-[24px] sticky top-0 z-50">
    <div class="px-8 md:px-16 py-0 flex h-14 items-center justify-between">
        <div class="flex items-center gap-8">
            <a href="{{ route('products.index') }}" class="font-space font-bold uppercase tracking-wider text-[#006a38] text-sm">Reloop</a>
            @auth
                @if(auth()->check() && auth()->user()->isAdmin())
                    <x-nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.*')">
                        Admin Panel
                    </x-nav-link>
                @else
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>
                @endif
            @endauth
        </div>

        @auth
            @php
                $navUnreadCount = auth()->user()->unreadNotifications()->count();
                $navDropdownNotifs = auth()->user()->notifications()->latest()->take(10)->get();
                $cartCount = auth()->user()->cartItems()->sum('quantity');
            @endphp
        @endauth

        <div class="hidden items-center gap-3 lg:flex">
            @auth
                @if(!auth()->user()->isAdmin())
                    <x-nav-link href="{{ route('wishlist.index') }}" :active="request()->routeIs('wishlist.*')">
                        Wishlist
                    </x-nav-link>
                    <a href="{{ route('cart.index') }}" class="relative font-space text-xs font-medium uppercase tracking-wider {{ request()->routeIs('cart.*') ? 'text-[#006a38] border-b-2 border-[#006a38]' : 'text-[#444746] hover:text-[#006a38]' }} px-3 py-2 transition-colors">
                        Cart
                        <span id="cart-count" class="{{ $cartCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-2 bg-[#e2e2e2] text-[#1a1c1c] text-[10px] font-space font-bold px-2 py-0.5">
                            {{ $cartCount > 0 ? ($cartCount > 99 ? '99+' : $cartCount) : '' }}
                        </span>
                    </a>
                @endif

                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" class="relative font-space text-xs font-medium uppercase tracking-wider text-[#444746] px-3 py-2 hover:text-[#006a38] transition-colors">
                        Notifications
                        <span id="notification-count" class="{{ $navUnreadCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-2 bg-[#e2e2e2] text-[#1a1c1c] text-[10px] font-space font-bold px-2 py-0.5">
                            {{ $navUnreadCount > 0 ? ($navUnreadCount > 99 ? '99+' : $navUnreadCount) : '' }}
                        </span>
                    </button>

                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute right-0 top-full z-50 mt-2 w-80 bg-white/90 backdrop-blur-[24px] shadow-[0_20px_40px_rgba(26,28,28,0.06)]"
                         style="display:none;">
                        <div class="mb-0 flex items-center justify-between bg-[#f3f3f3] px-4 py-3">
                            <span class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Notifications</span>
                            <div class="flex items-center gap-2 text-xs">
                                <button id="mark-all-read-btn" class="font-space font-bold uppercase text-[#006a38] hover:text-[#004a29]" onclick="markAllNotificationsRead(event)">Mark all read</button>
                                <a href="{{ route('notifications.index') }}" class="font-space font-bold uppercase text-[#444746] hover:text-[#006a38]">See all</a>
                            </div>
                        </div>

                        <div id="notification-dropdown-list" class="max-h-80 overflow-y-auto">
                            @forelse($navDropdownNotifs as $notif)
                                @php
                                    $isUnread = is_null($notif->read_at);
                                    $msg = $notif->data['message'] ?? 'Notification';
                                    $url = $notif->data['redirect_url'] ?? route('notifications.index');
                                    $type = $notif->data['type'] ?? 'general';
                                    $canClick = $url !== '#';

                                    if ($type === 'rental' && !empty($notif->data['rental_request_id'])) {
                                        $rentalReq = \App\Models\RentalRequest::find($notif->data['rental_request_id']);

                                        if (!$rentalReq || $rentalReq->status === 'rejected') {
                                            $url = '#';
                                            $canClick = false;
                                        } elseif ($rentalReq->status === 'approved') {
                                            $url = route('products.myListings');
                                            $canClick = true;
                                        } else {
                                            $url = route('rental.review', $rentalReq->id);
                                            $canClick = true;
                                        }
                                    }
                                @endphp
                                <div class="notification-dropdown-item {{ $canClick ? 'cursor-pointer' : 'cursor-not-allowed opacity-70' }} px-4 py-3 border-b border-[rgba(189,202,189,0.2)] {{ $isUnread ? 'bg-white' : 'bg-[#f9f9f9]' }} hover:bg-[#f3f3f3]"
                                     data-id="{{ $notif->id }}"
                                     data-url="{{ $url }}"
                                     @if($canClick) onclick="handleDropdownNotifClick(this)" @endif>
                                    <div class="flex items-start gap-2">
                                        @if($isUnread)
                                            <span class="mt-1 h-2 w-2 shrink-0 bg-[#006a38]"></span>
                                        @else
                                            <span class="mt-1 h-2 w-2 shrink-0 border border-[#bdcabd] bg-transparent"></span>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="line-clamp-2 font-manrope text-sm text-[#1a1c1c] {{ $isUnread ? 'font-medium' : '' }}">{{ $msg }}</p>
                                            <p class="mt-0.5 font-manrope text-xs text-[#444746]">{{ $notif->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="bg-[#f3f3f3] px-4 py-6 text-center font-manrope text-sm text-[#444746]">No notifications yet</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endauth

            @auth
                <x-nav-link href="{{ route('profile.edit') }}" :active="request()->routeIs('profile.*')">
                    Profile
                </x-nav-link>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="font-space text-xs font-bold uppercase tracking-wider text-[#ba1a1a] px-3 py-2 hover:text-[#8a1515] transition-colors">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="font-space text-xs font-medium uppercase tracking-wider text-[#444746] px-3 py-2 hover:text-[#006a38] transition-colors">
                    Log In
                </a>
                <a href="{{ route('register') }}" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-4 py-2 font-space font-bold text-xs uppercase tracking-wider hover:brightness-110 transition-all">
                    Register
                </a>
            @endauth
        </div>

        <div class="flex items-center gap-2 lg:hidden">
            @auth
                <a href="{{ route('notifications.index') }}" class="relative font-space text-xs font-medium uppercase tracking-wider text-[#444746] px-3 py-2 hover:text-[#006a38]" title="Notifications">
                    Alerts
                    @if(isset($navUnreadCount) && $navUnreadCount > 0)
                        <span class="absolute -top-1 -right-1 bg-[#e2e2e2] text-[#1a1c1c] text-[10px] font-space font-bold px-2 py-0.5">
                            {{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}
                        </span>
                    @endif
                </a>
            @endauth
            @auth
                @if(!auth()->user()->isAdmin())
                    <a href="{{ route('cart.index') }}" class="relative font-space text-xs font-medium uppercase tracking-wider text-[#444746] px-3 py-2 hover:text-[#006a38]">Cart
                        @if($cartCount > 0)
                            <span class="absolute -top-1 -right-1 bg-[#e2e2e2] text-[#1a1c1c] text-[10px] font-space font-bold px-2 py-0.5">
                                {{ $cartCount > 99 ? '99+' : $cartCount }}
                            </span>
                        @endif
                    </a>
                @endif
            @endauth

            <button id="menu-toggle" type="button" class="font-space text-xs font-bold uppercase tracking-wider text-[#1a1c1c] px-3 py-2 hover:text-[#006a38]">Menu</button>
        </div>
    </div>

    <div id="mobile-menu" class="hidden bg-white border-t border-[rgba(189,202,189,0.2)] lg:hidden">
        <div class="flex flex-col px-4">
            @auth
                @if(auth()->user()->isAdmin())
                    <x-responsive-nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.*')">Admin Panel</x-responsive-nav-link>
                @else
                    <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
                @endif
            @else
                <x-responsive-nav-link href="{{ route('dashboard') }}">Dashboard</x-responsive-nav-link>
            @endauth

            @auth
                @if(!auth()->user()->isAdmin())
                    <x-responsive-nav-link href="{{ route('wishlist.index') }}" :active="request()->routeIs('wishlist.*')">My Wishlist</x-responsive-nav-link>
                    <x-responsive-nav-link href="{{ route('dispute.my') }}">My Disputes</x-responsive-nav-link>
                @endif
                <x-responsive-nav-link href="{{ route('notifications.index') }}">Notifications</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('profile.edit') }}" :active="request()->routeIs('profile.*')">Profile</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}" class="block w-full">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-3 font-space text-sm uppercase tracking-wider text-[#ba1a1a] border-b border-[rgba(189,202,189,0.2)] hover:text-[#8a1515] min-h-[44px] flex items-center transition-colors">Logout</button>
                </form>
            @else
                <x-responsive-nav-link href="{{ route('login') }}">Log in</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('register') }}">Register</x-responsive-nav-link>
            @endauth
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('menu-toggle');
            const menu = document.getElementById('mobile-menu');
            if (btn && menu) {
                btn.addEventListener('click', () => menu.classList.toggle('hidden'));
            }
        });

        function handleDropdownNotifClick(el) {
            const id = el.dataset.id;
            const url = el.dataset.url;
            if (!id || !url) return;

            fetch('{{ route('notifications.markRead') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (window.Laravel && window.Laravel.csrfToken)
                        ? window.Laravel.csrfToken
                        : document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ id }),
            }).then(() => {
                el.classList.remove('bg-primary-50');
                el.classList.add('bg-accent-50');
                const dot = el.querySelector('span');
                if (dot) {
                    dot.classList.remove('bg-primary-700');
                    dot.classList.add('border', 'border-accent-600', 'bg-transparent');
                }
                const txt = el.querySelector('p');
                if (txt) txt.classList.remove('font-semibold');

                decrementBadge();
                window.location.href = url;
            });
        }

        function markAllNotificationsRead(e) {
            e.stopPropagation();
            fetch('{{ route('notifications.markAllRead') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (window.Laravel && window.Laravel.csrfToken)
                        ? window.Laravel.csrfToken
                        : document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
            }).then(() => {
                const badge = document.getElementById('notification-count');
                if (badge) { badge.textContent = ''; badge.classList.add('hidden'); }

                document.querySelectorAll('#notification-dropdown-list .notification-dropdown-item').forEach(item => {
                    item.classList.remove('bg-primary-50');
                    item.classList.add('bg-accent-50');
                    const dot = item.querySelector('span');
                    if (dot) {
                        dot.classList.remove('bg-primary-700');
                        dot.classList.add('border', 'border-accent-600', 'bg-transparent');
                    }
                    const txt = item.querySelector('p');
                    if (txt) txt.classList.remove('font-semibold');
                });
            });
        }

        function decrementBadge() {
            const badge = document.getElementById('notification-count');
            if (!badge) return;
            const current = parseInt(badge.textContent) || 0;
            if (current <= 1) {
                badge.textContent = '';
                badge.classList.add('hidden');
            } else {
                badge.textContent = current - 1;
            }
        }

        function prependNotificationToDropdown(msg, url, id) {
            const list = document.getElementById('notification-dropdown-list');
            if (!list) return;

            const empty = list.querySelector('.text-neutral-500');
            if (empty) empty.closest('div').remove();

            const el = document.createElement('div');
            el.className = 'notification-dropdown-item bg-primary-50 cursor-pointer p-3';
            el.dataset.id = id;
            el.dataset.url = url;
            el.setAttribute('onclick', 'handleDropdownNotifClick(this)');
            el.innerHTML = `
                <div class="flex items-start gap-2">
                    <span class="mt-1.5 h-2 w-2 shrink-0 bg-primary-700"></span>
                    <div class="min-w-0 flex-1">
                        <p class="line-clamp-2 text-sm font-semibold text-neutral-800">${escapeHtml(msg)}</p>
                        <p class="mt-0.5 text-xs text-neutral-500">just now</p>
                    </div>
                </div>`;
            list.prepend(el);

            const items = list.querySelectorAll('.notification-dropdown-item');
            if (items.length > 10) items[items.length - 1].remove();
        }

        function escapeHtml(str) {
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(str));
            return d.innerHTML;
        }
    </script>
</nav>
