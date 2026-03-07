@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <p class="text-sm text-gray-500 mt-0.5">All your recent activity</p>
        </div>
        @if($notifications->where('read_at', null)->count())
            <form method="POST" action="{{ route('notifications.markAllRead') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    {{-- Notification list --}}
    @if($notifications->isEmpty())
        <div id="notification-empty-state" class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <p class="text-gray-500 font-medium">No notifications yet</p>
            <p class="text-gray-400 text-sm mt-1">We'll let you know when something arrives.</p>
        </div>
    @else
        <div id="notification-page-list" class="space-y-2">
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
                        'rental'      => ['bg' => 'bg-blue-100',   'text' => 'text-blue-600',   'icon' => '🏠'],
                        'rentalAccept'=> ['bg' => 'bg-green-100',  'text' => 'text-green-600',  'icon' => '✅'],
                        'rentalReject'=> ['bg' => 'bg-red-100',    'text' => 'text-red-600',    'icon' => '❌'],
                        'swap'        => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'icon' => '🔄'],
                        'swapAccept'  => ['bg' => 'bg-green-100',  'text' => 'text-green-600',  'icon' => '🤝'],
                        'swapCounter' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'icon' => '↩️'],
                        'swapReject'  => ['bg' => 'bg-red-100',    'text' => 'text-red-600',    'icon' => '❌'],
                        'dispute'     => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'icon' => '⚑'],
                    ];
                    $icon = $iconMap[$type] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'icon' => '🔔'];
                @endphp
                <div class="group relative flex items-start gap-4 rounded-2xl border px-5 py-4 transition
                            {{ $isUnread
                                ? 'bg-blue-50 border-blue-200 hover:bg-blue-100'
                                : 'bg-white border-gray-200 hover:bg-gray-50' }}"
                     data-id="{{ $notification->id }}">

                    {{-- Type icon --}}
                    <div class="shrink-0 w-10 h-10 rounded-full {{ $icon['bg'] }} {{ $icon['text'] }} flex items-center justify-center text-lg">
                        {{ $icon['icon'] }}
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm {{ $isUnread ? 'font-semibold text-gray-900' : 'text-gray-700' }}">
                                {{ $message }}
                            </p>
                            @if($isUnread)
                                <span class="shrink-0 inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-[10px] font-bold text-white">
                                    New
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>

                        {{-- Actions --}}
                        <div class="mt-2 flex items-center gap-3">
                            @if($canView)
                                <a href="{{ $redirectUrl }}"
                                   onclick="markNotifReadAndGo(event, '{{ $notification->id }}', '{{ $redirectUrl }}')"
                                   class="text-xs font-medium text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                    View
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @elseif($type === 'rental')
                                <span class="text-xs font-medium text-gray-400 cursor-not-allowed inline-flex items-center gap-1">
                                    View
                                </span>
                            @endif

                            @if($isUnread)
                                <button type="button"
                                        onclick="markNotifReadOnly(event, '{{ $notification->id }}', this)"
                                        class="text-xs text-gray-500 hover:text-gray-700">
                                    Mark as read
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="mt-8">
                {{ $notifications->links() }}
            </div>
        @endif
    @endif
</div>

<script>
    const MARK_READ_URL     = '{{ route('notifications.markRead') }}';
    const MARK_ALL_READ_URL = '{{ route('notifications.markAllRead') }}';
    const CSRF_TOKEN        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function prependNotificationToPage(message, redirectUrl, id, type = 'general') {
            let list = document.getElementById('notification-page-list');
            if (!list) {
                const empty = document.getElementById('notification-empty-state');
                if (empty) {
                    empty.remove();

                    list = document.createElement('div');
                    list.id = 'notification-page-list';
                    list.className = 'space-y-2';
                    const wrapper = document.querySelector('.max-w-3xl.mx-auto.py-8');
                    if (wrapper) {
                        wrapper.appendChild(list);
                    }
                }
            }
            if (!list) return;

        const iconMap = {
            rental: '🏠',
            rentalAccept: '✅',
            rentalReject: '❌',
            swap: '🔄',
            swapAccept: '🤝',
            swapCounter: '↩️',
            swapReject: '❌',
            dispute: '⚑',
            general: '🔔',
        };
        const icon = iconMap[type] ?? '🔔';

        const card = document.createElement('div');
        card.className = 'group relative flex items-start gap-4 rounded-2xl border px-5 py-4 transition bg-blue-50 border-blue-200 hover:bg-blue-100';

        const safeMessage = escapeHtml(message || 'Notification');
        const isViewable = redirectUrl && redirectUrl !== '#';

        card.innerHTML = `
            <div class="shrink-0 w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-lg">${icon}</div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-semibold text-gray-900">${safeMessage}</p>
                    <span class="shrink-0 inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-[10px] font-bold text-white">New</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">just now</p>
                <div class="mt-2 flex items-center gap-3">
                    ${isViewable
                        ? `<a href="${redirectUrl}" class="text-xs font-medium text-blue-600 hover:text-blue-800 inline-flex items-center gap-1" onclick="markNotifReadAndGo(event, '${id}', '${redirectUrl}')">View</a>`
                        : `<span class="text-xs font-medium text-gray-400 cursor-not-allowed inline-flex items-center gap-1">View</span>`}
                    <button type="button" class="text-xs text-gray-500 hover:text-gray-700" onclick="markNotifReadOnly(event, '${id}', this)">Mark as read</button>
                </div>
            </div>
        `;

        list.prepend(card);
    }

    function markNotifReadAndGo(e, id, url) {
        e.preventDefault();
        fetch(MARK_READ_URL, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body   : JSON.stringify({ id }),
        }).finally(() => { window.location.href = url; });
    }

    function markNotifReadOnly(e, id, btn) {
        e.stopPropagation();
        fetch(MARK_READ_URL, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body   : JSON.stringify({ id }),
        }).then(r => r.json()).then(() => {
            // Restyle the card
            const card = btn.closest('[class*="rounded-2xl"]');
            if (card) {
                card.classList.remove('bg-blue-50', 'border-blue-200', 'hover:bg-blue-100');
                card.classList.add('bg-white', 'border-gray-200', 'hover:bg-gray-50');
                const msgEl = card.querySelector('p');
                if (msgEl) { msgEl.classList.remove('font-semibold', 'text-gray-900'); msgEl.classList.add('text-gray-700'); }
                card.querySelector('.bg-blue-600')?.remove();
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
