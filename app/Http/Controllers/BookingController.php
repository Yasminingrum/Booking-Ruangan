<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * FR-09: Display all bookings for authenticated user
     */
    public function index(Request $request)
    {
        $query = Auth::user()->bookings()->with(['room']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('booking_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('booking_date', '<=', $request->date_to);
        }

        $bookings = $query->orderBy('booking_date', 'desc')
                          ->orderBy('start_time', 'desc')
                          ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ], 200);
    }

    /**
     * Get upcoming bookings for user
     */
    public function upcoming()
    {
        $bookings = Auth::user()->bookings()
            ->with(['room'])
            ->where('booking_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ], 200);
    }

    /**
     * FR-10: Display the specified booking
     */
    public function show($id)
    {
        $booking = Booking::with(['room', 'user', 'approver', 'histories.changedBy'])
            ->findOrFail($id);

        // Check authorization
        if (Auth::id() !== $booking->user_id && !Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke booking ini.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $booking,
        ], 200);
    }

    /**
     * FR-05, FR-06, FR-07: Store a newly created booking
     * Middleware ValidateBookingConflict akan handle validasi konflik
     */
    public function store(BookingRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Create booking
            $booking = Booking::create([
                'user_id' => Auth::id(),
                'room_id' => $data['room_id'],
                'booking_date' => $data['booking_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'purpose' => $data['purpose'],
                'participants' => $data['participants'],
                'is_recurring' => $data['is_recurring'] ?? false,
                'recurring_pattern' => $data['recurring_pattern'] ?? null,
                'status' => 'pending',
            ]);

            // Create booking history
            BookingHistory::create([
                'booking_id' => $booking->id,
                'changed_by_user_id' => Auth::id(),
                'old_status' => null,
                'new_status' => 'pending',
                'notes' => 'Booking dibuat',
            ]);

            // Send notification to all admins
            $admins = User::where('role', 'admin')->where('is_active', true)->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'booking_created',
                    'title' => 'Pengajuan Peminjaman Baru',
                    'message' => Auth::user()->name . ' mengajukan peminjaman ' .
                                 $booking->room->name . ' pada ' .
                                 Carbon::parse($booking->booking_date)->format('d M Y'),
                    'related_booking_id' => $booking->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan peminjaman berhasil dibuat. Menunggu persetujuan admin.',
                'data' => $booking->load('room'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pengajuan peminjaman.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update booking (User can only update pending bookings)
     */
    public function update(BookingRequest $request, $id)
    {
        $booking = Booking::findOrFail($id);

        // Check authorization
        if (Auth::id() !== $booking->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengubah booking ini.',
            ], 403);
        }

        // Can only update pending bookings
        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya booking dengan status pending yang dapat diubah.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $oldData = $booking->getOriginal();

            $booking->update($request->validated());

            // Create history if there are changes
            BookingHistory::create([
                'booking_id' => $booking->id,
                'changed_by_user_id' => Auth::id(),
                'old_status' => 'pending',
                'new_status' => 'pending',
                'notes' => 'Booking diperbarui',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil diperbarui.',
                'data' => $booking->load('room'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui booking.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel booking by user
     */
    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);

        // Check authorization
        if (Auth::id() !== $booking->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk membatalkan booking ini.',
            ], 403);
        }

        // Can only cancel pending or approved bookings
        if (!in_array($booking->status, ['pending', 'approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'Booking ini tidak dapat dibatalkan.',
            ], 422);
        }

        // Cannot cancel if booking date is in the past or today
        if ($booking->booking_date <= now()->toDateString()) {
            return response()->json([
                'success' => false,
                'message' => 'Booking yang sudah dimulai atau telah lewat tidak dapat dibatalkan.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $oldStatus = $booking->status;
            $booking->cancel(Auth::id());

            // Notify admin about cancellation
            $admins = User::where('role', 'admin')->where('is_active', true)->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'booking_cancelled',
                    'title' => 'Peminjaman Dibatalkan',
                    'message' => Auth::user()->name . ' membatalkan peminjaman ' .
                                 $booking->room->name . ' pada ' .
                                 \Carbon\Carbon::parse($booking->booking_date)->format('d M Y'),
                    'related_booking_id' => $booking->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dibatalkan.',
                'data' => $booking,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan booking.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * FR-12: Get all bookings (Admin only)
     */
    public function adminIndex(Request $request)
    {
        $query = Booking::with(['room', 'user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by room
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('booking_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('booking_date', '<=', $request->date_to);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $bookings = $query->orderBy('booking_date', 'desc')
                          ->orderBy('start_time', 'desc')
                          ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ], 200);
    }

    /**
     * FR-13: Approve booking (Admin only)
     */
    public function approve($id)
    {
        $booking = Booking::with(['room', 'user'])->findOrFail($id);

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya booking dengan status pending yang dapat disetujui.',
            ], 422);
        }

        // Validate conflict again (safety check)
        if (!$booking->validateConflict($booking->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menyetujui. Terdapat konflik jadwal dengan booking lain.',
            ], 409);
        }

        DB::beginTransaction();

        try {
            $booking->approve(Auth::id());

            // Send notification to user
            Notification::create([
                'user_id' => $booking->user_id,
                'type' => 'booking_approved',
                'title' => 'Peminjaman Disetujui',
                'message' => 'Peminjaman ' . $booking->room->name . ' pada ' .
                             \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') .
                             ' telah disetujui.',
                'related_booking_id' => $booking->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil disetujui.',
                'data' => $booking,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui booking.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * FR-14: Reject booking (Admin only)
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan harus diisi.',
            'rejection_reason.min' => 'Alasan penolakan minimal 10 karakter.',
        ]);

        $booking = Booking::with(['room', 'user'])->findOrFail($id);

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya booking dengan status pending yang dapat ditolak.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $booking->reject(Auth::id(), $request->rejection_reason);

            // Send notification to user
            Notification::create([
                'user_id' => $booking->user_id,
                'type' => 'booking_rejected',
                'title' => 'Peminjaman Ditolak',
                'message' => 'Peminjaman ' . $booking->room->name . ' pada ' .
                             \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') .
                             ' ditolak. Alasan: ' . $request->rejection_reason,
                'related_booking_id' => $booking->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil ditolak.',
                'data' => $booking,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak booking.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * FR-11: Get booking history
     */
    public function history($id)
    {
        $booking = Booking::findOrFail($id);

        // Check authorization
        if (Auth::id() !== $booking->user_id && !Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke riwayat booking ini.',
            ], 403);
        }

        $histories = $booking->histories()
            ->with('changedBy:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'booking' => $booking,
                'histories' => $histories,
            ],
        ], 200);
    }
}
