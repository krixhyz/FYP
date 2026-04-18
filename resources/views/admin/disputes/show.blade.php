@extends('layouts.admin')
@section('title', 'Dispute #' . $dispute->id)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.disputes') }}" class="btn-pill btn-pill-soft !px-3 !py-1.5 text-xs">Back to Disputes</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Dispute Details --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="surface-card p-6">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-xl font-extrabold">{{ $dispute->subject }}</h2>
                    <p class="font-manrope text-xs text-[#888888] mt-0.5">
                        Dispute #{{ $dispute->id }} · {{ ucfirst($dispute->transaction_type) }} ·
                        Filed {{ $dispute->created_at->diffForHumans() }}
                    </p>
                </div>
                <span class="shrink-0 status-chip {{ $dispute->statusBadge() }}">
                    {{ ucfirst(str_replace('_',' ',$dispute->status)) }}
                </span>
            </div>

            @if($dispute->favored_party)
                <div class="mb-4 inline-flex items-center gap-2 rounded-md border border-[rgba(0,106,56,0.35)] bg-[rgba(0,106,56,0.08)] px-3 py-1.5 text-xs font-semibold text-[#006a38]">
                    Decision:
                    @if($dispute->favored_party === 'reporter')
                        Favored Reporter{{ $dispute->reporter ? ' (' . $dispute->reporter->name . ')' : '' }}
                    @else
                        Favored Counterparty{{ !empty($counterparty?->name) ? ' (' . $counterparty->name . ')' : '' }}
                    @endif
                </div>
            @endif

            <div class="font-manrope text-[#444746] leading-relaxed">
                <p>{{ $dispute->description }}</p>
            </div>

            @if(!empty($dispute->evidence_photos))
                <div class="mt-5">
                    <p class="text-sm font-extrabold mb-3">Evidence Photos</p>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                        @foreach($dispute->evidence_photos as $photo)
                            <a href="{{ asset('storage/' . $photo) }}" target="_blank" class="block overflow-hidden rounded-lg border border-[rgba(189,202,189,0.1)] bg-[#f9f9f9]">
                                <img src="{{ asset('storage/' . $photo) }}" alt="Evidence photo" class="h-32 w-full object-cover">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Transaction Context --}}
        <div class="surface-card p-6">
            <h3 class="text-lg font-extrabold mb-3">Transaction Reference</h3>
            @if($dispute->order)
                <dl class="grid grid-cols-2 gap-2 text-sm">
                    <dt class="meta-text">Order ID</dt><dd class="font-medium">#{{ $dispute->order->id }}</dd>
                    <dt class="meta-text">Product</dt><dd class="font-medium">{{ $dispute->order->product?->title ?? 'N/A' }}</dd>
                    <dt class="meta-text">Status</dt><dd><span class="capitalize">{{ $dispute->order->status }}</span></dd>
                </dl>
            @elseif($dispute->rentedRental)
                <dl class="grid grid-cols-2 gap-2 text-sm">
                    <dt class="meta-text">Rented Rental ID</dt><dd class="font-medium">#{{ $dispute->rentedRental->id }}</dd>
                    <dt class="meta-text">Product</dt><dd class="font-medium">{{ $dispute->rentedRental->product?->title ?? 'N/A' }}</dd>
                    <dt class="meta-text">Status</dt><dd><span class="capitalize">{{ $dispute->rentedRental->status }}</span></dd>
                </dl>
            @elseif($dispute->rentalRequest)
                <dl class="grid grid-cols-2 gap-2 text-sm">
                    <dt class="meta-text">Rental Request ID</dt><dd class="font-medium">#{{ $dispute->rentalRequest->id }}</dd>
                    <dt class="meta-text">Product</dt><dd class="font-medium">{{ $dispute->rentalRequest->product?->title ?? 'N/A' }}</dd>
                    <dt class="meta-text">Status</dt><dd><span class="capitalize">{{ $dispute->rentalRequest->status }}</span></dd>
                </dl>
            @elseif($dispute->swap)
                <dl class="grid grid-cols-2 gap-2 text-sm">
                    <dt class="meta-text">Swap ID</dt><dd class="font-medium">#{{ $dispute->swap->id }}</dd>
                    <dt class="meta-text">Status</dt><dd><span class="capitalize">{{ $dispute->swap->status }}</span></dd>
                </dl>
            @else
                <p class="font-manrope text-sm text-[#888888]">Transaction no longer exists.</p>
            @endif

            @if($dispute->transaction_type === 'rental')
                <div class="mt-4 border border-[rgba(189,202,189,0.35)] bg-[#f6faf7] p-3 text-sm">
                    <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">Rental Deposit Context</p>
                    <p class="mt-2 font-manrope text-[#1a1c1c]">Held Deposit: <strong>Rs. {{ number_format((float) ($rentalDepositAmount ?? 0), 2) }}</strong></p>
                    <p class="mt-1 font-manrope text-[#1a1c1c]">Owner Requested Claim: <strong>Rs. {{ number_format((float) ($dispute->owner_claim_amount ?? 0), 2) }}</strong></p>
                    @if(!is_null($dispute->owner_award_amount))
                        <p class="mt-1 font-manrope text-[#1a1c1c]">Last Awarded Amount: <strong>Rs. {{ number_format((float) $dispute->owner_award_amount, 2) }}</strong></p>
                    @endif
                </div>
            @endif
        </div>

        @if($dispute->admin_notes)
            <div class="surface-card p-5 border-2 border-blue-300 bg-blue-50">
                <p class="font-space text-sm font-bold text-blue-800 mb-1">Previous Admin Note</p>
                <p class="font-manrope text-sm text-blue-700">{{ $dispute->admin_notes }}</p>
                @if($dispute->resolver)
                    <p class="font-manrope text-xs text-blue-600 mt-2">by {{ $dispute->resolver->name }} | {{ $dispute->resolved_at?->diffForHumans() }}</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Resolution Panel --}}
    <div class="space-y-4">
        {{-- Reporter Info --}}
        <div class="surface-card p-5">
            <h3 class="text-lg font-extrabold mb-3">Reporter</h3>
            <p class="font-medium">{{ $dispute->reporter?->name ?? 'N/A' }}</p>
            <p class="font-manrope text-sm text-[#444746]">{{ $dispute->reporter?->email }}</p>
            @if($dispute->reporter)
                <a href="{{ route('admin.users.show', $dispute->reporter->id) }}"
                   class="mt-2 inline-block text-xs text-[#006a38] hover:underline font-semibold">View profile</a>
            @endif
        </div>

        <div class="surface-card p-5">
            <h3 class="text-lg font-extrabold mb-3">Counterparty</h3>
            @if($counterparty)
                <p class="font-medium">{{ $counterparty->name }}</p>
                <p class="font-manrope text-sm text-[#444746]">{{ $counterparty->email }}</p>
                <a href="{{ route('admin.users.show', $counterparty->id) }}"
                   class="mt-2 inline-block text-xs text-[#006a38] hover:underline font-semibold">View profile</a>
            @else
                <p class="font-manrope text-sm text-[#888888]">Counterparty could not be resolved for this dispute.</p>
            @endif
        </div>

        {{-- Resolution Form --}}
        <div class="surface-card p-5">
            <h3 class="text-lg font-extrabold mb-4">Update Status</h3>

            @if($requiresEscalation)
                <div class="mb-4 border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800">
                    This dispute involves privileged accounts. You must escalate to Super Admin.
                </div>

                <form method="POST" action="{{ route('admin.disputes.escalate', $dispute) }}" class="space-y-3 mb-4">
                    @csrf
                    @method('PATCH')
                    <textarea name="reason" rows="3" required
                              placeholder="Why this should be escalated..."
                              class="input-field text-sm resize-none"></textarea>
                    <button type="submit"
                            class="btn-pill w-full justify-center !border-amber-700 !text-amber-700 !py-2.5 hover:!bg-amber-700 hover:!text-white">
                        Escalate to Super Admin
                    </button>
                </form>
            @endif

            <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="field-label">Set Status</label>
                    <select name="status" class="input-field !py-2 text-sm">
                        @foreach(['in_review','resolved','dismissed'] as $s)
                            <option value="{{ $s }}" @selected($dispute->status === $s)>
                                {{ ucfirst(str_replace('_',' ',$s)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="field-label">Decision</label>
                    <select name="favored_party" class="input-field !py-2 text-sm">
                        <option value="">Select favored party for final resolution</option>
                        <option value="reporter" @selected(old('favored_party', $dispute->favored_party) === 'reporter')>
                            Favor Reporter{{ $dispute->reporter ? ': ' . $dispute->reporter->name : '' }}
                        </option>
                        <option value="counterparty" @selected(old('favored_party', $dispute->favored_party) === 'counterparty')>
                            Favor Counterparty{{ !empty($counterparty?->name) ? ': ' . $counterparty->name : '' }}
                        </option>
                    </select>
                    <p class="mt-1 text-xs text-[#666]">Required when status is set to resolved or dismissed.</p>
                    @error('favored_party')
                        <p class="mt-1 text-xs text-[#ba1a1a]">{{ $message }}</p>
                    @enderror
                </div>

                @if($dispute->transaction_type === 'rental')
                    <div>
                        <label class="field-label">Award Amount To Owner (Rs.)</label>
                        <input
                            type="number"
                            name="owner_award_amount"
                            min="0"
                            step="0.01"
                            value="{{ old('owner_award_amount', $dispute->owner_award_amount ?? $dispute->owner_claim_amount) }}"
                            placeholder="Leave as requested claim"
                            class="input-field text-sm"
                        >
                        <p class="mt-1 text-xs text-[#666]">Used only when decision favors owner. You may lower or increase within held deposit.</p>
                        @error('owner_award_amount')
                            <p class="mt-1 text-xs text-[#ba1a1a]">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div>
                    <label class="field-label">Admin Notes</label>
                    <textarea name="admin_notes" rows="4"
                              placeholder="Explain the resolution or next steps..."
                              class="input-field text-sm resize-none">{{ old('admin_notes', $dispute->admin_notes) }}</textarea>
                </div>

                <button type="submit"
                        @disabled($requiresEscalation)
                        class="btn-pill btn-pill-dark w-full justify-center !py-2.5 disabled:opacity-40 disabled:cursor-not-allowed">
                    Save &amp; Notify Reporter
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
