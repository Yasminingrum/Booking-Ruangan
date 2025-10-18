<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebBookingRequest;
use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserBookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Show booking form for selected room
     */
    public function create(Request $request, Room $room)
    {
        if (!$room->is_active) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['booking' => 'Ruangan tidak tersedia untuk peminjaman saat ini.']);
        }

        $prefill = [
            'date' => $request->input('date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
        ];

        return view('bookings.create', [
            'room' => $room,
            'prefill' => $prefill,
        ]);
    }

    /**
     * Store booking submission from web form
     */
    public function store(WebBookingRequest $request)
    {
        $data = $request->validated();

        $startTime = $data['start_time'];
        $endTime = $data['end_time'];

        if (strlen($startTime) === 5) {
            $startTime .= ':00';
        }

        if (strlen($endTime) === 5) {
            $endTime .= ':00';
        }

        try {
            $booking = $this->bookingService->createBooking([
                'user_id' => Auth::id(),
                'room_id' => $data['room_id'],
                'booking_date' => $data['booking_date'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'purpose' => $data['purpose'],
                'participants' => $data['participants'],
                'is_recurring' => false,
                'recurring_pattern' => null,
            ]);

            return redirect()
                ->route('dashboard')
                ->with('success', 'Pengajuan peminjaman berhasil dikirim dan menunggu persetujuan admin.');
        } catch (\Exception $e) {
            Log::warning('Web booking failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'room_id' => $data['room_id'] ?? null,
            ]);

            return back()
                ->withInput()
                ->withErrors(['booking' => $e->getMessage()]);
        }
    }
}
