@extends('layouts.app')

@section('content')
@php
    $toImageUrl = function ($value) {
        if (empty($value)) {
            return null;
        }

        if (is_object($value)) {
            $value = data_get($value, 'image_url') ?? data_get($value, 'url') ?? data_get($value, 'path');
        }

        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, 'data:image') || str_starts_with($value, '/')) {
            return $value;
        }

        return asset('storage/' . ltrim($value, '/'));
    };

    $resolveProductImage = function ($item) use ($toImageUrl) {
        if (!$item) {
            return null;
        }

        $images = $item->images ?? null;
        if (is_array($images) && !empty($images)) {
            $first = $images[0] ?? null;
            $firstUrl = $toImageUrl($first);
            if ($firstUrl) {
                return $firstUrl;
            }
        }

        return $toImageUrl($item->image_url ?? null) ?? $toImageUrl($item->image ?? null);
    };

    $targetImageUrl = $resolveProductImage($product);
@endphp

<div class="max-w-5xl mx-auto px-3 md:px-5 py-3 space-y-3">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-[#006a38] to-[#09864a] px-5 md:px-6 py-6 text-white">
        <p class="font-space text-xs font-bold uppercase tracking-widest text-green-100 mb-2">Swap Workflow</p>
        <h1 class="font-space font-bold text-2xl md:text-3xl mb-1">Propose a Swap</h1>
        <p class="font-manrope text-sm text-green-50 max-w-2xl">Trade your item for something you want. Fill in details and send your proposal.</p>
    </section>

    <!-- Main Content -->
    <div class="space-y-3">
        <!-- Error Messages -->
        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 space-y-2">
                <h6 class="font-space font-bold text-red-700 text-sm">Please fix the following issues:</h6>
                <ul class="space-y-1 ml-4">
                    @foreach($errors->all() as $error)
                        <li class="font-manrope text-sm text-red-600">• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Swap Comparison Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-start">
            <!-- What You Want -->
            <div class="bg-white border border-gray-200 shadow-sm hover:shadow-md transition-shadow h-full">
                <div class="bg-gray-50 px-5 py-3 border-b">
                    <p class="font-space text-xs uppercase tracking-widest text-gray-600 mb-1">You want</p>
                    <h3 class="font-space font-bold text-lg text-[#006a38]">{{ $product->title }}</h3>
                </div>
                
                <div class="p-4 space-y-3">
                    <!-- Product Image -->
                    <div class="bg-gray-100 rounded h-56 md:h-64 flex items-center justify-center overflow-hidden">
                        @if($targetImageUrl)
                            <img src="{{ $targetImageUrl }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="text-gray-400">No image</div>
                        @endif
                    </div>

                    <!-- Offered Product Selector (moved to left for balance) -->
                    <div class="border border-gray-200 bg-[#f8faf8] p-3 space-y-3">
                        <div>
                            <p class="font-space text-[10px] uppercase tracking-widest text-gray-500 mb-1">Your Offer</p>
                            <h4 class="font-space font-bold text-sm text-[#1a1c1c]">Select your product</h4>
                        </div>

                        @if($userProducts->isEmpty())
                            <div class="text-center py-4">
                                <p class="font-manrope text-sm text-gray-600 mb-3">You don't have any products available to trade.</p>
                                <a href="{{ route('products.myListings') }}" class="inline-block bg-[#006a38] text-white px-5 py-2 font-space font-bold text-xs uppercase tracking-wider hover:bg-[#00522c]">
                                    Add a Product
                                </a>
                            </div>
                        @else
                            <div>
                                <select name="offered_product_id" id="offered_product_id" form="swap-request-form" class="w-full border border-gray-300 focus:border-[#006a38] focus:ring focus:ring-green-200 outline-none px-4 py-2 font-manrope text-sm">
                                    <option value="">Choose a product...</option>
                                    @foreach($userProducts as $p)
                                        @php
                                            $imageUrl = $resolveProductImage($p);
                                            $conditionText = $p->condition ?? 'Good Condition';
                                        @endphp
                                        <option value="{{ $p->id }}"
                                                data-price="{{ $p->price }}"
                                                data-name="{{ $p->title }}"
                                                data-image="{{ $imageUrl ?? '' }}"
                                                data-condition="{{ $conditionText }}">
                                            {{ $p->title }} (Rs. {{ number_format($p->price, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="product-preview" class="p-3 bg-blue-50 border border-blue-200 rounded hidden space-y-2">
                                <div class="flex gap-3">
                                    <div class="w-16 h-16 rounded bg-white overflow-hidden flex items-center justify-center">
                                        <img id="preview-image" src="" alt="" class="w-16 h-16 object-cover hidden">
                                        <span id="preview-no-image" class="text-[10px] text-gray-500">No image</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-manrope font-bold text-xs text-gray-900" id="preview-name"></p>
                                        <p class="font-manrope text-[11px] text-gray-600 mt-1" id="preview-condition"></p>
                                        <p class="font-manrope font-semibold text-blue-600 text-xs mt-1">Rs. <span id="preview-price"></span></p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Product Details -->
                    <div class="space-y-2">
                        <p class="font-manrope text-sm text-gray-600 line-clamp-2">{{ $product->description }}</p>
                        
                        <div class="flex items-center gap-2">
                            <span class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded text-xs font-semibold">
                                Rs. {{ number_format($product->price, 2) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <p class="text-gray-500 text-xs mb-1">Condition</p>
                                <p class="font-manrope font-medium">{{ $product->condition ?? 'Good Condition' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs mb-1">Owner</p>
                                <a href="{{ route('users.show', $product->user_id) }}" class="font-manrope font-medium text-[#006a38] hover:underline">{{ $product->user->name }}</a>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2">
                            <div class="border border-gray-200 bg-gray-50 p-3">
                                <p class="font-space text-[10px] uppercase tracking-widest text-gray-500 mb-1">Listing Snapshot</p>
                                <div class="space-y-1">
                                    <p class="font-manrope text-xs text-gray-700"><span class="text-gray-500">Category:</span> {{ $product->category->name ?? 'General' }}</p>
                                    <p class="font-manrope text-xs text-gray-700"><span class="text-gray-500">Posted:</span> {{ optional($product->created_at)->format('d M Y') ?? 'N/A' }}</p>
                                    <p class="font-manrope text-xs text-gray-700"><span class="text-gray-500">Quantity:</span> {{ $product->quantity ?? 1 }}</p>
                                </div>
                            </div>
                            <div class="border border-gray-200 bg-gray-50 p-3">
                                <p class="font-space text-[10px] uppercase tracking-widest text-gray-500 mb-1">Trade Guidance</p>
                                <ul class="space-y-1 text-xs text-gray-700 font-manrope">
                                    <li>Choose your offered item first.</li>
                                    <li>Cash direction locks by value gap.</li>
                                    <li>Add a clear message for faster approval.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- What You Offer -->
            <form id="swap-request-form" action="{{ route('swap.request.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <!-- Cash Adjustment -->
                <div class="bg-white border border-gray-200 shadow-sm">
                    <div class="bg-gray-50 px-5 py-3 border-b">
                        <p class="font-space text-xs uppercase tracking-widest text-gray-600 mb-1">Optional</p>
                        <h3 class="font-space font-bold text-lg text-gray-900">Cash Adjustment</h3>
                    </div>
                    
                    <div class="p-4 space-y-3">
                        <p class="font-manrope text-sm text-gray-600">If the items have different values, you can adjust with cash to make the trade fair.</p>
                        
                        <div class="space-y-3">
                            <label class="flex items-center p-3 border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                                <input type="radio" id="money_direction_none" name="money_direction" value="none" checked onchange="toggleCashInput()" class="w-4 h-4 text-[#006a38] cursor-pointer">
                                <span class="ml-3 font-manrope font-medium text-gray-900">No cash involved</span>
                            </label>
                            
                            <label class="flex items-center p-3 border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                                <input type="radio" id="money_direction_requester_offers_cash" name="money_direction" value="requester_offers_cash" onchange="toggleCashInput()" class="w-4 h-4 text-[#006a38] cursor-pointer">
                                <span class="ml-3 font-manrope font-medium text-gray-900">I'll pay additional cash</span>
                            </label>
                            
                            <label class="flex items-center p-3 border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                                <input type="radio" id="money_direction_owner_asks_cash" name="money_direction" value="owner_asks_cash" onchange="toggleCashInput()" class="w-4 h-4 text-[#006a38] cursor-pointer">
                                <span class="ml-3 font-manrope font-medium text-gray-900">I'll request additional cash</span>
                            </label>
                        </div>

                        <p id="ask-cash-rule-hint" class="hidden text-xs text-[#9a3412]">You can request cash only when your offered product price is higher than the target product price.</p>

                        <div id="cash-hint" class="hidden text-xs text-amber-700 bg-amber-50 border border-amber-200 px-3 py-2 rounded">
                            Suggested from price difference. You can edit this amount.
                        </div>

                        <div id="cash-input-pay" class="hidden p-4 bg-amber-50 border border-amber-200">
                            <label class="font-manrope font-semibold text-sm text-gray-900 mb-2 block">I will pay (Rs.)</label>
                            <input type="text" name="offered_amount" id="offered_amount" class="w-full border border-gray-300 focus:border-[#006a38] focus:ring focus:ring-green-200 outline-none px-4 py-2 font-manrope text-sm" inputmode="decimal" autocomplete="off" placeholder="0.01" pattern="^\d+(\.\d{1,2})?$" oninput="sanitizePositiveMoneyInput(this)" onchange="updateSummary()">
                        </div>

                        <div id="cash-input-need" class="hidden p-4 bg-amber-50 border border-amber-200">
                            <label class="font-manrope font-semibold text-sm text-gray-900 mb-2 block">I need (Rs.)</label>
                            <input type="text" name="asking_amount" id="asking_amount" class="w-full border border-gray-300 focus:border-[#006a38] focus:ring focus:ring-green-200 outline-none px-4 py-2 font-manrope text-sm" inputmode="decimal" autocomplete="off" placeholder="0.01" pattern="^\d+(\.\d{1,2})?$" oninput="sanitizePositiveMoneyInput(this)" onchange="updateSummary()">
                        </div>
                    </div>
                </div>

                <!-- Message Section -->
                <div class="bg-white border border-gray-200 shadow-sm">
                    <div class="bg-gray-50 px-5 py-3 border-b">
                        <p class="font-space text-xs uppercase tracking-widest text-gray-600 mb-1">Communication</p>
                        <h3 class="font-space font-bold text-lg text-gray-900">Message</h3>
                    </div>
                    
                    <div class="p-4 space-y-2">
                        <textarea name="message" class="w-full border border-gray-300 focus:border-[#006a38] focus:ring focus:ring-green-200 outline-none px-4 py-2 font-manrope text-sm rounded" rows="3" placeholder="Explain why you want this item or any details about your product condition..." maxlength="2000">{{ old('message') }}</textarea>
                        <div class="flex justify-between items-center">
                            <p class="font-manrope text-xs text-gray-500">Be specific to increase your chances of a successful trade</p>
                            <span class="font-manrope text-xs text-gray-500"><span id="char-count">0</span>/2000</span>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="bg-[#f3f3f3] border border-gray-200 p-4 space-y-3">
                    <h4 class="font-space font-bold text-gray-900">Swap Summary</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="font-manrope text-xs text-gray-600 mb-1">You offer</p>
                            <p id="summary-offering" class="font-space font-bold text-gray-900">Not selected</p>
                        </div>
                        <div class="flex justify-center items-center text-gray-400">
                            <div class="border-b-2 border-gray-400 w-8"></div>
                            <span class="px-2 text-xl">⇄</span>
                            <div class="border-b-2 border-gray-400 w-8"></div>
                        </div>
                        <div>
                            <p class="font-manrope text-xs text-gray-600 mb-1">You receive</p>
                            <p class="font-space font-bold text-gray-900">{{ $product->title }}</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-300">
                        <p class="font-manrope text-xs text-gray-600 mb-1">Cash Adjustment</p>
                        <p id="summary-cash" class="font-space font-bold text-gray-900">None</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('products.show', $product->id) }}" class="bg-white border-2 border-gray-300 text-gray-900 px-5 py-2.5 font-space font-bold text-sm uppercase tracking-wider hover:bg-gray-50 text-center">Cancel</a>
                    <button type="submit" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-5 py-2.5 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110">Send Proposal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const productSelect = document.getElementById('offered_product_id');
    const targetPrice = {{ number_format((float) ($product->price ?? 0), 2, '.', '') }};
    const requesterOffersCashRadio = document.getElementById('money_direction_requester_offers_cash');
    const ownerAsksCashRadio = document.getElementById('money_direction_owner_asks_cash');
    const noCashRadio = document.getElementById('money_direction_none');
    
    // Product selection
    if (productSelect) {
        productSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const preview = document.getElementById('product-preview');
            
            if (this.value) {
                document.getElementById('preview-name').textContent = option.dataset.name;
                document.getElementById('preview-price').textContent = parseFloat(option.dataset.price).toFixed(2);
                document.getElementById('preview-condition').textContent = option.dataset.condition;
                const previewImage = document.getElementById('preview-image');
                const previewNoImage = document.getElementById('preview-no-image');
                const imageSrc = option.dataset.image || '';

                if (imageSrc) {
                    previewImage.src = imageSrc;
                    previewImage.classList.remove('hidden');
                    previewNoImage.classList.add('hidden');
                } else {
                    previewImage.src = '';
                    previewImage.classList.add('hidden');
                    previewNoImage.classList.remove('hidden');
                }

                preview.classList.remove('hidden');
                suggestCashFromPrices();
            } else {
                preview.classList.add('hidden');
                document.querySelector('input[name="money_direction"][value="none"]').checked = true;
                clearCashInputs();
                toggleCashInput();
            }
            updateSummary();
        });

        if (productSelect.value) {
            productSelect.dispatchEvent(new Event('change'));
        }
    }

    function sanitizePositiveMoneyInput(input) {
        let cleaned = (input.value || '').replace(/[^0-9.]/g, '');
        const parts = cleaned.split('.');
        if (parts.length > 2) {
            cleaned = parts.shift() + '.' + parts.join('');
        }
        input.value = cleaned;
    }

    function clearCashInputs() {
        const offered = document.getElementById('offered_amount');
        const asking = document.getElementById('asking_amount');
        if (offered) {
            offered.value = '';
        }
        if (asking) {
            asking.value = '';
        }
    }

    function getPriceGapState() {
        if (!productSelect || !productSelect.value) {
            return 'none';
        }
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const offeredPrice = parseFloat(selectedOption.dataset.price || '0');
        const diff = +(offeredPrice - targetPrice).toFixed(2);

        if (Math.abs(diff) < 0.01) {
            return 'equal';
        }

        return diff > 0 ? 'requester-higher' : 'requester-lower';
    }

    function canRequesterAskCash() {
        return getPriceGapState() === 'requester-higher';
    }

    function canRequesterOfferCash() {
        return getPriceGapState() === 'requester-lower';
    }

    function updateAskingCashEligibility() {
        const hint = document.getElementById('ask-cash-rule-hint');
        const askEligible = canRequesterAskCash();
        const offerEligible = canRequesterOfferCash();
        const state = getPriceGapState();

        ownerAsksCashRadio.disabled = !askEligible;
        requesterOffersCashRadio.disabled = !offerEligible;

        hint.classList.toggle('hidden', askEligible);

        if (state === 'equal') {
            hint.classList.remove('hidden');
            hint.textContent = 'Both products have equal price. Choose straight swap (no cash).';
        } else if (state === 'none') {
            hint.classList.remove('hidden');
            hint.textContent = 'Select your product first. Cash direction is locked automatically by value gap.';
        } else if (!askEligible) {
            hint.textContent = 'You can request cash only when your offered product price is higher than the target product price.';
        } else {
            hint.textContent = 'You can request cash because your product price is higher.';
        }

        if (!askEligible && ownerAsksCashRadio.checked) {
            noCashRadio.checked = true;
            document.getElementById('asking_amount').value = '';
        }

        if (!offerEligible && requesterOffersCashRadio.checked) {
            noCashRadio.checked = true;
            document.getElementById('offered_amount').value = '';
        }
    }

    function suggestCashFromPrices() {
        if (!productSelect || !productSelect.value) {
            updateAskingCashEligibility();
            return;
        }

        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const offeredPrice = parseFloat(selectedOption.dataset.price || '0');
        const difference = +(targetPrice - offeredPrice).toFixed(2);

        if (Math.abs(difference) < 0.01) {
            document.querySelector('input[name="money_direction"][value="none"]').checked = true;
            clearCashInputs();
            updateAskingCashEligibility();
            toggleCashInput();
            return;
        }

        if (difference > 0) {
            document.querySelector('input[name="money_direction"][value="requester_offers_cash"]').checked = true;
            document.getElementById('offered_amount').value = difference.toFixed(2);
            document.getElementById('asking_amount').value = '';
        } else {
            document.querySelector('input[name="money_direction"][value="owner_asks_cash"]').checked = true;
            document.getElementById('asking_amount').value = Math.abs(difference).toFixed(2);
            document.getElementById('offered_amount').value = '';
        }

        updateAskingCashEligibility();
        toggleCashInput();
    }

    // Cash input visibility
    function toggleCashInput() {
        const payInput = document.getElementById('cash-input-pay');
        const needInput = document.getElementById('cash-input-need');
        const cashHint = document.getElementById('cash-hint');
        const selected = document.querySelector('input[name="money_direction"]:checked').value;
        updateAskingCashEligibility();
        
        if (selected === 'none') {
            payInput.classList.add('hidden');
            needInput.classList.add('hidden');
            cashHint.classList.add('hidden');
            document.getElementById('offered_amount').value = '';
            document.getElementById('asking_amount').value = '';
        } else if (selected === 'requester_offers_cash') {
            payInput.classList.remove('hidden');
            needInput.classList.add('hidden');
            cashHint.classList.remove('hidden');
            document.getElementById('asking_amount').value = '';
        } else {
            payInput.classList.add('hidden');
            needInput.classList.remove('hidden');
            cashHint.classList.remove('hidden');
            document.getElementById('offered_amount').value = '';
        }
        updateSummary();
    }

    // Update summary
    function updateSummary() {
        const productSelect = document.getElementById('offered_product_id');
        const offeringDiv = document.getElementById('summary-offering');
        
        if (productSelect && productSelect.value) {
            const option = productSelect.options[productSelect.selectedIndex];
            offeringDiv.textContent = option.dataset.name;
        } else {
            offeringDiv.textContent = 'Not selected';
        }

        const cashDiv = document.getElementById('summary-cash');
        const selected = document.querySelector('input[name="money_direction"]:checked').value;
        const offeredAmount = document.getElementById('offered_amount').value;
        const askingAmount = document.getElementById('asking_amount').value;

        if (selected === 'none') {
            cashDiv.textContent = 'None';
        } else if (selected === 'requester_offers_cash' && offeredAmount) {
            cashDiv.textContent = 'You pay Rs. ' + parseFloat(offeredAmount).toFixed(2);
        } else if (selected === 'owner_asks_cash' && askingAmount) {
            cashDiv.textContent = 'You request Rs. ' + parseFloat(askingAmount).toFixed(2);
        } else {
            cashDiv.textContent = 'Enter amount';
        }
    }

    // Prevent invalid amount submit
    const swapForm = document.getElementById('swap-request-form');
    if (swapForm) {
        swapForm.addEventListener('submit', function (event) {
            const selected = document.querySelector('input[name="money_direction"]:checked').value;
            const offeredField = document.getElementById('offered_amount');
            const askingField = document.getElementById('asking_amount');

            offeredField.setCustomValidity('');
            askingField.setCustomValidity('');

            const isPositiveNumber = (value) => /^\d+(\.\d+)?$/.test(value) && parseFloat(value) > 0;

            if (selected === 'requester_offers_cash') {
                const value = (offeredField.value || '').trim();
                if (!isPositiveNumber(value)) {
                    offeredField.setCustomValidity('Enter a valid amount greater than 0 using numbers only.');
                    offeredField.reportValidity();
                    event.preventDefault();
                    return;
                }
            }

            if (selected === 'owner_asks_cash') {
                if (!canRequesterAskCash()) {
                    askingField.setCustomValidity('You can request cash only when your offered product price is higher.');
                    askingField.reportValidity();
                    event.preventDefault();
                    return;
                }
                const value = (askingField.value || '').trim();
                if (!isPositiveNumber(value)) {
                    askingField.setCustomValidity('Enter a valid amount greater than 0 using numbers only.');
                    askingField.reportValidity();
                    event.preventDefault();
                    return;
                }
            }
        });

        updateAskingCashEligibility();
    }

    // Character counter
    const messageField = document.querySelector('textarea[name="message"]');
    if (messageField) {
        messageField.addEventListener('input', function() {
            document.getElementById('char-count').textContent = this.value.length;
        });
    }
</script>

@endsection
