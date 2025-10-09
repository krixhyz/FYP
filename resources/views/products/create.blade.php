<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Add New Listing</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto mt-6">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            {{-- Title --}}
            <div>
                <label class="block font-medium">Title</label>
                <input type="text" name="title" class="w-full border-gray-300 rounded-md" required>
            </div>

            {{-- Description --}}
            <div>
                <label class="block font-medium">Description</label>
                <textarea name="description" class="w-full border-gray-300 rounded-md" required></textarea>
            </div>

            {{-- Price (for selling only) --}}
            <div>
                <label class="block font-medium">Price (For Sell)</label>
                <input type="number" name="price" class="w-full border-gray-300 rounded-md">
            </div>

            {{-- Listing Type --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Listing Type:</label>
                <div class="flex items-center space-x-4">
                    <label><input type="checkbox" name="listing_type[]" value="sell"> Sell</label>
                    <label><input type="checkbox" name="listing_type[]" value="rent" id="rentCheckbox"> Rent</label>
                    <label><input type="checkbox" name="listing_type[]" value="swap"> Swap</label>
                </div>
            </div>

            {{-- Rent Section (shown only if Rent is checked) --}}
            <div id="rentSection" class="hidden space-y-4 border p-4 rounded-md bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Rental Details</h3>

                <div>
                    <label class="block font-medium">Rent Deposit</label>
                    <input type="number" step="0.01" name="rent_deposit" id="rentDeposit"
                        class="w-full border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block font-medium">Rent Fare</label>
                    <input type="number" step="0.01" name="rent_fare" id="rentFare"
                        class="w-full border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block font-medium">Rent Type</label>
                    <select name="rent_type" id="rentType" class="w-full border-gray-300 rounded-md">
                        <option value="">Select Type</option>
                        <option value="hourly">Hourly</option>
                        <option value="daily">Daily</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium">Available Duration (hours or days)</label>
                    <input type="number" name="rent_duration" id="rentDuration"
                        class="w-full border-gray-300 rounded-md">
                </div>
            </div>

            {{-- Image --}}
            <div>
                <label class="block font-medium">Image</label>
                <input type="file" name="image" class="w-full">
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md">Add Listing</button>
        </form>
    </div>

    {{-- JS Section --}}
    <script>
        const rentCheckbox = document.getElementById('rentCheckbox');
        const rentSection = document.getElementById('rentSection');

        rentCheckbox.addEventListener('change', function() {
            rentSection.classList.toggle('hidden', !this.checked);
        });
    </script>
</x-app-layout>
