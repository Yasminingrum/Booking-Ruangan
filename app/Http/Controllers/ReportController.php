<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * FR-18: Display dashboard summary
     * Statistik ringkasan untuk Admin/Kepala Sekolah
     */
    public function dashboard(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year

        $dateFrom = match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $dateTo = now()->endOfDay();

        // Total bookings statistics
        $totalBookings = Booking::whereBetween('booking_date', [$dateFrom, $dateTo])->count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $approvedBookings = Booking::where('status', 'approved')
            ->whereBetween('booking_date', [$dateFrom, $dateTo])
            ->count();
        $rejectedBookings = Booking::where('status', 'rejected')
            ->whereBetween('booking_date', [$dateFrom, $dateTo])
            ->count();

        // Most popular room
        $mostPopularRoom = Booking::select('room_id', DB::raw('COUNT(*) as total'))
            ->whereBetween('booking_date', [$dateFrom, $dateTo])
            ->whereIn('status', ['approved', 'completed'])
            ->groupBy('room_id')
            ->orderBy('total', 'desc')
            ->with('room')
            ->first();

        // Room utilization average
        $totalRooms = Room::where('is_active', true)->count();
        $utilizationPercentage = $totalRooms > 0
            ? round(($approvedBookings / ($totalRooms * 30)) * 100, 2)
            : 0;

        // Booking trends (last 7 days)
        $bookingTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = Booking::whereDate('booking_date', $date)
                ->whereIn('status', ['approved', 'completed'])
                ->count();

            $bookingTrends[] = [
                'date' => $date,
                'count' => $count,
            ];
        }

        // Top users
        $topUsers = User::withCount(['bookings' => function($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('booking_date', [$dateFrom, $dateTo])
                      ->whereIn('status', ['approved', 'completed']);
            }])
            ->where('role', 'peminjam')
            ->orderBy('bookings_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_bookings' => $totalBookings,
                    'pending_approval' => $pendingBookings,
                    'approved_bookings' => $approvedBookings,
                    'rejected_bookings' => $rejectedBookings,
                    'utilization_percentage' => $utilizationPercentage,
                ],
                'most_popular_room' => $mostPopularRoom ? [
                    'room' => $mostPopularRoom->room,
                    'total_bookings' => $mostPopularRoom->total,
                ] : null,
                'booking_trends' => $bookingTrends,
                'top_users' => $topUsers,
                'period' => $period,
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
            ],
        ], 200);
    }

    /**
     * FR-18: Generate detailed report
     * Laporan detail untuk periode tertentu
     */
    public function detailReport(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'room_id' => 'nullable|exists:rooms,id',
            'status' => 'nullable|in:pending,approved,rejected,cancelled,completed',
        ]);

        $query = Booking::with(['room', 'user'])
            ->whereBetween('booking_date', [$request->date_from, $request->date_to]);

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('booking_date')
                          ->orderBy('start_time')
                          ->get();

        // Calculate statistics
        $statistics = [
            'total_bookings' => $bookings->count(),
            'by_status' => [
                'pending' => $bookings->where('status', 'pending')->count(),
                'approved' => $bookings->where('status', 'approved')->count(),
                'rejected' => $bookings->where('status', 'rejected')->count(),
                'cancelled' => $bookings->where('status', 'cancelled')->count(),
                'completed' => $bookings->where('status', 'completed')->count(),
            ],
            'by_room' => $bookings->groupBy('room_id')->map(function($items) {
                return [
                    'room' => $items->first()->room,
                    'count' => $items->count(),
                ];
            })->values(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'bookings' => $bookings,
                'statistics' => $statistics,
                'filters' => [
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to,
                    'room_id' => $request->room_id,
                    'status' => $request->status,
                ],
            ],
        ], 200);
    }

    /**
     * FR-18: Room utilization report
     * Laporan penggunaan per ruangan
     */
    public function roomUtilization(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $rooms = Room::where('is_active', true)->get();

        $utilization = [];

        foreach ($rooms as $room) {
            // Count approved/completed bookings
            $totalBookings = Booking::where('room_id', $room->id)
                ->whereBetween('booking_date', [$request->date_from, $request->date_to])
                ->whereIn('status', ['approved', 'completed'])
                ->count();

            // Calculate total hours used
            $totalHours = Booking::where('room_id', $room->id)
                ->whereBetween('booking_date', [$request->date_from, $request->date_to])
                ->whereIn('status', ['approved', 'completed'])
                ->get()
                ->sum(function($booking) {
                    $start = Carbon::parse($booking->start_time);
                    $end = Carbon::parse($booking->end_time);
                    return $start->diffInHours($end);
                });

            // Calculate available hours (assuming 8 hours per day)
            $dateFrom = Carbon::parse($request->date_from);
            $dateTo = Carbon::parse($request->date_to);
            $totalDays = $dateFrom->diffInDays($dateTo) + 1;
            $availableHours = $totalDays * 8; // 8 working hours per day

            $utilizationPercentage = $availableHours > 0
                ? round(($totalHours / $availableHours) * 100, 2)
                : 0;

            $utilization[] = [
                'room' => $room,
                'total_bookings' => $totalBookings,
                'total_hours' => $totalHours,
                'available_hours' => $availableHours,
                'utilization_percentage' => $utilizationPercentage,
            ];
        }

        // Sort by utilization percentage
        usort($utilization, function($a, $b) {
            return $b['utilization_percentage'] <=> $a['utilization_percentage'];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'utilization' => $utilization,
                'period' => [
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to,
                    'total_days' => $totalDays ?? 0,
                ],
            ],
        ], 200);
    }

    /**
     * FR-19: Export report to PDF
     */
    public function exportPDF(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'type' => 'required|in:detail,utilization,summary',
        ]);

        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);

        $data = [
            'date_from' => $dateFrom->format('d M Y'),
            'date_to' => $dateTo->format('d M Y'),
            'generated_at' => now()->format('d M Y H:i'),
            'generated_by' => auth::user()->name,
        ];

        if ($request->type === 'detail') {
            $bookings = Booking::with(['room', 'user'])
                ->whereBetween('booking_date', [$request->date_from, $request->date_to])
                ->whereIn('status', ['approved', 'completed'])
                ->orderBy('booking_date')
                ->orderBy('start_time')
                ->get();

            $data['bookings'] = $bookings;
            $data['total'] = $bookings->count();

            $pdf = Pdf::loadView('reports.detail-pdf', $data);
            return $pdf->download('laporan-detail-peminjaman-' . $dateFrom->format('Y-m-d') . '-to-' . $dateTo->format('Y-m-d') . '.pdf');
        }

        if ($request->type === 'utilization') {
            $rooms = Room::where('is_active', true)->get();
            $utilization = [];

            foreach ($rooms as $room) {
                $totalBookings = Booking::where('room_id', $room->id)
                    ->whereBetween('booking_date', [$request->date_from, $request->date_to])
                    ->whereIn('status', ['approved', 'completed'])
                    ->count();

                $totalHours = Booking::where('room_id', $room->id)
                    ->whereBetween('booking_date', [$request->date_from, $request->date_to])
                    ->whereIn('status', ['approved', 'completed'])
                    ->get()
                    ->sum(function($booking) {
                        $start = Carbon::parse($booking->start_time);
                        $end = Carbon::parse($booking->end_time);
                        return $start->diffInHours($end);
                    });

                $totalDays = $dateFrom->diffInDays($dateTo) + 1;
                $availableHours = $totalDays * 8;
                $utilizationPercentage = $availableHours > 0
                    ? round(($totalHours / $availableHours) * 100, 2)
                    : 0;

                $utilization[] = [
                    'room' => $room,
                    'total_bookings' => $totalBookings,
                    'total_hours' => $totalHours,
                    'utilization_percentage' => $utilizationPercentage,
                ];
            }

            $data['utilization'] = $utilization;

            $pdf = Pdf::loadView('reports.utilization-pdf', $data);
            return $pdf->download('laporan-utilisasi-ruangan-' . $dateFrom->format('Y-m-d') . '-to-' . $dateTo->format('Y-m-d') . '.pdf');
        }

        if ($request->type === 'summary') {
            $summary = [
                'total_bookings' => Booking::whereBetween('booking_date', [$request->date_from, $request->date_to])->count(),
                'approved' => Booking::whereBetween('booking_date', [$request->date_from, $request->date_to])
                    ->where('status', 'approved')->count(),
                'rejected' => Booking::whereBetween('booking_date', [$request->date_from, $request->date_to])
                    ->where('status', 'rejected')->count(),
                'cancelled' => Booking::whereBetween('booking_date', [$request->date_from, $request->date_to])
                    ->where('status', 'cancelled')->count(),
            ];

            $data['summary'] = $summary;

            $pdf = Pdf::loadView('reports.summary-pdf', $data);
            return $pdf->download('laporan-ringkasan-' . $dateFrom->format('Y-m-d') . '-to-' . $dateTo->format('Y-m-d') . '.pdf');
        }
    }

    /**
     * Export report to Excel (optional feature)
     */
    public function exportExcel(Request $request)
    {
        // TODO: Implement Excel export using Maatwebsite\Excel
        return response()->json([
            'success' => false,
            'message' => 'Excel export belum diimplementasikan',
        ], 501);
    }
}
