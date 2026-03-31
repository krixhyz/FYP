@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-8">
    <section class="surface-card-strong p-6 sm:p-8">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Swap Workflow</p>
        <h1 class="mt-4 font-space text-4xl font-bold text-[#1a1c1c]">Send Swap Request</h1>
    </section>

    <section class="surface-card p-5 sm:p-6">
        <form action="{{ route('swap.request.store') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" id="target_price" value="{{ $product->price }}">

            <div class="bg-accent-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-neutral-500">Target Listing</p>
                <h2 class="mt-2 text-xl font-bold text-neutral-900">{{ $product->title }}</h2>
                <p class="mt-1 text-sm text-neutral-600">{{ Str::limit($product->description, 100) }}</p>
                <p class="mt-2 text-sm">Category: <span class="font-semibold">{{ $product->category ?? 'General' }}</span></p>
                <p class="text-sm">Price: <span class="font-semibold">Rs. {{ $product->price }}</span></p>
            </div>

            <div>
                <label for="offered_product_id" class="label">Select Your Offered Product</label>
                <select name="offered_product_id" id="offered_product_id" required class="input">
                    <option value="">Choose your product</option>
                    @foreach ($userProducts as $userProduct)
                        <option value="{{ $userProduct->id }}" data-price="{{ $userProduct->price }}">
                            {{ $userProduct->title }} (Rs. {{ $userProduct->price }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="offered_amount" class="label">Additional Cash Offer</label>
                <input type="number" name="offered_amount" id="offered_amount" value="0" min="0" class="input" />
                <p id="cash_suggestion" class="mt-2 text-xs text-neutral-600">
                    Tip: If your product value is lower, you can add a cash top-up.
                </p>
            </div>

            <div>
                <label for="message" class="label">Message (Optional)</label>
                <textarea name="message" id="message" rows="3" class="input" placeholder="Share product condition or exchange notes"></textarea>
            </div>

            <button type="submit" class="btn-pill btn-pill-dark w-full justify-center">Send Swap Request</button>
        </form>
    </section>
</div>

<script>
const targetPrice = parseFloat(document.getElementById('target_price').value);
const offeredProductSelect = document.getElementById('offered_product_id');
const offeredAmountInput = document.getElementById('offered_amount');
const cashSuggestion = document.getElementById('cash_suggestion');

offeredProductSelect.addEventListener('change', () => {
    const selectedOption = offeredProductSelect.options[offeredProductSelect.selectedIndex];
    const yourPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;

    if (yourPrice < targetPrice) {
        const difference = targetPrice - yourPrice;
        offeredAmountInput.value = difference;
        cashSuggestion.innerHTML = `Your product is lower by Rs. <strong>${difference}</strong>. Consider adding this as cash top-up.`;
    } else if (yourPrice > targetPrice) {
        const surplus = yourPrice - targetPrice;
        offeredAmountInput.value = 0;
        cashSuggestion.innerHTML = `Your product is higher by Rs. <strong>${surplus}</strong>. Extra cash is not required.`;
    } else {
        offeredAmountInput.value = 0;
        cashSuggestion.innerHTML = 'Both products have equivalent value. No cash top-up needed.';
    }

    offeredAmountInput.addEventListener('input', () => {
        if (offeredAmountInput.value < 0) offeredAmountInput.value = 0;
    });
});
</script>
@endsection
