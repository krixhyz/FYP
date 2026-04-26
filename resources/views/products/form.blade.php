@php
    $tempImages = old('temp_images', session('temp_product_images', []));
    if (!is_array($tempImages)) {
        $tempImages = [];
    }

    $rentalConfig = $product->rentals ?? null;
    $existingAvailableFrom = $rentalConfig?->available_from
        ? \Carbon\Carbon::parse($rentalConfig->available_from)->format('Y-m-d')
        : '';
    $existingEndDate = '';
    if ($rentalConfig?->available_from && $rentalConfig?->available_duration) {
        $existingEndDate = \Carbon\Carbon::parse($rentalConfig->available_from)
            ->addDays(max(((int) $rentalConfig->available_duration) - 1, 0))
            ->format('Y-m-d');
    }
@endphp

<div class="space-y-5">
        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-300 rounded p-4">
                <h4 class="text-red-900 font-bold mb-2">Please fix the following errors:</h4>
                <ul class="text-red-700 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Title --}}
        <div>
             <label class="field-label">Title</label>
            <input type="text" name="title"
                   value="{{ old('title', $product->title ?? '') }}"
                 class="input-field @error('title') border-red-500 @enderror" required>
            @error('title')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Description --}}
        <div>
            <label class="field-label">Description</label>
            <textarea name="description" rows="3"
                      class="input-field @error('description') border-red-500 @enderror" required>{{ old('description', $product->description ?? '') }}</textarea>
            @error('description')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Parent Category Selector --}}
        <div>
            <label class="field-label">Category (Step 1)</label>
            <div id="parentCategoryContainer" class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-3">
                <p class="text-gray-500 text-sm">Loading categories...</p>
            </div>
            @error('category_id')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Subcategory Dropdown --}}
        <div id="subcategorySection" class="hidden">
            <label class="field-label">Subcategory (Step 2)</label>
            <select id="categorySelect" name="category_id" disabled
                    class="input-field @error('category_id') border-red-500 @enderror">
                <option value="">Select Subcategory</option>
            </select>
            @error('category_id')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Condition Selector --}}
        <div>
            <label class="field-label">Condition</label>
            <div class="flex flex-wrap gap-2">
                @foreach (['NEW' => 'New/Unused', 'LIKE_NEW' => 'Like New', 'GOOD' => 'Good', 'FAIR' => 'Fair', 'WORN_FOR_PARTS' => 'Worn/For Parts'] as $value => $label)
                    <label class="inline-flex items-center px-4 py-2 border-2 rounded cursor-pointer transition-all"
                           style="border-color: #ddd; background-color: #f9f9f9;">
                        <input type="radio" name="condition" value="{{ $value }}"
                               {{ old('condition', $product->condition ?? 'GOOD') === $value ? 'checked' : '' }}
                               style="margin-right: 8px;">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @error('condition')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Eco-Points Preview --}}
        <div id="ecoPreview" class="p-4 bg-green-50 border border-green-200 rounded hidden">
            <p class="text-sm text-green-800">
                <strong>Estimated Eco-Impact:</strong>
                <span id="ecoPointsDisplay">0.00</span> points
            </p>
            <p class="text-xs text-green-700 mt-1">Adjusted based on transaction type when completed.</p>
        </div>

        {{-- Listing Type --}}
        @php
            $selectedTypes = old('listing_type', $product->type ?? ['sell']);
            if (is_string($selectedTypes)) {
                $selectedTypes = json_decode($selectedTypes, true) ?? [$selectedTypes];
            }
            if (empty($selectedTypes)) {
                $selectedTypes = ['sell'];
            }
        @endphp
        <div>
            <label class="field-label">Listing Type</label>
            <div class="flex flex-wrap gap-3">
                @foreach (['sell' => 'Sell', 'rent' => 'Rent', 'swap' => 'Swap'] as $value => $label)
                    <label class="flex items-center space-x-1">
                        <input type="checkbox" name="listing_type[]" value="{{ $value }}"
                               {{ in_array($value, $selectedTypes) ? 'checked' : '' }}
                               class="rounded text-[#0066cc] focus:ring-blue-500 @error('listing_type') border-red-500 @enderror">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @error('listing_type')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Quantity --}}
        <div>
             <label class="field-label">Quantity (Units Available)</label>
            <input type="number" name="quantity" min="1"
                   value="{{ old('quantity', $product->quantity ?? 1) }}"
                 class="input-field @error('quantity') border-red-500 @enderror" required>
            @error('quantity')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Sell Price --}}
        <div id="priceSection" class="transition-all duration-300 {{ in_array('sell', $selectedTypes) ? '' : 'hidden' }}">
             <label class="field-label">Price (For Sale)</label>
            <input type="number" name="price" min="0.01" step="0.01"
                   value="{{ old('price', $product->price ?? '') }}"
                 class="input-field @error('price') border-red-500 @enderror">
            @error('price')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Rent Section --}}
         <div id="rentSection" class="transition-all duration-300 space-y-4 border-2 border-[#bdbdbd] p-4 bg-[#f3f3f3] {{ in_array('rent', $selectedTypes) ? '' : 'hidden' }}">
             <h3 class="font-space text-sm font-bold uppercase tracking-wider text-[#888888]">Rental Details</h3>

            <div>
              <label class="field-label">Rent Deposit</label>
                <input type="number" step="0.01" name="rent_deposit"
                      value="{{ old('rent_deposit', $rentalConfig?->rent_deposit ?? '') }}"
                  class="input-field @error('rent_deposit') border-red-500 @enderror">
                @error('rent_deposit')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
              <label class="field-label">Rent Fare</label>
                <input type="number" step="0.01" name="rent_fare"
                      value="{{ old('rent_fare', $rentalConfig?->rent_fare ?? '') }}"
                  class="input-field @error('rent_fare') border-red-500 @enderror">
                @error('rent_fare')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
              <label class="field-label">Rent Type</label>
              <select name="rent_type" class="input-field @error('rent_type') border-red-500 @enderror">
                <option value="">Select Rent Type</option>
                                <option value="hourly" {{ old('rent_type', $rentalConfig?->rent_type ?? '') === 'hourly' ? 'selected' : '' }}>Hourly</option>
                                <option value="daily" {{ old('rent_type', $rentalConfig?->rent_type ?? '') === 'daily' ? 'selected' : '' }}>Daily</option>
              </select>
              @error('rent_type')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
              @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="field-label">Start Date</label>
                    <input type="date" name="available_from" id="startDate"
                              value="{{ old('available_from', $existingAvailableFrom) }}"
                      class="input-field @error('available_from') border-red-500 @enderror">
                    @error('available_from')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                  <label class="field-label">End Date</label>
                    <input type="date" name="end_date" id="endDate"
                              value="{{ old('end_date', $existingEndDate) }}"
                      class="input-field @error('end_date') border-red-500 @enderror">
                    @error('end_date')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
              <label class="field-label">Available Duration (days)</label>
                <input type="number" name="rent_duration" id="rentDuration" min="1"
                      value="{{ old('rent_duration', $product->rent_duration ?? $rentalConfig?->available_duration ?? '') }}"
                  class="input-field @error('rent_duration') border-red-500 @enderror">
                @error('rent_duration')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Product Images --}}
        <div>
            <label class="field-label">Product Images <span class="font-manrope text-[#888888] font-normal">(up to 6)</span></label>

            {{-- Existing images (edit mode) --}}
            @php use App\Helpers\ImageUrlHelper; @endphp
            @if(!empty($product->images))
                <p class="font-manrope text-xs text-[#444746] mb-2">Current images &mdash; check to remove:</p>
                <div class="flex flex-wrap gap-3 mb-3">
                    @foreach($product->images as $imgPath)
                        <label class="relative cursor-pointer group">
                            <input type="checkbox" name="remove_images[]" value="{{ $imgPath }}"
                                   class="absolute top-1 left-1 z-10 accent-red-500">
                            <img src="{{ ImageUrlHelper::getProductImageUrl($imgPath) }}"
                                 class="w-24 h-24 object-cover border-2 border-[#bdbdbd] group-hover:opacity-75 transition">
                            <span class="absolute bottom-1 right-1 bg-[#ba1a1a] text-white text-xs px-1 hidden group-hover:block">Remove</span>
                        </label>
                    @endforeach
                </div>
            @elseif(!empty($product->image))
                <div class="flex flex-wrap gap-3 mb-3">
                    <img src="{{ ImageUrlHelper::getProductImageUrl($product->image) }}" class="w-24 h-24 object-cover border-2 border-[#bdbdbd]">
                </div>
            @endif

            @if(!empty($tempImages))
                <p class="font-manrope text-xs text-[#444746] mb-2">Selected images kept from previous failed submission:</p>
                <div class="flex flex-wrap gap-3 mb-3">
                    @foreach($tempImages as $tmpPath)
                        <label class="relative cursor-pointer group">
                            <input type="hidden" name="temp_images[]" value="{{ $tmpPath }}">
                            <input type="checkbox" name="remove_temp_images[]" value="{{ $tmpPath }}"
                                   class="absolute top-1 left-1 z-10 accent-red-500">
                            <img src="{{ ImageUrlHelper::getProductImageUrl($tmpPath) }}"
                                 class="w-24 h-24 object-cover border-2 border-[#bdbdbd] group-hover:opacity-75 transition">
                            <span class="absolute bottom-1 right-1 bg-[#ba1a1a] text-white text-xs px-1 hidden group-hover:block">Remove</span>
                        </label>
                    @endforeach
                </div>
            @endif

            <input type="file" name="images[]" multiple accept="image/*"
                   id="imageUploader"
                   class="w-full text-sm file:mr-2 file:py-1 file:px-3 file:border-0 file:bg-[#006a38] file:text-white hover:file:bg-[#09864a] cursor-pointer {{ ($errors->has('images') || $errors->has('images.*')) ? 'border-red-500' : '' }}">
            @if($errors->has('images') || $errors->has('images.*'))
                <p class="text-red-600 text-xs mt-1">{{ $errors->first('images') ?: $errors->first('images.*') }}</p>
            @endif
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
    const subcategorySection = document.getElementById('subcategorySection');
    const categorySelect = document.getElementById('categorySelect');

    function syncCategorySelectState() {
        const sectionVisible = subcategorySection && !subcategorySection.classList.contains('hidden');
        if (!categorySelect) return;

        categorySelect.disabled = !sectionVisible;
        categorySelect.required = sectionVisible;
    }


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
        const rentIsChecked = rentCheckbox.checked;
        const sellIsChecked = sellCheckbox.checked;
        const swapIsChecked = swapCheckbox.checked;
        
        // Show rent section if rent is checked
        rentSection.classList.toggle('hidden', !rentIsChecked);
        
        // Show price section if sell OR swap is checked
        priceSection.classList.toggle('hidden', !(sellIsChecked || swapIsChecked));
    }
    
    rentCheckbox.addEventListener('change', updateSections);
    sellCheckbox.addEventListener('change', updateSections);
    swapCheckbox.addEventListener('change', updateSections);
    
    // Call on DOMContentLoaded AND immediately to handle initial state
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateSections);
    } else {
        updateSections();
    }

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

    // ─────── Category Selector ───────────────────────────────────────────────
    let selectedParentId = null;

    document.addEventListener('DOMContentLoaded', async function() {
        await loadParentCategories();
        
        // Restore selected values from form submission
        const oldCategoryId = "{{ old('category_id', $product->category_id ?? '') }}";
        const oldCondition = "{{ old('condition', $product->condition ?? 'GOOD') }}";
        
        if (oldCategoryId) {
            // Find parent of this category
            const allParents = await fetch('/api/categories').then(r => r.json());
            for (const parent of allParents) {
                const subs = await fetch(`/api/categories/${parent.id}/subcategories`).then(r => r.json());
                if (subs.some(s => s.id === parseInt(oldCategoryId))) {
                    selectedParentId = parent.id;
                    await selectParent(parent.id, parent.name);
                    document.getElementById('categorySelect').value = oldCategoryId;
                    syncCategorySelectState();
                    updateEcoPreview();
                    break;
                }
            }
        }

        syncCategorySelectState();
        
        if (oldCondition) {
            document.querySelector(`input[name="condition"][value="${oldCondition}"]`)?.click();
            updateEcoPreview();
        }
    });

    async function loadParentCategories() {
        try {
            const response = await fetch('/api/categories');
            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }
            
            const parents = await response.json();
            const container = document.getElementById('parentCategoryContainer');
            
            if (!parents || parents.length === 0) {
                container.innerHTML = '<p class="text-red-600 text-sm col-span-3">No categories available. Please contact support.</p>';
                return;
            }
            
            container.innerHTML = '';
            
            parents.forEach(parent => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = parent.name;
                btn.className = 'px-4 py-2 border-2 rounded font-medium transition-all cursor-pointer';
                btn.style.borderColor = '#ddd';
                btn.style.backgroundColor = '#f9f9f9';
                
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    selectParent(parent.id, parent.name);
                });
                container.appendChild(btn);
            });
        } catch (error) {
            console.error('Error loading categories:', error);
            const container = document.getElementById('parentCategoryContainer');
            container.innerHTML = '<p class="text-red-600 text-sm col-span-3">Error loading categories. Check browser console.</p>';
        }
    }

    async function selectParent(parentId, parentName) {
        selectedParentId = parentId;
        
        // Update button styles
        document.querySelectorAll('#parentCategoryContainer button').forEach(btn => {
            if (btn.textContent === parentName) {
                btn.style.borderColor = '#006a38';
                btn.style.backgroundColor = '#e8f5e9';
            } else {
                btn.style.borderColor = '#ddd';
                btn.style.backgroundColor = '#f9f9f9';
            }
        });
        
        // Load subcategories
        try {
            const response = await fetch(`/api/categories/${parentId}/subcategories`);
            const subcategories = await response.json();
            
            const select = document.getElementById('categorySelect');
            select.innerHTML = '<option value="">Select Subcategory</option>';
            
            subcategories.forEach(sub => {
                const option = document.createElement('option');
                option.value = sub.id;
                option.textContent = sub.name;
                select.appendChild(option);
            });
            
            document.getElementById('subcategorySection').classList.remove('hidden');
            syncCategorySelectState();
            select.onchange = updateEcoPreview;
        } catch (error) {
            console.error('Error loading subcategories:', error);
        }
    }

    function updateEcoPreview() {
        const categoryId = document.getElementById('categorySelect')?.value;
        const condition = document.querySelector('input[name="condition"]:checked')?.value || 'GOOD';
        
        if (!categoryId) {
            document.getElementById('ecoPreview').classList.add('hidden');
            return;
        }
        
        const conditionMultipliers = {'NEW': 1.00, 'LIKE_NEW': 0.95, 'GOOD': 0.85, 'FAIR': 0.70, 'WORN_FOR_PARTS': 0.50};
        const multiplier = conditionMultipliers[condition] || 0.85;
        
        fetch(`/api/categories/${categoryId}`)
            .then(r => r.json())
            .then(cat => {
                const preview = (cat.eco_points * multiplier).toFixed(2);
                document.getElementById('ecoPointsDisplay').textContent = preview;
                document.getElementById('ecoPreview').classList.remove('hidden');
            })
            .catch(err => console.error('Error:', err));
    }

    document.addEventListener('change', function(e) {
        if (e.target.matches('input[name="condition"]')) {
            updateEcoPreview();
        } else if (e.target.matches('#categorySelect')) {
            updateEcoPreview();
        }
    });
</script>
