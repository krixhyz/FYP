<div class="bg-white shadow-lg rounded-2xl p-6 border border-gray-100">
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @if ($method === 'PUT')
            @method('PUT')
        @endif

        {{-- Title --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Title</label>
            <input type="text" name="title"
                   value="{{ old('title', $product->title ?? '') }}"
                   class="w-full border-gray-300 rounded-lg text-sm p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3"
                      class="w-full border-gray-300 rounded-lg text-sm p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>{{ old('description', $product->description ?? '') }}</textarea>
        </div>

        {{-- Category --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Category</label>
            <select name="category" required
                    class="w-full border-gray-300 rounded-lg text-sm p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Select Category</option>
                @foreach(['electronics', 'clothing', 'furniture', 'general'] as $cat)
                    <option value="{{ $cat }}" {{ old('category', $product->category ?? '') == $cat ? 'selected' : '' }}>
                        {{ ucfirst($cat) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Listing Type --}}
        @php
            $selectedTypes = old('listing_type', $product->type ?? []);
            if (is_string($selectedTypes)) {
                $selectedTypes = json_decode($selectedTypes, true) ?? [$selectedTypes];
            }
        @endphp
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Listing Type</label>
            <div class="flex flex-wrap gap-3">
                @foreach (['sell' => 'Sell', 'rent' => 'Rent', 'swap' => 'Swap'] as $value => $label)
                    <label class="flex items-center space-x-1">
                        <input type="checkbox" name="listing_type[]" value="{{ $value }}"
                               {{ in_array($value, $selectedTypes) ? 'checked' : '' }}
                               class="rounded text-blue-600 focus:ring-blue-500">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Quantity --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Quantity (Units Available)</label>
            <input type="number" name="quantity" min="1"
                   value="{{ old('quantity', $product->quantity ?? 1) }}"
                   class="w-full border-gray-300 rounded-lg text-sm p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
        </div>

        {{-- Sell Price --}}
        <div id="priceSection" class="transition-all duration-300 {{ in_array('sell', $selectedTypes) ? '' : 'hidden' }}">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Price (For Sale)</label>
            <input type="number" name="price" step="0.01"
                   value="{{ old('price', $product->price ?? '') }}"
                   class="w-full border-gray-300 rounded-lg text-sm p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>

        {{-- Rent Section --}}
        <div id="rentSection" class="transition-all duration-300 space-y-4 border p-4 rounded-xl bg-gray-50 {{ in_array('rent', $selectedTypes) ? '' : 'hidden' }}">
            <h3 class="text-sm font-semibold text-gray-800">Rental Details</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700">Rent Deposit</label>
                <input type="number" step="0.01" name="rent_deposit"
                       value="{{ old('rent_deposit', $product->rentals->rent_deposit ?? '') }}"
                       class="w-full border-gray-300 rounded-lg text-sm p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Rent Fare</label>
                <input type="number" step="0.01" name="rent_fare"
                       value="{{ old('rent_fare', $product->rentals->rent_fare ?? '') }}"
                       class="w-full border-gray-300 rounded-lg text-sm p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="available_from" id="startDate"
                           value="{{ old('start_date', $product->rentals->start_date ?? '') }}"
                           class="w-full border-gray-300 rounded-lg text-sm p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" id="endDate"
                           value="{{ old('end_date', $product->rentals->end_date ?? '') }}"
                           class="w-full border-gray-300 rounded-lg text-sm p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Available Duration (days)</label>
                <input type="number" name="rent_duration" id="rentDuration"
                       value="{{ old('rent_duration', $product->rentals->available_duration ?? '') }}"
                       class="w-full border-gray-300 rounded-lg text-sm p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" readonly>
            </div>
        </div>

        {{-- Product Images --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Product Images <span class="text-gray-400 font-normal">(up to 6)</span></label>

            {{-- Existing images (edit mode) --}}
            @if(!empty($product->images))
                <p class="text-xs text-gray-500 mb-2">Current images &mdash; check to remove:</p>
                <div class="flex flex-wrap gap-3 mb-3">
                    @foreach($product->images as $imgPath)
                        <label class="relative cursor-pointer group">
                            <input type="checkbox" name="remove_images[]" value="{{ $imgPath }}"
                                   class="absolute top-1 left-1 z-10 accent-red-500">
                            <img src="{{ asset('storage/'.$imgPath) }}"
                                 class="w-24 h-24 object-cover rounded-lg border border-gray-200 group-hover:opacity-75 transition">
                            <span class="absolute bottom-1 right-1 bg-red-500 text-white text-xs px-1 rounded hidden group-hover:block">Remove</span>
                        </label>
                    @endforeach
                </div>
            @elseif(!empty($product->image))
                <div class="flex flex-wrap gap-3 mb-3">
                    <img src="{{ asset('storage/'.$product->image) }}" class="w-24 h-24 object-cover rounded-lg border border-gray-200">
                </div>
            @endif

            <input type="file" name="images[]" multiple accept="image/*"
                   id="imageUploader"
                   class="w-full text-sm file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
            <p class="text-xs text-gray-400 mt-1">JPEG, PNG, GIF or WebP &bull; max 4 MB each &bull; up to 6 images</p>

            {{-- New image previews --}}
            <div id="newImagePreviews" class="flex flex-wrap gap-3 mt-3"></div>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg transition-colors">
            {{ $buttonText }}
        </button>
    </form>
</div>

<script>

    document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', () => {
        if (parseFloat(input.value) < 0) {
            input.value = '';
        }
    });
});

    const rentCheckbox = document.querySelector('input[value="rent"]');
    const sellCheckbox = document.querySelector('input[value="sell"]');
    const rentSection = document.getElementById('rentSection');
    const priceSection = document.getElementById('priceSection');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const rentDuration = document.getElementById('rentDuration');
    const swapCheckbox = document.querySelector('input[value="swap"]');


    // ── Image accumulator ─────────────────────────────────────────────────────
    const imageUploader = document.getElementById('imageUploader');
    const newImagePreviews = document.getElementById('newImagePreviews');
    const MAX_IMAGES = 6;

    // DataTransfer accumulates files across multiple open-dialog calls
    let dt = new DataTransfer();

    imageUploader?.addEventListener('change', function () {
        const incoming = Array.from(this.files);

        incoming.forEach(file => {
            // Skip duplicates (same name + size)
            const isDuplicate = Array.from(dt.files).some(
                f => f.name === file.name && f.size === file.size
            );
            if (!isDuplicate && dt.files.length < MAX_IMAGES) {
                dt.items.add(file);
            }
        });

        // Push accumulated list back to the input
        this.files = dt.files;

        renderPreviews();
    });

    function renderPreviews() {
        newImagePreviews.innerHTML = '';

        Array.from(dt.files).forEach((file, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'relative group';

            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-24 h-24 object-cover rounded-lg border border-blue-200';
                wrapper.appendChild(img);

                // Remove button
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'absolute top-0 right-0 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition';
                btn.innerHTML = '×';
                btn.addEventListener('click', () => removeFile(index));
                wrapper.appendChild(btn);

                newImagePreviews.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });
    }

    function removeFile(index) {
        const newDt = new DataTransfer();
        Array.from(dt.files).forEach((file, i) => {
            if (i !== index) newDt.items.add(file);
        });
        dt = newDt;
        imageUploader.files = dt.files;
        renderPreviews();
    }

    // Set min dates to today
    const today = new Date().toISOString().split('T')[0];
    startDate.setAttribute('min', today);
    endDate.setAttribute('min', today);

    // Show/hide sections based on checkboxes
    function updateSections() {
        rentSection.classList.toggle('hidden', !rentCheckbox.checked);
        priceSection.classList.toggle('hidden', !(sellCheckbox.checked || swapCheckbox.checked));

    }
    rentCheckbox.addEventListener('change', updateSections);
    sellCheckbox.addEventListener('change', updateSections);
    swapCheckbox.addEventListener('change', updateSections);
    document.addEventListener('DOMContentLoaded', updateSections);

    // Duration auto-calc
    function updateDuration() {
        if (!startDate.value || !endDate.value) return;
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        if (end < start) endDate.value = startDate.value;
        const diff = (end - start) / (1000 * 60 * 60 * 24) + 1;
        rentDuration.value = Math.max(1, Math.floor(diff));
    }
    startDate?.addEventListener('change', updateDuration);
    endDate?.addEventListener('change', updateDuration);

    // Run once on load for edit
    updateDuration();
</script>
