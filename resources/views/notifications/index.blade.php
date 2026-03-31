@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-8 md:px-16 py-12 space-y-8">
    <section class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-6 md:p-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Activity Feed</p>
                <h1 class="font-space font-bold text-3xl text-[#1a1c1c]">Notifications</h1>
            </div>
            @if($notifications->where('read_at', null)->count())
                <form method="POST" action="{{ route('notifications.markAllRead') }}">
                    @csrf
                    <button type="submit" class="bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-4 py-2 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110">Mark all as read</button>
                </form>
            @endif
        </div>
    </section>

    @if($notifications->isEmpty())
        <div id="notification-empty-state" class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-12 text-center">
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">No notifications yet</p>
            <p class="font-manrope text-xs text-[#888888] mt-1">New updates will appear here.</p>
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
                        'rental'      => ['bg' => 'bg-[#dbeafe]',  'text' => 'text-[#1e40af]',  'label' => 'RENT'],
                        'rentalAccept'=> ['bg' => 'bg-[#d1fae5]',  'text' => 'text-[#065f46]',  'label' => 'OK'],
                        'rentalReject'=> ['bg' => 'bg-[#fee2e2]',  'text' => 'text-[#7f1d1d]',  'label' => 'NO'],
                        'swap'        => ['bg' => 'bg-[#dcfce7]', 'text' => 'text-[#166534]', 'label' => 'SWAP'],
                        'swapAccept'  => ['bg' => 'bg-[#d1fae5]',  'text' => 'text-[#065f46]',  'label' => 'OK'],
                        'swapCounter' => ['bg' => 'bg-[#fef3c7]', 'text' => 'text-[#92400e]', 'label' => 'CTR'],
                        'swapReject'  => ['bg' => 'bg-[#fee2e2]',  'text' => 'text-[#7f1d1d]',  'label' => 'NO'],
                        'dispute'     => ['bg' => 'bg-[#fed7aa]', 'text' => 'text-[#92400e]', 'label' => 'DSP'],
                    ];
                    $icon = $iconMap[$type] ?? ['bg' => 'bg-[#f3f4f6]', 'text' => 'text-[#374151]', 'label' => 'N'];
                @endphp

                <div class="group relative flex items-start gap-4 p-4 {{ $isUnread ? 'bg-[#f3f3f3]' : 'bg-white' }} shadow-[0_20px_40px_rgba(26,28,28,0.06)]" data-id="{{ $notification->id }}">
                    <div class="shrink-0 flex h-10 min-w-10 items-center justify-center text-[10px] font-bold uppercase tracking-[0.08em] {{ $icon['bg'] }} {{ $icon['text'] }}">
                        {{ $icon['label'] }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-manrope text-sm {{ $isUnread ? 'font-bold text-[#1a1c1c]' : 'text-[#444746]' }}">{{ $message }}</p>
                            @if($isUnread)
                                <span class="shrink-0 bg-[#006a38] px-2 py-0.5 text-[10px] font-space font-bold uppercase tracking-widest text-white">New</span>
                            @endif
                        </div>
                        <p class="font-manrope text-xs text-[#888888] mt-1">{{ $notification->created_at->diffForHumans() }}</p>

                        <div class="mt-2 flex items-center gap-3">
                            @if($isUnread)
                                <button type="button" onclick="markNotifReadOnly(event, '{{ $notification->id }}', this)" class="font-manrope text-xs text-[#888888] hover:text-[#444746]">Mark as read</button>
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
    card.className = 'group relative flex items-start gap-4 p-4 bg-[#f3f3f3] shadow-[0_20px_40px_rgba(26,28,28,0.06)]';

    const safeMessage = escapeHtml(message || 'Notification');
    const isViewable = redirectUrl && redirectUrl !== '#';

    card.innerHTML = `
        <div class="shrink-0 flex h-10 min-w-10 items-center justify-center text-[10px] font-bold uppercase tracking-[0.08em] bg-[#f3f4f6] text-[#374151]">${label}</div>
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <p class="text-sm font-bold text-[#1a1c1c] font-manrope">${safeMessage}</p>
                <span class="shrink-0 bg-[#006a38] px-2 py-0.5 text-[10px] font-space font-bold uppercase tracking-widest text-white">New</span>
            </div>
            <p class="text-xs text-[#888888] mt-1 font-manrope">just now</p>
            <div class="mt-2 flex items-center gap-3">
                ${isViewable
                    ? `<a href="${redirectUrl}" class="text-xs font-manrope font-bold text-[#006a38]" onclick="markNotifReadAndGo(event, '${id}', '${redirectUrl}')">View</a>`
                    : `<span class="text-xs font-manrope font-medium text-[#ccc] cursor-not-allowed">View</span>`}
                <button type="button" class="text-xs font-manrope text-[#888888] hover:text-[#444746]" onclick="markNotifReadOnly(event, '${id}', this)">Mark as read</button>
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
            card.classList.remove('bg-[#f3f3f3]');
            card.classList.add('bg-white');
            const msgEl = card.querySelector('p');
            if (msgEl) { msgEl.classList.remove('font-bold', 'text-[#1a1c1c]'); msgEl.classList.add('text-[#444746]'); }
            card.querySelector('.bg-[#006a38]')?.remove();
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
