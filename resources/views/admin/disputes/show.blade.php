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
                    <p class="text-xs text-neutral-500 mt-0.5">
                        Dispute #{{ $dispute->id }} · {{ ucfirst($dispute->transaction_type) }} ·
                        Filed {{ $dispute->created_at->diffForHumans() }}
                    </p>
                </div>
                <span class="shrink-0 status-chip {{ $dispute->statusBadge() }}">
                    {{ ucfirst(str_replace('_',' ',$dispute->status)) }}
                </span>
            </div>

            <div class="prose prose-sm max-w-none text-neutral-700">
                <p>{{ $dispute->description }}</p>
            </div>
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
                <p class="text-sm text-neutral-500">Transaction no longer exists.</p>
            @endif
        </div>

        @if($dispute->admin_notes)
            <div class="surface-card p-5 bg-blue-50 border-blue-200">
                <p class="text-sm font-semibold text-blue-800 mb-1">Previous Admin Note</p>
                <p class="text-sm text-blue-700">{{ $dispute->admin_notes }}</p>
                @if($dispute->resolver)
                    <p class="text-xs text-blue-600 mt-2">by {{ $dispute->resolver->name }} | {{ $dispute->resolved_at?->diffForHumans() }}</p>
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
            <p class="text-sm text-neutral-600">{{ $dispute->reporter?->email }}</p>
            @if($dispute->reporter)
                <a href="{{ route('admin.users.show', $dispute->reporter->id) }}"
                   class="mt-2 inline-block text-xs text-[var(--reloop-green)] hover:underline">View profile</a>
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
