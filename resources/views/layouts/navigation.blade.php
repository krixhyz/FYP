<nav class="sticky top-0 z-50 bg-transparent"> 
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mt-4">
            <div class="rounded-full p-[1.5px] bg-gradient-to-r from-blue-500/40 via-rose-500/40 to-amber-500/40">
                <div class="rounded-full bg-white/80 backdrop-blur shadow-sm">
                
                    <div class="flex h-14 items-center justify-between px-4">
                    
                        <!-- LEFT SIDE -->
                        <div class="flex items-center gap-8">
                            <a href="{{ route('products.index') }}" 
                               class="text-lg font-semibold tracking-tight text-gray-900 hover:text-gray-700 whitespace-nowrap">
                               Reloop
                            </a>

                            <div class="hidden lg:flex items-center gap-6">
                                <a href="{{ route('dashboard') }}"
                                   class="inline-flex items-center whitespace-nowrap text-sm font-medium 
                                   {{ request()->routeIs('dashboard') ? 'text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                                    Dashboard
                                </a>
                            </div>
                        </div>

                        <!-- RIGHT SIDE -->
                        <div class="hidden lg:flex items-center gap-3 shrink-0 ml-auto">
                            @auth
                                <a href="{{ route('wishlist.index') }}"
                                   class="text-gray-600 hover:text-red-500 inline-flex items-center transition"
                                   title="Wishlist">
                                    <svg class="h-5 w-5" fill="{{ request()->routeIs('wishlist.*') ? 'currentColor' : 'none' }}"
                                         stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </a>

                                {{-- ─── NOTIFICATION BELL ──────────────────────────────────── --}}
                                @php
                                    $navUnreadCount = auth()->user()->unreadNotifications()->count();
                                    $navDropdownNotifs = auth()->user()->notifications()->latest()->take(10)->get();
                                @endphp
                                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button @click="open = !open"
                                            class="relative text-gray-600 hover:text-gray-900 inline-flex items-center focus:outline-none"
                                            title="Notifications">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                        <span id="notification-count"
                                              class="{{ $navUnreadCount > 0 ? '' : 'hidden' }} absolute -top-1.5 -right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white leading-none">
                                            {{ $navUnreadCount > 0 ? ($navUnreadCount > 99 ? '99+' : $navUnreadCount) : '' }}
                                        </span>
                                    </button>

                                    {{-- Dropdown panel --}}
                                    <div x-show="open"
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute right-0 mt-2 w-80 origin-top-right rounded-2xl bg-white shadow-xl ring-1 ring-black/5 focus:outline-none z-50"
                                         style="display:none; top: 100%;">
                                        {{-- Header --}}
                                        <div class="flex items-center justify-between px-4 py-3 border-b">
                                            <span class="text-sm font-semibold text-gray-900">Notifications</span>
                                            <div class="flex items-center gap-2">
                                                <button id="mark-all-read-btn"
                                                        class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                                                        onclick="markAllNotificationsRead(event)">
                                                    Mark all read
                                                </button>
                                                <a href="{{ route('notifications.index') }}"
                                                   class="text-xs text-gray-500 hover:text-gray-700">
                                                    See all
                                                </a>
                                            </div>
                                        </div>
                                        {{-- Notification list --}}
                                        <div id="notification-dropdown-list" class="max-h-80 overflow-y-auto divide-y divide-gray-100">
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
                                                <div class="notification-dropdown-item {{ $isUnread ? 'bg-blue-50' : 'bg-white' }} hover:bg-gray-50 {{ $canClick ? 'cursor-pointer' : 'cursor-not-allowed opacity-70' }} px-4 py-3 transition"
                                                     data-id="{{ $notif->id }}"
                                                     data-url="{{ $url }}"
                                                     @if($canClick) onclick="handleDropdownNotifClick(this)" @endif>
                                                    <div class="flex items-start gap-2">
                                                        @if($isUnread)
                                                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                                                        @else
                                                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-transparent border border-gray-300"></span>
                                                        @endif
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-sm text-gray-800 {{ $isUnread ? 'font-medium' : '' }} line-clamp-2">{{ $msg }}</p>
                                                            <p class="text-xs text-gray-400 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="px-4 py-8 text-center text-sm text-gray-400">
                                                    No notifications yet
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                                {{-- ─────────────────────────────────────────────────────── --}}
                            @endauth

                            <a href="{{ route('cart.index') }}" 
                               class="text-gray-600 hover:text-gray-900 inline-flex items-center" 
                               aria-label="Cart">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.293 2.293a1 1 0 
                                      00.707 1.707H19m-7 0a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 
                                      100 4 2 2 0 000-4z"/>
                                </svg>
                            </a>

                            @auth
                                <a href="{{ route('profile.edit') }}"
                                   class="inline-flex items-center whitespace-nowrap text-sm font-medium text-gray-700 hover:text-gray-900">
                                    Profile
                                </a>

                                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center whitespace-nowrap rounded-full bg-gray-900 text-white text-sm px-4 py-2 hover:bg-gray-800">
                                        Logout
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}"
                                   class="inline-flex items-center whitespace-nowrap rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-900">
                                    Log in
                                </a>
                                <a href="{{ route('register') }}"
                                   class="inline-flex items-center whitespace-nowrap rounded-full bg-gray-900 text-white text-sm px-4 py-2 hover:bg-gray-800">
                                    Register
                                </a>
                            @endauth
                        </div>

                        <!-- MOBILE RIGHT -->
                        <div class="flex lg:hidden items-center gap-3">
                            @auth
                                {{-- Mobile bell --}}
                                <a href="{{ route('notifications.index') }}"
                                   class="relative text-gray-600 hover:text-gray-900" title="Notifications">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    @if(isset($navUnreadCount) && $navUnreadCount > 0)
                                        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                            {{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}
                                        </span>
                                    @endif
                                </a>
                            @endauth
                            <a href="{{ route('cart.index') }}" class="text-gray-600 hover:text-gray-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.293 2.293a1 1 0 
                                          00.707 1.707H19m-7 0a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 
                                          100 4 2 2 0 000-4z" />
                                </svg>
                            </a>
                            <button id="menu-toggle" type="button"
                                    class="inline-flex items-center justify-center rounded-full p-2 text-gray-700 hover:bg-gray-100">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- MOBILE MENU -->
                    <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-100">
                        <div class="p-3 flex flex-col gap-2">
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center rounded-full px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                                Dashboard
                            </a>

                            @auth
                                <a href="{{ route('wishlist.index') }}"
                                   class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                    My Wishlist
                                </a>
                                <a href="{{ route('notifications.index') }}"
                                   class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                                    🔔 Notifications
                                    @if(isset($navUnreadCount) && $navUnreadCount > 0)
                                        <span class="ml-auto flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                            {{ $navUnreadCount }}
                                        </span>
                                    @endif
                                </a>
                                <a href="{{ route('dispute.my') }}"
                                   class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                                    ⚑ My Disputes
                                </a>
                                <a href="{{ route('profile.edit') }}"
                                   class="inline-flex items-center rounded-full px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                                    Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center rounded-full bg-gray-900 text-white px-3 py-2 text-sm hover:bg-gray-800">
                                        Logout
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}"
                                   class="inline-flex items-center rounded-full px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                                    Log in
                                </a>
                                <a href="{{ route('register') }}"
                                   class="inline-flex items-center rounded-full bg-gray-900 text-white px-3 py-2 text-sm hover:bg-gray-800">
                                    Register
                                </a>
                            @endauth
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const btn = document.getElementById("menu-toggle");
            const menu = document.getElementById("mobile-menu");
            if (btn && menu) {
                btn.addEventListener("click", () => menu.classList.toggle("hidden"));
            }
        });

        /**
         * Handle clicking a notification in the dropdown:
         * marks it as read via AJAX, then redirects.
         */
        function handleDropdownNotifClick(el) {
            const id  = el.dataset.id;
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
                // Visually mark as read
                el.classList.remove('bg-blue-50');
                el.classList.add('bg-white');
                const dot = el.querySelector('span');
                if (dot) {
                    dot.classList.remove('bg-blue-500');
                    dot.classList.add('border', 'border-gray-300', 'bg-transparent');
                }
                const txt = el.querySelector('p');
                if (txt) txt.classList.remove('font-medium');

                // Decrease badge
                decrementBadge();

                window.location.href = url;
            });
        }

        /**
         * Mark all notifications as read.
         */
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
                // Clear badge
                const badge = document.getElementById('notification-count');
                if (badge) { badge.textContent = ''; badge.classList.add('hidden'); }

                // Style all dropdown items as read
                document.querySelectorAll('#notification-dropdown-list .notification-dropdown-item').forEach(item => {
                    item.classList.remove('bg-blue-50');
                    item.classList.add('bg-white');
                    const dot = item.querySelector('span');
                    if (dot) {
                        dot.classList.remove('bg-blue-500');
                        dot.classList.add('border', 'border-gray-300', 'bg-transparent');
                    }
                    const txt = item.querySelector('p');
                    if (txt) txt.classList.remove('font-medium');
                });
            });
        }

        /**
         * Decrease the bell badge by 1.
         */
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

        /**
         * Prepend a notification to the dropdown list.
         */
        function prependNotificationToDropdown(msg, url, id) {
            const list = document.getElementById('notification-dropdown-list');
            if (!list) return;

            // Remove "No notifications" placeholder if present
            const empty = list.querySelector('.text-gray-400');
            if (empty) empty.closest('div').remove();

            const el = document.createElement('div');
            el.className = 'notification-dropdown-item bg-blue-50 hover:bg-gray-50 cursor-pointer px-4 py-3 transition';
            el.dataset.id  = id;
            el.dataset.url = url;
            el.setAttribute('onclick', 'handleDropdownNotifClick(this)');
            el.innerHTML = `
                <div class="flex items-start gap-2">
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-800 font-medium line-clamp-2">${escapeHtml(msg)}</p>
                        <p class="text-xs text-gray-400 mt-0.5">just now</p>
                    </div>
                </div>`;
            list.prepend(el);

            // Keep list trimmed to 10
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
