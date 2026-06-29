<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Return unread count + latest notifications as JSON (used by polling).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = Notification::forUser($user->id)
            ->with('ticket:id,title')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'message'    => $n->message,
                'ticket_id'  => $n->ticket_id,
                'ticket_url' => $n->ticket_id ? route('tickets.show', $n->ticket_id) : null,
                'read'       => ! is_null($n->read_at),
                'time'       => $n->created_at->diffForHumans(),
            ]);

        $unreadCount = Notification::forUser($user->id)->unread()->count();

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json(['status' => 'ok']);
    }

    /**
     * Mark all notifications as read for current user.
     */
    public function readAll(Request $request): JsonResponse
    {
        Notification::forUser($request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'ok']);
    }
}
