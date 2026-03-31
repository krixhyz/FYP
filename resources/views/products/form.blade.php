<div class="space-y-5">
        {{-- Title --}}
        <div>
             <label class="field-label">Title</label>
            <input type="text" name="title"
                   value="{{ old('title', $product->title ?? '') }}"
                 class="input-field" required>
        </div>

        {{-- Description --}}
        <div>
            <label class="field-label">Description</label>
            <textarea name="description" rows="3"
                      class="input-field" required>{{ old('description', $product->description ?? '') }}</textarea>
        </div>

        {{-- Category --}}
        <div>
            <label class="field-label">Category</label>
            <select name="category" required
                    class="input-field">
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
            <label class="field-label">Listing Type</label>
            <div class="flex flex-wrap gap-3">
                @foreach (['sell' => 'Sell', 'rent' => 'Rent', 'swap' => 'Swap'] as $value => $label)
                    <label class="flex items-center space-x-1">
                        <input type="checkbox" name="listing_type[]" value="{{ $value }}"
                               {{ in_array($value, $selectedTypes) ? 'checked' : '' }}
                               class="rounded text-[#0066cc] focus:ring-blue-500">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Quantity --}}
        <div>
             <label class="field-label">Quantity (Units Available)</label>
            <input type="number" name="quantity" min="1"
                   value="{{ old('quantity', $product->quantity ?? 1) }}"
                 class="input-field" required>
        </div>

        {{-- Sell Price --}}
        <div id="priceSection" class="transition-all duration-300 {{ in_array('sell', $selectedTypes) ? '' : 'hidden' }}">
             <label class="field-label">Price (For Sale)</label>
            <input type="number" name="price" step="0.01"
                   value="{{ old('price', $product->price ?? '') }}"
                 class="input-field">
        </div>

        {{-- Rent Section --}}
         <div id="rentSection" class="transition-all duration-300 space-y-4 border-2 border-[#bdbdbd] p-4 bg-[#f3f3f3] {{ in_array('rent', $selectedTypes) ? '' : 'hidden' }}">
             <h3 class="font-space text-sm font-bold uppercase tracking-wider text-[#888888]">Rental Details</h3>

            <div>
              <label class="field-label">Rent Deposit</label>
                <input type="number" step="0.01" name="rent_deposit"
                       value="{{ old('rent_deposit', $product->rentals->rent_deposit ?? '') }}"
                  class="input-field">
            </div>

            <div>
              <label class="field-label">Rent Fare</label>
                <input type="number" step="0.01" name="rent_fare"
                       value="{{ old('rent_fare', $product->rentals->rent_fare ?? '') }}"
                  class="input-field">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="field-label">Start Date</label>
                    <input type="date" name="available_from" id="startDate"
                           value="{{ old('start_date', $product->rentals->start_date ?? '') }}"
                      class="input-field">
                </div>

                <div>
                  <label class="field-label">End Date</label>
                    <input type="date" name="end_date" id="endDate"
                           value="{{ old('end_date', $product->rentals->end_date ?? '') }}"
                      class="input-field">
                </div>
            </div>

            <div>
              <label class="field-label">Available Duration (days)</label>
                <input type="number" name="rent_duration" id="rentDuration"
                       value="{{ old('rent_duration', $product->rentals->available_duration ?? '') }}"
                  class="input-field" readonly>
            </div>
        </div>

        {{-- Product Images --}}
        <div>
            <label class="field-label">Product Images <span class="font-manrope text-[#888888] font-normal">(up to 6)</span></label>

            {{-- Existing images (edit mode) --}}
            @if(!empty($product->images))
                <p class="font-manrope text-xs text-[#444746] mb-2">Current images &mdash; check to remove:</p>
                <div class="flex flex-wrap gap-3 mb-3">
                    @foreach($product->images as $imgPath)
                        <label class="relative cursor-pointer group">
                            <input type="checkbox" name="remove_images[]" value="{{ $imgPath }}"
                                   class="absolute top-1 left-1 z-10 accent-red-500">
                            <img src="{{ asset('storage/'.$imgPath) }}"
                                 class="w-24 h-24 object-cover border-2 border-[#bdbdbd] group-hover:opacity-75 transition">
                            <span class="absolute bottom-1 right-1 bg-[#ba1a1a] text-white text-xs px-1 hidden group-hover:block">Remove</span>
                        </label>
                    @endforeach
                </div>
            @elseif(!empty($product->image))
                <div class="flex flex-wrap gap-3 mb-3">
                    <img src="{{ asset('storage/'.$product->image) }}" class="w-24 h-24 object-cover border-2 border-[#bdbdbd]">
                </div>
            @endif

            <input type="file" name="images[]" multiple accept="image/*"
                   id="imageUploader"
                   class="w-full text-sm file:mr-2 file:py-1 file:px-3 file:border-0 file:bg-[#006a38] file:text-white hover:file:bg-[#09864a] cursor-pointer">
            <p class="font-manrope text-xs text-[#888888] mt-1">JPEG, PNG, GIF or WebP • max 4 MB each • up to 6 images</p>

            {{-- New image previews --}}
            <div id="newImagePreviews" class="flex flex-wrap gap-3 mt-3"></div>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="btn-pill btn-pill-dark w-full justify-center">
            {{ $buttonText }}
        </button>
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
                img.className = 'w-24 h-24 object-cover border-2 border-[var(--reloop-border)]';
                wrapper.appendChild(img);

                // Remove button
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'absolute top-0 right-0 bg-[#ba1a1a] text-white w-5 h-5 text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition';
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
