@extends('layouts.dashboard')

@section('content')
<!-- Header Section -->
<section class="px-0 md:px-8 py-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="font-space text-[12px] font-bold uppercase tracking-widest text-[#888] mb-2">Activity Feed</p>
            <h1 class="font-space font-bold text-4xl text-[#1a1c1c]">Notifications</h1>
        </div>
        @if($notifications->where('read_at', null)->count())
            <form method="POST" action="{{ route('notifications.markAllRead') }}">
                @csrf
                <button type="submit" class="bg-[#006a38] text-white px-4 py-2 font-space font-bold text-[10px] uppercase tracking-wider hover:bg-[#004a29] transition-all">Mark all as read</button>
            </form>
        @endif
    </div>
</section>

<!-- Notifications List -->
<section class="px-0 md:px-8 py-6">
    @if($notifications->isEmpty())
        <div id="notification-empty-state" class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-12 text-center">
            <svg class="w-16 h-16 text-[#ccc] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888]">No notifications yet</p>
            <p class="font-manrope text-sm text-[#888] mt-2">New updates will appear here.</p>
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

                <div class="group relative flex items-start gap-4 p-4 {{ $isUnread ? 'bg-[#f9f9f9] border-l-4 border-l-[#006a38]' : 'bg-white' }} border border-[rgba(189,202,189,0.1)] rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] hover:shadow-[0_8px_12px_rgba(0,0,0,0.1)] transition-all" data-id="{{ $notification->id }}">
                    <div class="shrink-0 flex h-10 min-w-10 items-center justify-center text-[10px] font-bold uppercase tracking-[0.08em] rounded {{ $icon['bg'] }} {{ $icon['text'] }}">
                        {{ $icon['label'] }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-manrope text-sm {{ $isUnread ? 'font-bold text-[#1a1c1c]' : 'text-[#444746]' }}">{{ $message }}</p>
                            @if($isUnread)
                                <span class="shrink-0 bg-[#006a38] px-2 py-0.5 text-[10px] font-space font-bold uppercase tracking-widest text-white rounded">New</span>
                            @endif
                        </div>
                        <p class="font-manrope text-xs text-[#888] mt-1">{{ $notification->created_at->diffForHumans() }}</p>

                        <div class="mt-3 flex items-center gap-3">
                            @if($canView)
                                <a href="{{ $redirectUrl }}" class="text-xs font-space font-bold text-[#006a38] hover:text-[#004a29] transition-colors" onclick="markNotifReadAndGo(event, '{{ $notification->id }}', '{{ $redirectUrl }}')">View</a>
                            @endif
                            @if($isUnread)
                                <button type="button" onclick="markNotifReadOnly(event, '{{ $notification->id }}', this)" class="font-manrope text-xs text-[#888] hover:text-[#1a1c1c] transition-colors">Mark as read</button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($notifications->hasPages())
            <div class="mt-8 px-0 md:px-8">{{ $notifications->links() }}</div>
        @endif
    @endif
</section>

<div class="h-8"></div>

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
            list.className = 'space-y-3 px-0 md:px-8 py-6';
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
    card.className = 'group relative flex items-start gap-4 p-4 bg-[#f9f9f9] border-l-4 border-l-[#006a38] border border-[rgba(189,202,189,0.1)] rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)]';

    const safeMessage = escapeHtml(message || 'Notification');
    const isViewable = redirectUrl && redirectUrl !== '#';

    card.innerHTML = `
        <div class="shrink-0 flex h-10 min-w-10 items-center justify-center text-[10px] font-bold uppercase tracking-[0.08em] bg-[#f3f4f6] text-[#374151] rounded">${label}</div>
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <p class="text-sm font-bold text-[#1a1c1c] font-manrope">${safeMessage}</p>
                <span class="shrink-0 bg-[#006a38] px-2 py-0.5 text-[10px] font-space font-bold uppercase tracking-widest text-white rounded">New</span>
            </div>
            <p class="text-xs text-[#888] mt-1 font-manrope">just now</p>
            <div class="mt-3 flex items-center gap-3">
                ${isViewable
                    ? `<a href="${redirectUrl}" class="text-xs font-space font-bold text-[#006a38] hover:text-[#004a29]" onclick="markNotifReadAndGo(event, '${id}', '${redirectUrl}')">View</a>`
                    : ``}
                <button type="button" class="text-xs font-manrope text-[#888] hover:text-[#1a1c1c]" onclick="markNotifReadOnly(event, '${id}', this)">Mark as read</button>
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
            card.classList.remove('bg-[#f9f9f9]', 'border-l-4', 'border-l-[#006a38]');
            card.classList.add('bg-white');
            const msgEl = card.querySelector('p');
            if (msgEl && msgEl.textContent.trim().length > 0) { msgEl.classList.remove('font-bold', 'text-[#1a1c1c]'); msgEl.classList.add('text-[#444746]'); }
            const newBadge = card.querySelector('.bg-\\[\\#006a38\\]');
            if (newBadge) newBadge.remove();
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
