<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

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
        try {
            $this->bookingService->updateBookingStatus(
                $booking->id,
                Booking::STATUS_APPROVED,
                Auth::id()
            );

            return back()->with('success', 'Peminjaman berhasil disetujui dan peminjam sudah menerima notifikasi email.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // Reject peminjaman (FR-14, FR-15):contentReference[oaicite:4]{index=4}
    public function reject(Booking $booking, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        try {
            $this->bookingService->updateBookingStatus(
                $booking->id,
                Booking::STATUS_REJECTED,
                Auth::id(),
                $request->reason
            );

            return back()->with('success', 'Peminjaman ditolak dan peminjam sudah menerima notifikasi email.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
