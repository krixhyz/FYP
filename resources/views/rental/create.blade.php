@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-8">
    <section class="surface-card-strong p-6 sm:p-8">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Rent Workflow</p>
        <h1 class="mt-4 font-space text-4xl font-bold text-[#1a1c1c]">Request Rental</h1>
        <p class="mt-2 font-manrope text-sm text-[#444746]">Select your rental window and submit a request.</p>
    </section>

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
                <div class="flex justify-between"><span>Category</span><span class="font-semibold">{{ $product->category ?? 'General' }}</span></div>
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
                  data-max-duration="{{ $rental ? $rental->duration : 100 }}"
                  data-owner-start-date="{{ $rental && $rental->available_from ? \Carbon\Carbon::parse($rental->available_from)->format('Y-m-d') : '' }}"
                  data-owner-end-date="{{ $ownerEndDate }}"
                  class="space-y-4">
                @csrf

                <div>
                    <label class="label">Start Date</label>
                    <input type="date" name="start_date" id="startDate" class="input" required>
                </div>

                <div>
                    <label class="label">End Date</label>
                    <input type="date" name="end_date" id="endDate" class="input" required>
                </div>

                <div class="bg-accent-50 p-4">
                    <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#444746]">Estimated Total</p>
                    <p id="totalAmount" class="mt-2 font-manrope text-2xl font-bold text-[#006a38]">Rs. 0</p>
                </div>

                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="rent_fare" id="rentFare" value="{{ $rental ? $rental->rent_fare : 0 }}">
                <input type="hidden" name="rent_deposit" id="rentDeposit" value="{{ $rental ? $rental->rent_deposit : 0 }}">
                <input type="hidden" name="duration" id="duration" value="0">
                <input type="hidden" name="total_amount" id="totalAmountInput" value="0">

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
    startInput.setAttribute('min', ownerStartDate || today);
    if (ownerEndDate) {
        startInput.setAttribute('max', ownerEndDate);
    }

    function updateTotal() {
        if (!startInput.value || !endInput.value) {
            totalAmountDisplay.textContent = 'Rs. 0';
            durationInput.value = 0;
            totalAmountInput.value = 0;
            return;
        }

        let start = new Date(startInput.value);
        let end = new Date(endInput.value);
        if (ownerEndDate) {
            const maxEnd = new Date(ownerEndDate);
            if (end > maxEnd) end = maxEnd;
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

    startInput.addEventListener('change', () => {
        const start = new Date(startInput.value);
        const maxEnd = new Date(start);
        maxEnd.setDate(maxEnd.getDate() + parseInt(maxDuration) - 1);

        if (ownerEndDate) {
            const ownerEnd = new Date(ownerEndDate);
            const finalMax = maxEnd < ownerEnd ? maxEnd : ownerEnd;
            endInput.max = finalMax.toISOString().split('T')[0];
        }

        endInput.min = startInput.value;
        const currentEnd = new Date(endInput.value);
        if (endInput.max && currentEnd > new Date(endInput.max)) {
            endInput.value = endInput.max;
        }

        updateTotal();
    });

    endInput.addEventListener('change', updateTotal);

    form.addEventListener('submit', () => {
        updateTotal();
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting...';
    });

    if (startInput.value && endInput.value) {
        updateTotal();
    }
}
</script>
@endsection
