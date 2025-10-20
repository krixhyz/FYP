<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>

            <div class="flex items-center gap-3">
                <!-- Dark mode toggle -->
                <button id="darkToggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    <svg id="moon" class="w-5 h-5 dark:hidden text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21.752 15.002A9.718 9.718 0 0112.01 21a9.718 9.718 0 01-9.742-8.748 9.74 9.74 0 0013.2-11.22A9.724 9.724 0 0121.752 15z" />
                    </svg>
                    <svg id="sun" class="hidden w-5 h-5 dark:inline text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 3v1m0 16v1m8.485-8.485l.707.707M4.808 4.808l.707.707m12.97 12.97l.707.707M4.808 19.192l.707.707M21 12h1M2 12H1m9-9h4a9 9 0 100 18h-4a9 9 0 000-18z" />
                    </svg>
                </button>

                <a href="{{ route('products.create') }}" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm shadow-sm transition">
                    Add Listing
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Hero -->
        <div class="bg-gradient-to-r from-green-600 to-emerald-500 rounded-xl p-8 mb-8 text-white shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }}</h2>
                    <p class="text-green-100 text-lg">You're ready to manage your listings and rentals.</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <a href="{{ route('products.create') }}" class="group bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md border border-gray-200 dark:border-gray-700 transition-all">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center group-hover:bg-green-200 dark:group-hover:bg-green-800 transition-colors">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add Listing</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Create a new rental listing</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('products.myListings') }}" class="group bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md border border-gray-200 dark:border-gray-700 transition-all">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-800 transition-colors">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">My Listings</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Manage your active listings</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('products.myPurchases') }}" class="group bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md border border-gray-200 dark:border-gray-700 transition-all">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center group-hover:bg-purple-200 dark:group-hover:bg-purple-800 transition-colors">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18l-2 13H5L3 3zm5 16h8a2 2 0 104 0H8a2 2 0 10-4 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">My Purchases</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">View your rental history</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Notifications -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Rental Notifications</h3>
                        <span id="notification-badge" class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            {{ auth()->user()->unreadNotifications->count() }} New
                        </span>
                    </div>

                    <div class="p-6 space-y-3">
                        @forelse(auth()->user()->unreadNotifications as $notification)
                            @php
                                $rentalId = $notification->data['rental_request_id'] ?? null;
                                $readUrl = route('rental.review', $rentalId ?? 0);
                            @endphp

                            <a href="{{ $readUrl }}" data-notification-id="{{ $notification->id }}"
                               class="notification-item flex items-start space-x-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors block"
                               onclick="event.preventDefault(); markReadAndRedirect(this);">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8V6a2 2 0 00-2-2H5a2 2 0 00-2 2v2m18 0l-9 6-9-6m18 0v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $notification->data['message'] ?? 'You have a new rental request' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                    <button class="text-blue-600 dark:text-blue-400 text-xs font-medium hover:underline mt-2">View Request →</button>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M3 8v8a2 2 0 002 2h14a2 2 0 002-2V8m-9 6v6" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400">No new notifications</p>
                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">You're all caught up.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <aside class="space-y-4">
                @php
                    $activeListings = auth()->user()->rentalListings()->where('status', 'available')->count();
                    $thisMonthRevenue = auth()->user()->approvedRentalsAsOwner()
                        ->whereMonth('created_at', now()->month)
                        ->sum('total_amount');
                    $totalRentalsMade = auth()->user()->rentedItems()->count();
                    $pendingRequests = auth()->user()->incomingRentalRequests()
                        ->where('status', 'requested')->count();
                    $rating = auth()->user()->rating ?? 4.9;
                @endphp

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-white shadow rounded-lg">
                        <h4 class="text-sm text-gray-500">Active Listings</h4>
                        <p class="text-xl font-bold">{{ $activeListings }}</p>
                    </div>
                    <div class="p-4 bg-white shadow rounded-lg">
                        <h4 class="text-sm text-gray-500">This Month Revenue</h4>
                        <p class="text-xl font-bold">Rs. {{ number_format($thisMonthRevenue, 2) }}</p>
                    </div>
                    <div class="p-4 bg-white shadow rounded-lg">
                        <h4 class="text-sm text-gray-500">Total Rentals Made</h4>
                        <p class="text-xl font-bold">{{ $totalRentalsMade }}</p>
                    </div>
                    <div class="p-4 bg-white shadow rounded-lg">
                        <h4 class="text-sm text-gray-500">Pending Requests</h4>
                        <p class="text-xl font-bold text-red-600">{{ $pendingRequests }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 flex items-center">
                    <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927C9.349 2.022 10.651 2.022 10.951 2.927L12.184 6.41a1 1 0 00.95.69h3.55c.969 0 1.371 1.24.588 1.81l-2.875 2.09a1 1 0 00-.364 1.118l1.1 3.396c.3.925-.755 1.688-1.54 1.118l-2.874-2.09a1 1 0 00-1.176 0l-2.875 2.09c-.785.57-1.84-.193-1.54-1.118l1.1-3.396a1 1 0 00-.364-1.118L2.728 8.91c-.783-.57-.38-1.81.588-1.81h3.55a1 1 0 00.95-.69l1.233-3.483z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Rating</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $rating }}</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        // Dark mode toggle persistence
        (function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
            const toggle = document.getElementById('darkToggle');
            toggle?.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                const isDark = document.documentElement.classList.contains('dark');
                localStorage.setItem('darkMode', isDark);
            });
        })();

        async function markReadAndRedirect(el) {
            const url = el.getAttribute('href');
            const id = el.dataset.notificationId;
            if (!id) return (window.location.href = url);
            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                await fetch("{{ route('notifications.markRead') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ id })
                });
            } catch (e) {
                console.error(e);
            } finally {
                window.location.href = url;
            }
        }
    </script>
</x-app-layout>
