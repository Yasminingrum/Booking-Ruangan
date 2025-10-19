<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    /**
     * Display notification center for web interface
     */
    public function webIndex(Request $request)
    {
        $request->validate([
            'status' => ['nullable', Rule::in(['all', 'unread', 'read'])],
            'type' => ['nullable', Rule::in([
                Notification::TYPE_BOOKING_CREATED,
                Notification::TYPE_BOOKING_APPROVED,
                Notification::TYPE_BOOKING_REJECTED,
                Notification::TYPE_BOOKING_CANCELLED,
                Notification::TYPE_BOOKING_REMINDER,
            ])],
        ]);

        $filters = [
            'status' => $request->input('status', 'all'),
            'type' => $request->input('type'),
        ];

        $query = Auth::user()
            ->notifications()
            ->with('booking.room');

        if ($filters['status'] === 'unread') {
            $query->where('is_read', false);
        } elseif ($filters['status'] === 'read') {
            $query->where('is_read', true);
        }

        if ($filters['type']) {
            $query->where('type', $filters['type']);
        }

        $notifications = $query
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $statusCounts = Auth::user()
            ->notifications()
            ->select('is_read', DB::raw('count(*) as total'))
            ->groupBy('is_read')
            ->pluck('total', 'is_read');

        $summary = [
            'total' => $statusCounts->sum(),
            'unread' => (int) ($statusCounts[0] ?? 0),
            'read' => (int) ($statusCounts[1] ?? 0),
        ];

        $typeCounts = Auth::user()
            ->notifications()
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('notifications.index', [
            'notifications' => $notifications,
            'filters' => $filters,
            'summary' => $summary,
            'typeCounts' => $typeCounts,
        ]);
    }

    /**
     * Toggle read status for a single notification (web)
     */
    public function webToggleRead(Notification $notification)
    {
        $this->ensureOwnership($notification);

        $notification->update([
            'is_read' => !$notification->is_read,
        ]);

        $message = $notification->is_read
            ? 'Notifikasi ditandai sebagai sudah dibaca.'
            : 'Notifikasi dikembalikan sebagai belum dibaca.';

        return back()->with('status', $message);
    }

    /**
     * Mark all notifications as read (web)
     */
    public function webMarkAllAsRead()
    {
        $updated = Auth::user()
            ->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $message = $updated > 0
            ? "{$updated} notifikasi ditandai sebagai sudah dibaca."
            : 'Tidak ada notifikasi yang perlu diperbarui.';

        return back()->with('status', $message);
    }

    /**
     * Delete a notification (web)
     */
    public function webDestroy(Notification $notification)
    {
        $this->ensureOwnership($notification);
        $notification->delete();

        return back()->with('status', 'Notifikasi berhasil dihapus.');
    }

    /**
     * Delete all read notifications (web)
     */
    public function webDeleteAllRead()
    {
        $deleted = Auth::user()
            ->notifications()
            ->where('is_read', true)
            ->delete();

        $message = $deleted > 0
            ? "{$deleted} notifikasi yang sudah dibaca berhasil dihapus."
            : 'Tidak ada notifikasi yang sudah dibaca untuk dihapus.';

        return back()->with('status', $message);
    }

    /**
     * Ensure notification belongs to authenticated user
     */
    protected function ensureOwnership(Notification $notification): void
    {
        if ($notification->user_id !== Auth::id()) {
            abort(404);
        }
    }

    /**
     * Display a listing of notifications for authenticated user
     */
    public function index(Request $request)
    {
        $query = Auth::user()->notifications()
            ->with('booking.room');

        // Filter by read status
        if ($request->has('is_read')) {
            $query->where('is_read', $request->is_read);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->orderBy('created_at', 'desc')
                              ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ], 200);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        $count = Auth::user()->notifications()
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ], 200);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sebagai sudah dibaca',
            'data' => $notification,
        ], 200);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $updated = Auth::user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sebagai sudah dibaca',
            'data' => [
                'updated_count' => $updated,
            ],
        ], 200);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dihapus',
        ], 200);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        $deleted = Auth::user()->notifications()
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi yang sudah dibaca berhasil dihapus',
            'data' => [
                'deleted_count' => $deleted,
            ],
        ], 200);
    }

    /**
     * Get recent notifications (last 10)
     */
    public function recent()
    {
        $notifications = Auth::user()->notifications()
            ->with('booking.room')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ], 200);
    }
}
