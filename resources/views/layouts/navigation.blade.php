<nav class="sticky top-0 z-50 px-4 pt-4 sm:px-6 lg:px-8">
    <div class="mx-auto w-full max-w-7xl bg-[rgb(243_243_243_/_0.8)] p-2 backdrop-blur-md">
        <div class="flex min-h-14 flex-wrap items-center justify-between gap-3 bg-white px-4 py-2">
            <div class="flex items-center gap-5">
                <a href="{{ route('products.index') }}" class="text-lg font-bold uppercase tracking-[0.12em] text-primary-800">Reloop</a>
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'nav-link-active' : '' }} hidden md:inline-flex">Admin Panel</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }} hidden md:inline-flex">Dashboard</a>
                    @endif
            </div>

            @auth
                @php
                    $navUnreadCount = auth()->user()->unreadNotifications()->count();
                    $navDropdownNotifs = auth()->user()->notifications()->latest()->take(10)->get();
                @endphp
            @endauth

            <div class="hidden items-center gap-2 lg:flex">
                @auth
                    @if(!auth()->user()->isAdmin())
                        <a href="{{ route('wishlist.index') }}" class="btn-pill btn-pill-soft !px-3" title="Wishlist">Wishlist</a>
                        <a href="{{ route('cart.index') }}" class="btn-pill btn-pill-soft !px-3" title="Cart">Cart</a>
                    @endif

                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="relative btn-pill btn-pill-soft !px-3" title="Notifications">
                            Notifications
                            <span id="notification-count" class="{{ $navUnreadCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white">
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
                             class="absolute right-0 top-full z-50 mt-2 w-80 bg-white p-2 shadow-soft"
                             style="display:none;">
                            <div class="mb-2 flex items-center justify-between bg-accent-100 px-3 py-2">
                                <span class="text-xs font-semibold uppercase tracking-[0.08em] text-neutral-900">Notifications</span>
                                <div class="flex items-center gap-2 text-xs">
                                    <button id="mark-all-read-btn" class="font-semibold text-primary-800" onclick="markAllNotificationsRead(event)">Mark all read</button>
                                    <a href="{{ route('notifications.index') }}" class="text-neutral-600 hover:text-neutral-900">See all</a>
                                </div>
                            </div>

                            <div id="notification-dropdown-list" class="max-h-80 space-y-2 overflow-y-auto">
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
                                    <div class="notification-dropdown-item {{ $isUnread ? 'bg-primary-50' : 'bg-accent-50' }} {{ $canClick ? 'cursor-pointer' : 'cursor-not-allowed opacity-70' }} p-3"
                                         data-id="{{ $notif->id }}"
                                         data-url="{{ $url }}"
                                         @if($canClick) onclick="handleDropdownNotifClick(this)" @endif>
                                        <div class="flex items-start gap-2">
                                            @if($isUnread)
                                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-primary-700"></span>
                                            @else
                                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full border border-accent-600 bg-transparent"></span>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <p class="line-clamp-2 text-sm text-neutral-800 {{ $isUnread ? 'font-semibold' : '' }}">{{ $msg }}</p>
                                                <p class="mt-0.5 text-xs text-neutral-500">{{ $notif->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="bg-accent-50 px-3 py-6 text-center text-sm text-neutral-500">No notifications yet</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endauth

                @auth
                    <a href="{{ route('profile.edit') }}" class="btn-pill btn-pill-soft !px-3">Profile</a>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="btn-pill btn-pill-dark !px-3">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-pill btn-pill-soft !px-3">Log In</a>
                    <a href="{{ route('register') }}" class="btn-pill btn-pill-dark !px-3">Register</a>
                @endauth
            </div>

            <div class="flex items-center gap-2 lg:hidden">
                @auth
                    <a href="{{ route('notifications.index') }}" class="relative btn-pill btn-pill-soft !px-2.5 !py-2" title="Notifications">
                        Alerts
                        @if(isset($navUnreadCount) && $navUnreadCount > 0)
                            <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white">
                                {{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}
                            </span>
                        @endif
                    </a>
                @endauth
                @auth
                    @if(!auth()->user()->isAdmin())
                        <a href="{{ route('cart.index') }}" class="btn-pill btn-pill-soft !px-2.5 !py-2">Cart</a>
                    @endif
                @endauth

                <button id="menu-toggle" type="button" class="btn-pill btn-pill-soft !px-2.5 !py-2">Menu</button>
            </div>
        </div>

        <div id="mobile-menu" class="mt-2 hidden bg-white p-3 lg:hidden">
            <div class="flex flex-col gap-2">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn-pill btn-pill-soft justify-start">Admin Panel</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn-pill btn-pill-soft justify-start">Dashboard</a>
                    @endif
                @else
                    <a href="{{ route('dashboard') }}" class="btn-pill btn-pill-soft justify-start">Dashboard</a>
                @endauth

                @auth
                    @if(!auth()->user()->isAdmin())
                        <a href="{{ route('wishlist.index') }}" class="btn-pill btn-pill-soft justify-start">My Wishlist</a>
                        <a href="{{ route('dispute.my') }}" class="btn-pill btn-pill-soft justify-start">My Disputes</a>
                    @endif
                    <a href="{{ route('notifications.index') }}" class="btn-pill btn-pill-soft justify-start">Notifications</a>
                    <a href="{{ route('profile.edit') }}" class="btn-pill btn-pill-soft justify-start">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-pill btn-pill-dark w-full justify-start">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-pill btn-pill-soft justify-start">Log in</a>
                    <a href="{{ route('register') }}" class="btn-pill btn-pill-dark justify-start">Register</a>
                @endauth
            </div>
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
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-primary-700"></span>
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
