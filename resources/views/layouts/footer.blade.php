@php
    $footerUser = auth()->user();
    $footerIsAdminUser = $footerUser && ($footerUser->isAdmin() || $footerUser->isSuperAdmin());
@endphp

<footer class="mt-16 border-t border-[rgba(189,202,189,0.35)] bg-[#e9ecef] text-[#1a1c1c]">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
        <div class="grid gap-10 lg:grid-cols-6">
            <div>
                <h3 class="font-space text-sm font-bold uppercase tracking-[0.12em] text-[#1a1c1c]">Quick Links</h3>
                <ul class="mt-4 space-y-3 text-sm text-[#2f3234]">
                    <li><a href="{{ route('landing') }}" class="transition-colors hover:text-[#006a38]">Home</a></li>
                    <li><a href="{{ route('products.index') }}" class="transition-colors hover:text-[#006a38]">Marketplace</a></li>
                    <li><a href="{{ route('cart.index') }}" class="transition-colors hover:text-[#006a38]">Cart</a></li>
                    <li><a href="{{ route('wishlist.index') }}" class="transition-colors hover:text-[#006a38]">Wishlist</a></li>
                    <li><a href="{{ $footerIsAdminUser ? route('admin.dashboard') : route('dashboard') }}" class="transition-colors hover:text-[#006a38]">Dashboard</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-space text-sm font-bold uppercase tracking-[0.12em] text-[#1a1c1c]">Explore</h3>
                <ul class="mt-4 space-y-3 text-sm text-[#2f3234]">
                    @auth
                        <li><a href="{{ route('products.create') }}" class="transition-colors hover:text-[#006a38]">Create Listing</a></li>
                        <li><a href="{{ route('products.myListings') }}" class="transition-colors hover:text-[#006a38]">My Listings</a></li>
                        <li><a href="{{ route('products.myPurchases') }}" class="transition-colors hover:text-[#006a38]">My Orders</a></li>
                        <li><a href="{{ route('rental.myRentals') }}" class="transition-colors hover:text-[#006a38]">My Rentals</a></li>
                        <li><a href="{{ route('swap.mySwaps') }}" class="transition-colors hover:text-[#006a38]">My Swaps</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="transition-colors hover:text-[#006a38]">Log In</a></li>
                        <li><a href="{{ route('register') }}" class="transition-colors hover:text-[#006a38]">Create Account</a></li>
                        <li><a href="{{ route('products.index') }}" class="transition-colors hover:text-[#006a38]">Browse Listings</a></li>
                    @endauth
                </ul>
            </div>

            <div>
                <h3 class="font-space text-sm font-bold uppercase tracking-[0.12em] text-[#1a1c1c]">Categories</h3>
                <ul class="mt-4 space-y-3 text-sm text-[#2f3234]">
                    <li><a href="{{ route('products.index', ['category' => 'Electronics']) }}" class="transition-colors hover:text-[#006a38]">Electronics</a></li>
                    <li><a href="{{ route('products.index', ['category' => 'Books']) }}" class="transition-colors hover:text-[#006a38]">Books</a></li>
                    <li><a href="{{ route('products.index', ['category' => 'Clothing']) }}" class="transition-colors hover:text-[#006a38]">Clothing</a></li>
                    <li><a href="{{ route('products.index', ['mode' => 'buy']) }}" class="transition-colors hover:text-[#006a38]">Buy</a></li>
                    <li><a href="{{ route('products.index', ['mode' => 'rent']) }}" class="transition-colors hover:text-[#006a38]">Rent</a></li>
                    <li><a href="{{ route('products.index', ['mode' => 'swap']) }}" class="transition-colors hover:text-[#006a38]">Swap</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-space text-sm font-bold uppercase tracking-[0.12em] text-[#1a1c1c]">Legal</h3>
                <ul class="mt-4 space-y-3 text-sm text-[#2f3234]">
                    <li><a href="{{ $footerIsAdminUser ? route('admin.profile.edit') : route('profile.edit') }}" class="transition-colors hover:text-[#006a38]">Account Settings</a></li>
                    <li><a href="{{ route('dispute.my') }}" class="transition-colors hover:text-[#006a38]">My Disputes</a></li>
                    <li><a href="{{ route('notifications.index') }}" class="transition-colors hover:text-[#006a38]">Notifications</a></li>
                    <li><a href="{{ route('review.create') }}" class="transition-colors hover:text-[#006a38]">Submit Review</a></li>
                    <li><a href="{{ route('dispute.create') }}" class="transition-colors hover:text-[#006a38]">Report Issue</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-space text-sm font-bold uppercase tracking-[0.12em] text-[#1a1c1c]">Contact</h3>
                <div class="mt-4 space-y-3 text-sm leading-relaxed text-[#2f3234]">
                    <p>Reloop Marketplace</p>
                    <p>Lalitpur, Bagmati, Nepal</p>
                    <p>Postal Code 44700</p>
                    <p><a href="mailto:info@reloop.com" class="transition-colors hover:text-[#006a38]">info@reloop.com</a></p>
                    <p><a href="tel:+97715454338" class="transition-colors hover:text-[#006a38]">+977 1 5454338</a></p>
                </div>

                <div class="mt-6">
                    <p class="font-space text-sm font-bold uppercase tracking-[0.12em] text-[#1a1c1c]">Find Us On</p>
                    <div class="mt-3 flex items-center gap-3 text-[#1a1c1c]">
                        <a href="#" aria-label="Instagram" class="rounded-md p-1.5 transition-colors hover:bg-[#dbe4dc] hover:text-[#006a38]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm8.5 1.5h-8.5A4.25 4.25 0 0 0 3.5 7.75v8.5a4.25 4.25 0 0 0 4.25 4.25h8.5a4.25 4.25 0 0 0 4.25-4.25v-8.5a4.25 4.25 0 0 0-4.25-4.25ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm5.38-2.38a1.12 1.12 0 1 1 0 2.24 1.12 1.12 0 0 1 0-2.24Z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="Facebook" class="rounded-md p-1.5 transition-colors hover:bg-[#dbe4dc] hover:text-[#006a38]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M13.5 21v-8h2.7l.4-3h-3.1V8.2c0-.9.3-1.5 1.6-1.5h1.7V4a22 22 0 0 0-2.5-.1c-2.5 0-4.3 1.5-4.3 4.4V10H7v3h2.9v8h3.6Z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="LinkedIn" class="rounded-md p-1.5 transition-colors hover:bg-[#dbe4dc] hover:text-[#006a38]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M6.2 8.8A2.1 2.1 0 1 1 6.2 4.6a2.1 2.1 0 0 1 0 4.2ZM4.4 20h3.5V10.2H4.4V20Zm6.3 0h3.5v-5.1c0-1.3.2-2.5 1.8-2.5s1.6 1.5 1.6 2.6V20H21v-5.7c0-2.8-.6-4.9-3.8-4.9-1.5 0-2.4.8-2.9 1.6h-.1v-1.4h-3.4V20Z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="X" class="rounded-md p-1.5 transition-colors hover:bg-[#dbe4dc] hover:text-[#006a38]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M18.9 3h2.8l-6.1 7 7.2 11h-5.7l-4.5-7L6.8 21H4l6.5-7.4L3.6 3h5.8l4 6.4L18.9 3Zm-1 16.3h1.5L8.5 4.6H6.8l11.1 14.7Z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="lg:pl-6">
                <div class="border border-[rgba(171,184,174,0.55)] bg-white px-5 py-4">
                    <h3 class="font-space text-xl font-bold tracking-wide text-[#1a1c1c]">Reloop</h3>
                    <p class="mt-2 text-sm leading-relaxed text-[#2f3234]">Trusted platform for circular commerce with secure buy, rent, and swap workflows.</p>
                </div>
                <div class="mt-5 space-y-3 text-sm leading-relaxed text-[#2f3234]">
                    <p>An inclusive marketplace for buying, renting, and swapping across the community.</p>
                    <p>Verified listings with transparent communication and payment trails.</p>
                    <p>Support Hours: Sun-Fri, 10:00 AM - 6:00 PM</p>
                </div>
            </div>
        </div>

        <div class="mt-12 flex flex-col items-start justify-between gap-3 border-t border-[rgba(189,202,189,0.45)] pt-6 text-sm text-[#2f3234] md:flex-row md:items-center">
            <p>© {{ date('Y') }} reloop.com.np | All Rights Reserved.</p>
            <p>Designed for reliable circular trade for everyone.</p>
        </div>
    </div>
</footer>
