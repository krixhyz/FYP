@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-8">
    <section class="surface-card-strong p-6 sm:p-8">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Rent Workflow</p>
        <h1 class="mt-4 font-space text-4xl font-bold text-[#1a1c1c]">Request Rental</h1>
        <p class="mt-2 font-manrope text-sm text-[#444746]">Select your rental window and submit a request.</p>
    </section>

    @if($errors->any())
        <div class="border-2 border-[#ba1a1a] bg-[rgba(186,26,26,0.06)] px-4 py-3 font-manrope text-sm text-[#7f1d1d] space-y-1">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#ba1a1a]">Please fix the following</p>
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @php
        $rental = $product->rentals()->first();
        $ownerEndDate = null;
        if ($rental && $rental->available_from && $rental->available_duration) {
            $start = \Carbon\Carbon::parse($rental->available_from);
            $ownerEndDate = $start->copy()->addDays($rental->available_duration - 1)->format('Y-m-d');
        }
    @endphp

    <section class="grid grid-cols-1 gap-6 md:grid-cols-[0.95fr_1.05fr]">
        <article class="surface-card p-4">
            <div class="h-56 overflow-hidden bg-[#f3f3f3]">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" class="h-full w-full object-cover" alt="{{ $product->title }}">
                @else
                    <div class="flex h-full items-center justify-center text-[#888888]">No image available</div>
                @endif
            </div>
            <h2 class="mt-3 text-xl font-bold">{{ $product->title }}</h2>
            <p class="mt-1 font-manrope text-sm text-[#444746]">{{ Str::limit($product->description, 120) }}</p>

            <div class="mt-4 space-y-2 bg-[#f3f3f3] p-4 text-sm">
                <div class="flex justify-between"><span>Category</span><span class="font-semibold">{{ $product->category?->name ?? 'General' }}</span></div>
                <div class="flex justify-between"><span>Available Quantity</span><span class="font-semibold">{{ $product->quantity }}</span></div>
                @if($rental)
                    <div class="flex justify-between"><span>Rent Fare</span><span class="font-semibold">Rs. {{ $rental->rent_fare }} / day</span></div>
                    <div class="flex justify-between"><span>Deposit</span><span class="font-semibold">Rs. {{ $rental->rent_deposit }}</span></div>
                    <div class="flex justify-between"><span>Available From</span><span class="font-semibold">{{ $rental->available_from ? \Carbon\Carbon::parse($rental->available_from)->format('Y-m-d') : 'Not set' }}</span></div>
                    <div class="flex justify-between"><span>Available Until</span><span class="font-semibold">{{ $ownerEndDate ?? 'Not set' }}</span></div>
                @else
                    <p class="text-red-700">Rental information unavailable.</p>
                @endif
            </div>
        </article>

        <article class="surface-card p-5">
            <form action="{{ route('rental.store', $product->id) }}" method="POST" id="rentalForm"
                  data-rent-fare="{{ $rental ? $rental->rent_fare : 0 }}"
                  data-rent-deposit="{{ $rental ? $rental->rent_deposit : 0 }}"
                  data-max-duration="{{ $product->rent_duration ?? ($rental ? $rental->available_duration : 100) }}"
                  data-owner-start-date="{{ $rental && $rental->available_from ? \Carbon\Carbon::parse($rental->available_from)->format('Y-m-d') : '' }}"
                  data-owner-end-date="{{ $ownerEndDate }}"
                  class="space-y-4">
                @csrf

                <div>
                    <label for="startDate" class="label">Start Date</label>
                    <input type="date" name="start_date" id="startDate" class="input" value="{{ old('start_date') }}" required>
                    @error('start_date')
                        <p class="mt-1 text-sm text-[#ba1a1a]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="endDate" class="label">End Date</label>
                    <input type="date" name="end_date" id="endDate" class="input" value="{{ old('end_date') }}" required>
                    @error('end_date')
                        <p class="mt-1 text-sm text-[#ba1a1a]">{{ $message }}</p>
                    @enderror
                </div>

                <p id="rentalFormError" class="hidden text-sm font-manrope text-[#ba1a1a]"></p>

                <div class="bg-accent-50 p-4">
                    <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#444746]">Estimated Total</p>
                    <p id="totalAmount" class="mt-2 font-manrope text-2xl font-bold text-[#006a38]">Rs. 0</p>
                </div>

                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="rent_fare" id="rentFare" value="{{ $rental ? $rental->rent_fare : 0 }}">
                <input type="hidden" name="rent_deposit" id="rentDeposit" value="{{ $rental ? $rental->rent_deposit : 0 }}">
                <input type="hidden" name="duration" id="duration" value="0">
                <input type="hidden" name="total_amount" id="totalAmountInput" value="0">
                @error('duration')
                    <p class="text-sm text-[#ba1a1a]">{{ $message }}</p>
                @enderror
                @error('total_amount')
                    <p class="text-sm text-[#ba1a1a]">{{ $message }}</p>
                @enderror

                <button type="submit" class="btn-pill btn-pill-dark w-full justify-center">Submit Rental Request</button>
            </form>
        </article>
    </section>
</div>

<script>
const form = document.getElementById('rentalForm');
const startInput = document.getElementById('startDate');
const endInput = document.getElementById('endDate');
const totalAmountDisplay = document.getElementById('totalAmount');

const rentFareInput = document.getElementById('rentFare');
const rentDepositInput = document.getElementById('rentDeposit');
const durationInput = document.getElementById('duration');
const totalAmountInput = document.getElementById('totalAmountInput');

if (form && startInput && endInput && totalAmountDisplay) {
    const rentFare = parseFloat(form.dataset.rentFare) || 0;
    const rentDeposit = parseFloat(form.dataset.rentDeposit) || 0;
    const maxDuration = parseInt(form.dataset.maxDuration) || 100;
    const ownerStartDate = form.dataset.ownerStartDate || '';
    const ownerEndDate = form.dataset.ownerEndDate || '';

    const today = new Date().toISOString().split('T')[0];
    const effectiveMinStart = ownerStartDate && ownerStartDate > today ? ownerStartDate : today;
    startInput.setAttribute('min', effectiveMinStart);
    if (ownerEndDate) {
        startInput.setAttribute('max', ownerEndDate);
    }

    function toDate(dateString) {
        return new Date(`${dateString}T00:00:00`);
    }

    function syncEndBounds() {
        const minEnd = startInput.value || effectiveMinStart;
        endInput.min = minEnd;

        if (!startInput.value) {
            endInput.max = ownerEndDate || '';
            return;
        }

        const start = toDate(startInput.value);
        const maxByDuration = new Date(start);
        maxByDuration.setDate(maxByDuration.getDate() + maxDuration - 1);

        let finalMax = maxByDuration;
        if (ownerEndDate) {
            const ownerEnd = toDate(ownerEndDate);
            finalMax = maxByDuration < ownerEnd ? maxByDuration : ownerEnd;
        }

        endInput.max = finalMax.toISOString().split('T')[0];
    }

    function enforceEndWithinBounds() {
        if (!endInput.value) {
            return;
        }

        if (endInput.min && endInput.value < endInput.min) {
            endInput.value = endInput.min;
        }

        if (endInput.max && endInput.value > endInput.max) {
            endInput.value = endInput.max;
        }
    }

    function updateTotal() {
        if (!startInput.value || !endInput.value) {
            totalAmountDisplay.textContent = 'Rs. 0';
            durationInput.value = 0;
            totalAmountInput.value = 0;
            return;
        }

        const start = toDate(startInput.value);
        const end = toDate(endInput.value);

        if (end < start) {
            totalAmountDisplay.textContent = 'Rs. 0';
            durationInput.value = 0;
            totalAmountInput.value = 0;
            return;
        }

        let diffDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
        if (diffDays > maxDuration) diffDays = maxDuration;

        const total = (rentFare * diffDays + rentDeposit).toFixed(2);

        totalAmountDisplay.textContent = `Rs. ${total}`;
        durationInput.value = diffDays;
        rentFareInput.value = rentFare;
        rentDepositInput.value = rentDeposit;
        totalAmountInput.value = total;
    }

    function calculateDurationDays() {
        if (!startInput.value || !endInput.value) {
            return 0;
        }

        const start = toDate(startInput.value);
        const end = toDate(endInput.value);

        if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end < start) {
            return 0;
        }

        let diffDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
        if (diffDays > maxDuration) {
            diffDays = maxDuration;
        }

        return diffDays;
    }

    function showFormError(message) {
        const formError = document.getElementById('rentalFormError');
        if (!formError) {
            return;
        }

        formError.textContent = message;
        formError.classList.remove('hidden');
    }

    function clearFormError() {
        const formError = document.getElementById('rentalFormError');
        if (!formError) {
            return;
        }

        formError.textContent = '';
        formError.classList.add('hidden');
    }

    startInput.addEventListener('change', () => {
        clearFormError();
        syncEndBounds();

        if (!endInput.value) {
            endInput.value = startInput.value;
        }

        enforceEndWithinBounds();

        updateTotal();
    });

    endInput.addEventListener('change', () => {
        clearFormError();
        enforceEndWithinBounds();
        updateTotal();
    });

    form.addEventListener('submit', () => {
        clearFormError();
        syncEndBounds();
        enforceEndWithinBounds();
        updateTotal();

        const durationDays = calculateDurationDays();
        if (durationDays > 0) {
            durationInput.value = durationDays;
            const total = (rentFare * durationDays + rentDeposit).toFixed(2);
            totalAmountDisplay.textContent = `Rs. ${total}`;
            totalAmountInput.value = total;
        } else {
            durationInput.value = 0;
            totalAmountInput.value = 0;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';
        }
    });

    syncEndBounds();
    enforceEndWithinBounds();

    if (startInput.value && endInput.value) {
        updateTotal();
    }
}
</script>
@endsection
