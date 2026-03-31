/**
 * Real-time notification handler.
 *
 * Listens on the authenticated user's private channel and:
 *  - Shows a clickable toast (auto-closes in 5 s)
 *  - Increments the bell badge
 *  - Prepends the notification into the dropdown list (no page refresh)
 */
let pollingIntervalId = null;

if (window.Laravel && window.Laravel.userId) {
    const channelName = `App.Models.User.${window.Laravel.userId}`;
    console.debug('[Echo] Subscribing to private channel:', channelName);

    const channel = window.Echo.private(channelName)
        .subscribed(() => {
            console.debug('[Echo] Subscribed successfully:', channelName);
            stopPollingFallback();
        })
        .error((error) => {
            console.error('[Echo] Subscription/auth error:', error);
            startPollingFallback();
        })
        .notification((notification) => {

            console.debug('[Echo] Notification received:', notification);

            const message     = notification.message     || 'You have a new notification';
            const redirectUrl = notification.redirect_url || null;
            const notifId     = notification.id          || null;   // UUID from Laravel

            // 1. Show clickable toast
            showToast(message, redirectUrl, notifId);

            // 2. Increment bell badge
            incrementBadge();

            // 3. Prepend into dropdown (if the dropdown helper is available)
            if (typeof prependNotificationToDropdown === 'function') {
                prependNotificationToDropdown(
                    message,
                    redirectUrl || '#',
                    notifId || ''
                );
            }

            if (typeof prependNotificationToPage === 'function') {
                prependNotificationToPage(
                    message,
                    redirectUrl || '#',
                    notifId || '',
                    notification.type || 'general'
                );
            }

            if (typeof prependNotificationToDashboard === 'function') {
                prependNotificationToDashboard(
                    message,
                    redirectUrl || '#',
                    notifId || '',
                    notification.type || 'general'
                );
            }
        });

    const connectorChannel = channel?.subscription;
    if (connectorChannel && typeof connectorChannel.bind === 'function') {
        connectorChannel.bind('pusher:subscription_succeeded', () => {
            console.debug('[Echo] pusher:subscription_succeeded:', channelName);
            stopPollingFallback();
        });
        connectorChannel.bind('pusher:subscription_error', (status) => {
            console.error('[Echo] pusher:subscription_error:', status);
            startPollingFallback();
        });
    }

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            syncNotificationsFallback();
        }
    });

    window.addEventListener('online', () => {
        syncNotificationsFallback();
    });

    setTimeout(syncNotificationsFallback, 1200);

} else {
    console.debug('[Echo] Live notifications not initialized (missing window.Laravel.userId).');
}

function startPollingFallback() {
    if (pollingIntervalId !== null) return;
    pollingIntervalId = window.setInterval(syncNotificationsFallback, 15000);
    syncNotificationsFallback();
}

function stopPollingFallback() {
    if (pollingIntervalId === null) return;
    window.clearInterval(pollingIntervalId);
    pollingIntervalId = null;
}

/* ─────────────────────────────────────────────────────────────────────────
   TOAST SYSTEM
   ───────────────────────────────────────────────────────────────────────── */

/**
 * Display a toast notification.
 *
 * @param {string}      message      Text to show.
 * @param {string|null} redirectUrl  Where to navigate when clicked.
 * @param {string|null} notifId      Database notification UUID (for mark-read).
 */
function showToast(message, redirectUrl = null, notifId = null) {
    const container = getOrCreateToastContainer();

    const toast = document.createElement('div');
    toast.className = [
        'toast-item',
        'flex items-start gap-3',
        'rounded-2xl shadow-lg border border-gray-200',
        'bg-white px-4 py-3 max-w-sm w-full',
        'cursor-pointer select-none',
        'transition-all duration-300 ease-out',
        'opacity-0 translate-y-2',
    ].join(' ');

    toast.innerHTML = `
        <div class="shrink-0 mt-0.5 h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 leading-snug line-clamp-3">${escapeHtml(message)}</p>
            ${redirectUrl ? '<p class="text-xs text-blue-600 mt-0.5">Click to view →</p>' : ''}
        </div>
        <button class="toast-close shrink-0 text-gray-400 hover:text-gray-600 ml-1 -mt-0.5"
                aria-label="Dismiss">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>`;

    container.prepend(toast);

    // Animate in
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', 'translate-y-2');
            toast.classList.add('opacity-100', 'translate-y-0');
        });
    });

    // Click on toast body → mark read + redirect
    toast.addEventListener('click', (e) => {
        if (e.target.closest('.toast-close')) return;
        dismissToast(toast);
        if (redirectUrl) {
            if (notifId) markNotificationRead(notifId);
            window.location.href = redirectUrl;
        }
    });

    // Close button
    toast.querySelector('.toast-close').addEventListener('click', (e) => {
        e.stopPropagation();
        dismissToast(toast);
    });

    // Auto-dismiss after 5 s
    const timer = setTimeout(() => dismissToast(toast), 5000);
    toast._dismissTimer = timer;
}

function dismissToast(toast) {
    clearTimeout(toast._dismissTimer);
    toast.classList.remove('opacity-100', 'translate-y-0');
    toast.classList.add('opacity-0', 'translate-y-2');
    setTimeout(() => toast.remove(), 300);
}

function getOrCreateToastContainer() {
    let c = document.getElementById('toast-container');
    if (!c) {
        c = document.createElement('div');
        c.id = 'toast-container';
        c.className = 'fixed bottom-5 right-5 z-[9999] flex flex-col gap-2 items-end';
        document.body.appendChild(c);
    }
    return c;
}

/* ─────────────────────────────────────────────────────────────────────────
   BADGE HELPERS
   ───────────────────────────────────────────────────────────────────────── */

function incrementBadge() {
    const badge = document.getElementById('notification-count');
    if (!badge) return;
    const current = parseInt(badge.textContent) || 0;
    badge.textContent = current + 1;
    badge.classList.remove('hidden');
}

/* ─────────────────────────────────────────────────────────────────────────
   CART NOTIFICATION SYSTEM
   ───────────────────────────────────────────────────────────────────────── */

/**
 * Display a cart success notification with checkmark icon
 */
function showCartSuccessToast(message) {
    const container = getOrCreateToastContainer();

    const toast = document.createElement('div');
    toast.className = [
        'toast-item',
        'flex items-start gap-3',
        'rounded-2xl shadow-lg border border-gray-200',
        'bg-white px-4 py-3 max-w-sm w-full',
        'cursor-pointer select-none',
        'transition-all duration-300 ease-out',
        'opacity-0 translate-y-2',
    ].join(' ');

    toast.innerHTML = `
        <div class="shrink-0 mt-0.5 h-8 w-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-900 leading-snug line-clamp-3">${escapeHtml(message)}</p>
        <button class="toast-close shrink-0 text-gray-400 hover:text-gray-600 ml-1 -mt-0.5"
                aria-label="Dismiss">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>`;

    container.prepend(toast);

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', 'translate-y-2');
            toast.classList.add('opacity-100', 'translate-y-0');
        });
    });

    toast.querySelector('.toast-close').addEventListener('click', (e) => {
        e.stopPropagation();
        dismissToast(toast);
    });

    const timer = setTimeout(() => dismissToast(toast), 5000);
    toast._dismissTimer = timer;
}

/**
 * Display a cart error notification with X icon
 */
function showCartErrorToast(message) {
    const container = getOrCreateToastContainer();

    const toast = document.createElement('div');
    toast.className = [
        'toast-item',
        'flex items-start gap-3',
        'rounded-2xl shadow-lg border border-gray-200',
        'bg-white px-4 py-3 max-w-sm w-full',
        'cursor-pointer select-none',
        'transition-all duration-300 ease-out',
        'opacity-0 translate-y-2',
    ].join(' ');

    toast.innerHTML = `
        <div class="shrink-0 mt-0.5 h-8 w-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-900 leading-snug line-clamp-3">${escapeHtml(message)}</p>
        <button class="toast-close shrink-0 text-gray-400 hover:text-gray-600 ml-1 -mt-0.5"
                aria-label="Dismiss">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>`;

    container.prepend(toast);

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', 'translate-y-2');
            toast.classList.add('opacity-100', 'translate-y-0');
        });
    });

    toast.querySelector('.toast-close').addEventListener('click', (e) => {
        e.stopPropagation();
        dismissToast(toast);
    });

    const timer = setTimeout(() => dismissToast(toast), 5000);
    toast._dismissTimer = timer;
}

/* ─────────────────────────────────────────────────────────────────────────
   AJAX HELPERS
   ───────────────────────────────────────────────────────────────────────── */

function markNotificationRead(id) {
    const csrf = (window.Laravel && window.Laravel.csrfToken)
        ? window.Laravel.csrfToken
        : document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    fetch('/notifications/mark-read', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body   : JSON.stringify({ id }),
    });
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

async function syncNotificationsFallback() {
    try {
        const response = await fetch('/notifications/latest', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (!response.ok) return;

        const payload = await response.json();
        const items = Array.isArray(payload.notifications) ? payload.notifications : [];

        for (const item of items) {
            if (!item?.id || hasNotificationInDom(item.id)) continue;

            if (typeof prependNotificationToDropdown === 'function') {
                prependNotificationToDropdown(
                    item.message || 'You have a new notification',
                    item.redirect_url || '#',
                    item.id
                );
            }

            if (typeof prependNotificationToPage === 'function') {
                prependNotificationToPage(
                    item.message || 'You have a new notification',
                    item.redirect_url || '#',
                    item.id,
                    item.type || 'general'
                );
            }

            if (typeof prependNotificationToDashboard === 'function') {
                prependNotificationToDashboard(
                    item.message || 'You have a new notification',
                    item.redirect_url || '#',
                    item.id,
                    item.type || 'general'
                );
            }

            incrementBadge();
        }
    } catch {
        // silent fallback
    }
}

function hasNotificationInDom(id) {
    const safeId = String(id).replace(/"/g, '\\"');
    return Boolean(
        document.querySelector(`[data-id="${safeId}"]`) ||
        document.querySelector(`[data-notification-id="${safeId}"]`)
    );
}

// Expose cart notification functions to window for global access
window.showCartSuccessToast = showCartSuccessToast;
window.showCartErrorToast = showCartErrorToast;
window.getOrCreateToastContainer = getOrCreateToastContainer;
window.dismissToast = dismissToast;
window.escapeHtml = escapeHtml;
