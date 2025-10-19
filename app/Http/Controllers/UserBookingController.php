<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebBookingRequest;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    /**
     * Show edit form for pending booking
     */
    public function edit(Booking $booking)
    {
        $this->abortIfNotOwner($booking);

        if ($booking->status !== Booking::STATUS_PENDING) {
            return redirect()
                ->route('bookings.history')
                ->withErrors(['booking' => 'Hanya pengajuan dengan status pending yang dapat diedit.']);
        }

        $rooms = Room::where('is_active', true)
            ->orderBy('name')
            ->get();

        $defaults = [
            'room_id' => old('room_id', $booking->room_id),
            'booking_date' => old('booking_date', optional($booking->booking_date)->format('Y-m-d')),
            'start_time' => old('start_time', substr($booking->start_time, 0, 5)),
            'end_time' => old('end_time', substr($booking->end_time, 0, 5)),
            'participants' => old('participants', $booking->participants),
            'purpose' => old('purpose', $booking->purpose),
        ];

        return view('bookings.edit', [
            'booking' => $booking,
            'rooms' => $rooms,
            'defaults' => $defaults,
        ]);
    }

    /**
     * Update booking submission from web form
     */
    public function update(WebBookingRequest $request, Booking $booking)
    {
        $this->abortIfNotOwner($booking);

        if ($booking->status !== Booking::STATUS_PENDING) {
            return redirect()
                ->route('bookings.history')
                ->withErrors(['booking' => 'Pengajuan tidak dapat diperbarui karena statusnya bukan pending.']);
        }

        $data = $request->validated();

        $startTime = strlen($data['start_time']) === 5 ? $data['start_time'] . ':00' : $data['start_time'];
        $endTime = strlen($data['end_time']) === 5 ? $data['end_time'] . ':00' : $data['end_time'];

        try {
            $this->bookingService->updateBookingDetails($booking, [
                'user_id' => Auth::id(),
                'room_id' => $data['room_id'],
                'booking_date' => $data['booking_date'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'purpose' => $data['purpose'],
                'participants' => $data['participants'],
            ]);

            return redirect()
                ->route('bookings.history')
                ->with('success', 'Pengajuan peminjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::warning('Failed to update booking via web', [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors(['booking' => $e->getMessage()]);
        }
    }

    /**
     * Cancel booking submission by borrower
     */
    public function destroy(Request $request, Booking $booking)
    {
        $this->abortIfNotOwner($booking);

        if (!in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_APPROVED], true)) {
            return back()->withErrors(['booking' => 'Pengajuan ini tidak dapat dibatalkan.']);
        }

        if ($booking->booking_date <= now()->toDateString()) {
            return back()->withErrors(['booking' => 'Pengajuan yang sudah dimulai atau telah lewat tidak dapat dibatalkan.']);
        }

        try {
            $this->bookingService->cancelBooking($booking->id, Auth::id());

            $admins = User::where('role', 'admin')
                ->where('is_active', true)
                ->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'booking_cancelled',
                    'title' => 'Peminjaman Dibatalkan',
                    'message' => Auth::user()->name . ' membatalkan peminjaman ' .
                                 $booking->room->name . ' pada ' .
                                 Carbon::parse($booking->booking_date)->format('d M Y'),
                    'related_booking_id' => $booking->id,
                ]);
            }

            return back()->with('success', 'Pengajuan peminjaman berhasil dibatalkan.');
        } catch (\Exception $e) {
            Log::warning('Failed to cancel booking via web', [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['booking' => $e->getMessage()]);
        }
    }

    /**
     * Show monthly booking calendar
     */
    public function calendar(Request $request)
    {
        $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'status' => ['nullable', Rule::in(['all', Booking::STATUS_PENDING, Booking::STATUS_APPROVED])],
            'room_id' => ['nullable', 'integer', Rule::exists('rooms', 'id')],
        ], [
            'room_id.exists' => 'Ruangan yang dipilih tidak ditemukan.',
        ]);

        $today = Carbon::today();

        $currentMonth = (int) $request->input('month', $today->month);
        $currentYear = (int) $request->input('year', $today->year);

        $currentDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfDay();
        $calendarStart = $currentDate->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $currentDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $filters = [
            'status' => $request->input('status', 'all'),
            'room_id' => $request->input('room_id'),
        ];

        $statusScope = [Booking::STATUS_PENDING, Booking::STATUS_APPROVED];

        $bookingQuery = Booking::with(['room:id,name', 'user:id,name'])
            ->whereBetween('booking_date', [$calendarStart->toDateString(), $calendarEnd->toDateString()])
            ->whereIn('status', $statusScope);

        if ($filters['status'] !== 'all') {
            $bookingQuery->where('status', $filters['status']);
        }

        if ($filters['room_id']) {
            $bookingQuery->where('room_id', $filters['room_id']);
        }

        $bookings = $bookingQuery
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        $bookingsByDate = $bookings->groupBy(fn (Booking $booking) => Carbon::parse($booking->booking_date)->toDateString());

        $cursor = $calendarStart->copy();
        $calendarDays = [];

        while ($cursor->lte($calendarEnd)) {
            $dateString = $cursor->toDateString();
            $calendarDays[] = [
                'date' => $cursor->copy(),
                'isCurrentMonth' => $cursor->month === $currentDate->month,
                'isToday' => $cursor->isSameDay($today),
                'bookings' => ($bookingsByDate[$dateString] ?? collect())
                    ->map(function (Booking $booking) {
                        return [
                            'id' => $booking->id,
                            'room' => $booking->room->name ?? 'Ruangan',
                            'purpose' => $booking->purpose,
                            'status' => $booking->status,
                            'time_range' => substr($booking->start_time, 0, 5) . '–' . substr($booking->end_time, 0, 5),
                            'user' => $booking->user->name ?? 'Tidak diketahui',
                        ];
                    })
                    ->values()
                    ->all(),
            ];
            $cursor->addDay();
        }

        $weeks = array_chunk($calendarDays, 7);

        $rooms = Room::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $summary = [
            'total' => $bookings->count(),
            'approved' => $bookings->where('status', Booking::STATUS_APPROVED)->count(),
            'pending' => $bookings->where('status', Booking::STATUS_PENDING)->count(),
        ];

        $previousDate = $currentDate->copy()->subMonth();
        $nextDate = $currentDate->copy()->addMonth();

        $navigation = [
            'current' => [
                'month' => $currentDate->month,
                'year' => $currentDate->year,
                'label' => $currentDate->translatedFormat('F Y'),
            ],
            'previous' => [
                'month' => $previousDate->month,
                'year' => $previousDate->year,
            ],
            'next' => [
                'month' => $nextDate->month,
                'year' => $nextDate->year,
            ],
            'today' => [
                'month' => $today->month,
                'year' => $today->year,
            ],
        ];

        $baseQuery = [
            'status' => $filters['status'],
        ];

        if ($filters['room_id']) {
            $baseQuery['room_id'] = $filters['room_id'];
        }

        return view('bookings.calendar', [
            'weeks' => $weeks,
            'filters' => $filters,
            'rooms' => $rooms,
            'summary' => $summary,
            'navigation' => $navigation,
            'baseQuery' => $baseQuery,
        ]);
    }

    /**
     * Show booking history for authenticated user
     */
    public function history(Request $request)
    {
        $request->validate([
            'status' => ['nullable', Rule::in([
                Booking::STATUS_PENDING,
                Booking::STATUS_APPROVED,
                Booking::STATUS_REJECTED,
                Booking::STATUS_CANCELLED,
                Booking::STATUS_COMPLETED,
            ])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ], [
            'date_to.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
        ]);

        $filters = [
            'status' => $request->input('status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $query = Auth::user()
            ->bookings()
            ->with('room');

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['date_from']) {
            $query->where('booking_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->where('booking_date', '<=', $filters['date_to']);
        }

        $bookings = $query
            ->orderByDesc('booking_date')
            ->orderByDesc('start_time')
            ->paginate(10)
            ->withQueryString();

        $statusCounts = Auth::user()
            ->bookings()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statistics = [
            'total' => $statusCounts->sum(),
            'pending' => (int) ($statusCounts[Booking::STATUS_PENDING] ?? 0),
            'approved' => (int) ($statusCounts[Booking::STATUS_APPROVED] ?? 0),
            'rejected' => (int) ($statusCounts[Booking::STATUS_REJECTED] ?? 0),
            'cancelled' => (int) ($statusCounts[Booking::STATUS_CANCELLED] ?? 0),
            'completed' => (int) ($statusCounts[Booking::STATUS_COMPLETED] ?? 0),
        ];

        $statusOptions = [
            '' => 'Semua Status',
            Booking::STATUS_PENDING => 'Menunggu',
            Booking::STATUS_APPROVED => 'Disetujui',
            Booking::STATUS_REJECTED => 'Ditolak',
            Booking::STATUS_CANCELLED => 'Dibatalkan',
            Booking::STATUS_COMPLETED => 'Selesai',
        ];

        return view('bookings.history', [
            'bookings' => $bookings,
            'filters' => $filters,
            'statistics' => $statistics,
            'statusOptions' => $statusOptions,
        ]);
    }

    protected function abortIfNotOwner(Booking $booking): void
    {
        if ($booking->user_id !== Auth::id()) {
            abort(404);
        }
    }
}
