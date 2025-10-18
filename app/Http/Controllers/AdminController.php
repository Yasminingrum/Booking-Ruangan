<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\Notification;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Dashboard ringkasan + recent bookings
    public function dashboard()
    {
        $totalUsers       = User::count();
        $totalRuangan     = Room::count();
        $totalPeminjaman  = Booking::count();

        $recentBookings = Booking::with(['user', 'room'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        $pendingCount  = Booking::where('status', 'pending')->count();
        $approvedCount = Booking::where('status', 'approved')->count();
        $rejectedCount = Booking::where('status', 'rejected')->count();

        return view('admin.dashboard', compact(
            'totalUsers','totalRuangan','totalPeminjaman',
            'recentBookings','pendingCount','approvedCount','rejectedCount'
        ));
    }

    // Daftar pengajuan "Pending" (FR-12):contentReference[oaicite:1]{index=1}
    public function pending(Request $request)
    {
        $bookings = Booking::with(['user','room'])
            ->where('status', 'pending')
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->paginate(20);

        return view('admin.pending', compact('bookings'));
    }

    // Approve peminjaman (FR-13, FR-15):contentReference[oaicite:2]{index=2}
    public function approve(Booking $booking, Request $request)
    {
        // Safety check konflik jadwal (FR-15):contentReference[oaicite:3]{index=3}
        $conflict = Booking::where('room_id', $booking->room_id)
            ->where('booking_date', $booking->booking_date)
            ->where('id', '<>', $booking->id)
            ->whereIn('status', ['approved','pending'])
            ->where(function($q) use ($booking) {
                $q->whereBetween('start_time', [$booking->start_time, $booking->end_time])
                  ->orWhereBetween('end_time', [$booking->start_time, $booking->end_time])
                  ->orWhere(function($q2) use ($booking) {
                      $q2->where('start_time', '<=', $booking->start_time)
                         ->where('end_time',   '>=', $booking->end_time);
                  });
            })->exists();

        if ($conflict) {
            return back()->with('error', 'Tidak bisa approve: terdapat konflik jadwal baru.');
        }

        DB::transaction(function () use ($booking) {
            $old = $booking->status;
            $booking->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            BookingHistory::create([
                'booking_id' => $booking->id,
                'changed_by_user_id' => Auth::id(),
                'old_status' => $old,
                'new_status' => 'approved',
                'notes' => 'Disetujui oleh admin',
            ]);

            Notification::create([
                'user_id' => $booking->user_id,
                'type' => 'booking_approved',
                'title' => 'Peminjaman Disetujui',
                'message' => 'Peminjaman ruangan ' . ($booking->room->name ?? '-') .
                             ' pada ' . $booking->booking_date . ' telah disetujui.',
            ]);
        });

        return back()->with('success', 'Peminjaman berhasil disetujui.');
    }

    // Reject peminjaman (FR-14, FR-15):contentReference[oaicite:4]{index=4}
    public function reject(Booking $booking, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($booking, $request) {
            $old = $booking->status;

            $booking->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            BookingHistory::create([
                'booking_id' => $booking->id,
                'changed_by_user_id' => Auth::id(),
                'old_status' => $old,
                'new_status' => 'rejected',
                'notes' => 'Alasan: '.$request->reason,
            ]);

            Notification::create([
                'user_id' => $booking->user_id,
                'type' => 'booking_rejected',
                'title' => 'Peminjaman Ditolak',
                'message' => 'Peminjaman ruangan ' . ($booking->room->name ?? '-') .
                             ' pada ' . $booking->booking_date . ' ditolak. Alasan: ' . $request->reason,
            ]);
        });

        return back()->with('success', 'Peminjaman ditolak.');
    }
}
