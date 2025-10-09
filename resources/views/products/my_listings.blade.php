<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Listings') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">Image</th>
                        <th class="px-6 py-3">Title</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-6 py-4">
                                <img src="{{ asset('storage/' . $product->image) }}" alt="Image" class="w-16 h-16 object-cover rounded">
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $product->title }}</td>
                            <td class="px-6 py-4">Rs. {{ $product->price }}</td>
                            <td class="px-6 py-4">
                                <form action="{{ route('products.updateStatus', $product->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="available" {{ $product->status == 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="sold" {{ $product->status == 'sold' ? 'selected' : '' }}>Sold</option>
                                        <option value="rented" {{ $product->status == 'rented' ? 'selected' : '' }}>Rented</option>
                                        <option value="swapped" {{ $product->status == 'swapped' ? 'selected' : '' }}>Swapped</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 hover:text-red-800 text-sm font-semibold">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
