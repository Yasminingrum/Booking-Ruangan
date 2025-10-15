<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
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
