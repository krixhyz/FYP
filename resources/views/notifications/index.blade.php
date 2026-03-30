@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-8">
    <section class="surface-card-strong p-6 sm:p-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-primary-800">Activity Feed</p>
                <h1 class="mt-3 text-4xl font-bold">Notifications</h1>
            </div>
            @if($notifications->where('read_at', null)->count())
                <form method="POST" action="{{ route('notifications.markAllRead') }}">
                    @csrf
                    <button type="submit" class="btn-pill btn-pill-dark">Mark all as read</button>
                </form>
            @endif
        </div>
    </section>

    @if($notifications->isEmpty())
        <div id="notification-empty-state" class="surface-card p-12 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.08em] text-neutral-700">No notifications yet</p>
            <p class="mt-1 text-xs text-neutral-500">New updates will appear here.</p>
        </div>
    @else
        <div id="notification-page-list" class="space-y-3">
            @foreach($notifications as $notification)
                @php
                    $isUnread   = is_null($notification->read_at);
                    $message    = $notification->data['message'] ?? 'Notification';
                    $redirectUrl = $notification->data['redirect_url'] ?? '#';
                    $type       = $notification->data['type'] ?? 'general';
                    $canView    = $redirectUrl !== '#';

                    if ($type === 'rental' && !empty($notification->data['rental_request_id'])) {
                        $rentalReq = \App\Models\RentalRequest::find($notification->data['rental_request_id']);

                        if (!$rentalReq || $rentalReq->status === 'rejected') {
                            $redirectUrl = '#';
                            $canView = false;
                        } elseif ($rentalReq->status === 'approved') {
                            $redirectUrl = route('products.myListings');
                            $canView = true;
                        } else {
                            $redirectUrl = route('rental.review', $rentalReq->id);
                            $canView = true;
                        }
                    }

                    $iconMap = [
                        'rental'      => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'label' => 'RENT'],
                        'rentalAccept'=> ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'label' => 'OK'],
                        'rentalReject'=> ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'label' => 'NO'],
                        'swap'        => ['bg' => 'bg-primary-100', 'text' => 'text-primary-800', 'label' => 'SWAP'],
                        'swapAccept'  => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'label' => 'OK'],
                        'swapCounter' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'CTR'],
                        'swapReject'  => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'label' => 'NO'],
                        'dispute'     => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'label' => 'DSP'],
                    ];
                    $icon = $iconMap[$type] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => 'N'];
                @endphp

                <div class="group relative flex items-start gap-4 p-4 {{ $isUnread ? 'surface-card-strong' : 'surface-card' }}" data-id="{{ $notification->id }}">
                    <div class="shrink-0 flex h-10 min-w-10 items-center justify-center text-[11px] font-bold uppercase tracking-[0.08em] {{ $icon['bg'] }} {{ $icon['text'] }}">
                        {{ $icon['label'] }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm {{ $isUnread ? 'font-semibold text-neutral-900' : 'text-neutral-700' }}">{{ $message }}</p>
                            @if($isUnread)
                                <span class="shrink-0 bg-primary-800 px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.08em] text-white">New</span>
                            @endif
                        </div>
                        <p class="text-xs text-neutral-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>

                        <div class="mt-2 flex items-center gap-3">
                            @if($isUnread)
                                <button type="button" onclick="markNotifReadOnly(event, '{{ $notification->id }}', this)" class="text-xs text-neutral-500 hover:text-neutral-700">Mark as read</button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($notifications->hasPages())
            <div class="mt-8">{{ $notifications->links() }}</div>
        @endif
    @endif
</div>

<script>
const MARK_READ_URL = '{{ route('notifications.markRead') }}';
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function prependNotificationToPage(message, redirectUrl, id, type = 'general') {
    let list = document.getElementById('notification-page-list');
    if (!list) {
        const empty = document.getElementById('notification-empty-state');
        if (empty) {
            empty.remove();
            list = document.createElement('div');
            list.id = 'notification-page-list';
            list.className = 'space-y-3';
            const wrapper = document.querySelector('.max-w-4xl.space-y-8');
            if (wrapper) wrapper.appendChild(list);
        }
    }
    if (!list) return;

    const iconMap = {
        rental: 'RENT',
        rentalAccept: 'OK',
        rentalReject: 'NO',
        swap: 'SWAP',
        swapAccept: 'OK',
        swapCounter: 'CTR',
        swapReject: 'NO',
        dispute: 'DSP',
        general: 'N',
    };
    const label = iconMap[type] ?? 'N';

    const card = document.createElement('div');
    card.className = 'group relative flex items-start gap-4 p-4 surface-card-strong';

    const safeMessage = escapeHtml(message || 'Notification');
    const isViewable = redirectUrl && redirectUrl !== '#';

    card.innerHTML = `
        <div class="shrink-0 flex h-10 min-w-10 items-center justify-center text-[11px] font-bold uppercase tracking-[0.08em] bg-accent-100 text-neutral-800">${label}</div>
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <p class="text-sm font-semibold text-neutral-900">${safeMessage}</p>
                <span class="shrink-0 bg-primary-800 px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.08em] text-white">New</span>
            </div>
            <p class="text-xs text-neutral-500 mt-1">just now</p>
            <div class="mt-2 flex items-center gap-3">
                ${isViewable
                    ? `<a href="${redirectUrl}" class="text-xs font-semibold text-primary-800" onclick="markNotifReadAndGo(event, '${id}', '${redirectUrl}')">View</a>`
                    : `<span class="text-xs font-medium text-neutral-400 cursor-not-allowed">View</span>`}
                <button type="button" class="text-xs text-neutral-500 hover:text-neutral-700" onclick="markNotifReadOnly(event, '${id}', this)">Mark as read</button>
            </div>
        </div>
    `;

    list.prepend(card);
}

function markNotifReadAndGo(e, id, url) {
    e.preventDefault();
    fetch(MARK_READ_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: JSON.stringify({ id }),
    }).finally(() => { window.location.href = url; });
}

function markNotifReadOnly(e, id, btn) {
    e.stopPropagation();
    fetch(MARK_READ_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: JSON.stringify({ id }),
    }).then(r => r.json()).then(() => {
        const card = btn.closest('[data-id]');
        if (card) {
            card.classList.remove('surface-card-strong');
            card.classList.add('surface-card');
            const msgEl = card.querySelector('p');
            if (msgEl) { msgEl.classList.remove('font-semibold', 'text-neutral-900'); msgEl.classList.add('text-neutral-700'); }
            card.querySelector('.bg-primary-800')?.remove();
        }
        btn.remove();
    });
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str ?? ''));
    return d.innerHTML;
}
</script>
@endsection
