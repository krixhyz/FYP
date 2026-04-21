<?php

namespace App\Http\Controllers\User;

use App\Models\SwapRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    /**
     * Lightweight latest notifications endpoint for live UI sync fallback.
     */
    public function latest(Request $request)
    {
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($notification) {
                $user = Auth::user();

                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'general',
                    'message' => $notification->data['message'] ?? 'Notification',
                    'redirect_url' => $this->resolveRedirectUrl($notification, $user),
                    'created_at_human' => $notification->created_at?->diffForHumans(),
                    'read_at' => $notification->read_at,
                ];
            })
            ->values();

        return response()->json([
            'unread_count' => Auth::user()->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Paginated notifications page.
     */
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a single notification as read (AJAX).
     */
    public function markRead(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['status' => 'missing_id'], 422);
        }

        $notification = Auth::user()->unreadNotifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['status' => 'ok']);
        }

        // Already read – still return ok so the UI stays consistent.
        return response()->json(['status' => 'ok']);
    }

    /**
     * Mark all notifications as read (AJAX).
     */
    public function markAllRead(Request $request)
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()->route('notifications.index');
    }

    private function resolveRedirectUrl($notification, $user): string
    {
        $fallbackUrl = $notification->data['redirect_url'] ?? '#';
        $type = $notification->data['type'] ?? 'general';
        $swapRequestId = $notification->data['swap_request_id'] ?? null;

        if (!$swapRequestId || !in_array($type, ['swap', 'swapAccept', 'swapCounter', 'swapReject'], true)) {
            return $fallbackUrl;
        }

        $swapRequest = SwapRequest::find($swapRequestId);
        if (!$swapRequest) {
            return route('notifications.index');
        }

        if ($type !== 'swapAccept') {
            return route('swap.request.show', $swapRequest->id);
        }

        $payerId = match ($swapRequest->money_direction) {
            'requester_offers_cash' => $swapRequest->requester_id,
            'owner_asks_cash' => $swapRequest->owner_id,
            default => null,
        };

        if ($payerId && (int) $user->id === (int) $payerId && $swapRequest->status === 'awaiting_payment') {
            return route('swap.checkout', $swapRequest->id);
        }

        return route('swap.request.show', $swapRequest->id);
    }
}
