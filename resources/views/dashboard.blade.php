<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>

                <a href="{{ route('products.create') }}" class="bg-green-600 text-white px-4 py-2 rounded">+ Add Listing</a>
<a href="{{ route('products.myListings') }}" class="bg-gray-600 text-white px-4 py-2 rounded">My Listings</a>
<a href="{{ route('products.myPurchases') }}" class="bg-gray-600 text-white px-4 py-2 rounded">My Purchases</a>
            </div>
        </div>
    </div>

    
    @if(auth()->user()->notifications->count())
    <div class="bg-white p-4 rounded shadow mt-4">
        <h3 class="font-semibold mb-2">Rental Notifications</h3>
        @foreach(auth()->user()->unreadNotifications as $notification)
            <div class="border-b border-gray-200 py-2">
                <p class="text-sm">{{ $notification->data['message'] }}</p>
                <a href="{{ route('rental.review', $notification->data['rental_request_id']) }}"
                   class="text-blue-600 text-xs">View Request</a>
            </div>
        @endforeach
    </div>
@else
    <p class="text-gray-500 text-sm mt-4">No new notifications.</p>
@endif



</x-app-layout>

