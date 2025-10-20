<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingsExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class ReportController extends Controller
{
    private const DAILY_OPERATIONAL_HOURS = 12;

    public const ROOM_TYPE_LABELS = [
        Room::TYPE_LABORATORIUM => 'Laboratorium',
        Room::TYPE_RUANG_MUSIK => 'Ruang Musik',
        Room::TYPE_AUDIO_VISUAL => 'Audio Visual',
        Room::TYPE_LAPANGAN_BASKET => 'Lapangan Basket',
        Room::TYPE_KOLAM_RENANG => 'Kolam Renang',
    ];

    /**
     * Dashboard visual untuk Kepala Sekolah
     */
    public function index(Request $request)
    {
        $period = $request->input('period', 'month');
    $category = $request->input('category');
    $knownCategories = array_keys(self::ROOM_TYPE_LABELS);
    $isOtherCategory = $category === 'other';

        $defaultStart = match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $defaultEnd = match ($period) {
            'day' => now()->endOfDay(),
            'week' => now()->endOfWeek(),
            'year' => now()->endOfYear(),
            default => now()->endOfMonth(),
        };

        $dateFrom = $this->parseDate($request->input('date_from'), $defaultStart)->startOfDay();
        $dateTo = $this->parseDate($request->input('date_to'), $defaultEnd)->endOfDay();

        if ($dateFrom->greaterThan($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        $roomsQuery = Room::active();
        if ($category) {
            if ($isOtherCategory) {
                $roomsQuery->where(function ($query) use ($knownCategories) {
                    $query->whereNull('type')
                          ->orWhereNotIn('type', $knownCategories);
                });
            } else {
                $roomsQuery->where('type', $category);
            }
        }
        $rooms = $roomsQuery->orderBy('name')->get();

        $bookingsBaseQuery = Booking::with(['room', 'user'])
            ->whereBetween('booking_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->when($category, function ($query) use ($category, $isOtherCategory, $knownCategories) {
                $query->whereHas('room', function ($roomQuery) use ($category, $isOtherCategory, $knownCategories) {
                    if ($isOtherCategory) {
                        $roomQuery->where(function ($sub) use ($knownCategories) {
                            $sub->whereNull('type')
                                ->orWhereNotIn('type', $knownCategories);
                        });
                    } else {
                        $roomQuery->where('type', $category);
                    }
                });
            });

        $totalBookings = (clone $bookingsBaseQuery)->count();
        $pendingApproval = (clone $bookingsBaseQuery)->where('status', Booking::STATUS_PENDING)->count();
        $approvedBookings = (clone $bookingsBaseQuery)->where('status', Booking::STATUS_APPROVED)->count();

        $popularRoom = (clone $bookingsBaseQuery)
            ->select('room_id', DB::raw('COUNT(*) as total'))
            ->whereIn('status', [Booking::STATUS_APPROVED, Booking::STATUS_COMPLETED])
            ->groupBy('room_id')
            ->orderByDesc('total')
            ->with('room')
            ->first();

        $daysRange = $dateFrom->diffInDays($dateTo) + 1;
        $availableHoursTotal = max(1, ($rooms->count() ?: Room::active()->count()) * $daysRange * self::DAILY_OPERATIONAL_HOURS);

        $roomStats = $rooms->map(function (Room $room) use ($dateFrom, $dateTo) {
            $bookings = $room->bookings()
                ->whereBetween('booking_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
                ->whereIn('status', [Booking::STATUS_APPROVED, Booking::STATUS_COMPLETED])
                ->get();

            $totalBookings = $bookings->count();
            $totalMinutes = $bookings->sum(function (Booking $booking) {
                $start = Carbon::parse($booking->start_time);
                $end = Carbon::parse($booking->end_time);
                return max(0, $start->diffInMinutes($end));
            });

            $totalHours = round($totalMinutes / 60, 2);
            $daysRange = max(1, Carbon::parse($dateFrom)->diffInDays($dateTo) + 1);
            $availableHours = $daysRange * self::DAILY_OPERATIONAL_HOURS;
            $utilization = $availableHours > 0
                ? round(($totalHours / $availableHours) * 100, 2)
                : 0;

            return [
                'room' => $room,
                'total_bookings' => $totalBookings,
                'total_hours' => $totalHours,
                'available_hours' => $availableHours,
                'utilization' => $utilization,
            ];
        });

        $totalHoursUsed = $roomStats->sum('total_hours');
        $averageUtilization = $availableHoursTotal > 0
            ? round(($totalHoursUsed / $availableHoursTotal) * 100, 2)
            : 0;

        $utilizationChart = $roomStats->map(function ($stat) {
            return [
                'name' => $stat['room']->name,
                'value' => $stat['utilization'],
            ];
        })->values();

        $bookingsForDistribution = (clone $bookingsBaseQuery)
            ->whereIn('status', [Booking::STATUS_APPROVED, Booking::STATUS_COMPLETED])
            ->get();

        $hourDistribution = $this->calculateHourDistribution($bookingsForDistribution);

        $categorySummary = $roomStats
            ->groupBy(fn ($stat) => $stat['room']->type)
            ->map(function ($items, $type) {
                $totalBookings = $items->sum('total_bookings');
                $totalHours = $items->sum('total_hours');
                $avgUtilization = $items->count() ? round($items->avg('utilization'), 2) : 0;

                return [
                    'category' => self::ROOM_TYPE_LABELS[$type] ?? ucfirst(str_replace('_', ' ', $type ?? 'Lainnya')),
                    'total_bookings' => $totalBookings,
                    'total_hours' => $totalHours,
                    'avg_utilization' => $avgUtilization,
                ];
            })
            ->values();

        $filters = [
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'period' => $period,
            'category' => $category,
        ];

        $categoryOptions = collect(['' => 'Semua Kategori'])
            ->merge(self::ROOM_TYPE_LABELS)
            ->merge(['other' => 'Kategori Lainnya']);

        return view('headmaster.dashboard', [
            'filters' => $filters,
            'totalBookings' => $totalBookings,
            'pendingApproval' => $pendingApproval,
            'popularRoom' => $popularRoom,
            'averageUtilization' => $averageUtilization,
            'utilizationChart' => $utilizationChart,
            'hourDistribution' => $hourDistribution,
            'roomStats' => $roomStats,
            'categorySummary' => $categorySummary,
            'categoryOptions' => $categoryOptions,
            'periodLabel' => $this->buildPeriodLabel($dateFrom, $dateTo),
        ]);
    }

    private function parseDate(?string $value, Carbon $fallback): Carbon
    {
        try {
            return $value ? Carbon::parse($value) : $fallback->copy();
        } catch (\Exception $e) {
            return $fallback->copy();
        }
    }

    private function buildPeriodLabel(Carbon $from, Carbon $to): string
    {
        if ($from->isSameDay($to)) {
            return $from->translatedFormat('d M Y');
        }

        return sprintf('%s – %s',
            $from->translatedFormat('d M Y'),
            $to->translatedFormat('d M Y')
        );
    }

    private function calculateHourDistribution($bookings): array
    {
        $buckets = [
            'Pagi (07-11)' => 0,
            'Siang (11-15)' => 0,
            'Sore (15-18)' => 0,
            'Malam (18-22)' => 0,
        ];

        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->start_time ?? '07:00');
            $end = Carbon::parse($booking->end_time ?? '08:00');
            $minutes = max(30, $start->diffInMinutes($end));

            $hour = (int) $start->format('H');

            if ($hour < 11) {
                $buckets['Pagi (07-11)'] += $minutes;
            } elseif ($hour < 15) {
                $buckets['Siang (11-15)'] += $minutes;
            } elseif ($hour < 18) {
                $buckets['Sore (15-18)'] += $minutes;
            } else {
                $buckets['Malam (18-22)'] += $minutes;
            }
        }

        $total = array_sum($buckets) ?: 1;

        return collect($buckets)->map(function ($minutes, $label) use ($total) {
            $hours = round($minutes / 60, 1);
            $percentage = round(($minutes / $total) * 100, 1);

            return [
                'label' => $label,
                'hours' => $hours,
                'percentage' => $percentage,
            ];
        })->values()->toArray();
    }

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
            'type' => 'required|in:detail,summary',
        ]);

        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);
        $category = $request->input('category');

        // Ambil data roomStats menggunakan logic yang sama dengan index()
        $roomsQuery = Room::active();
        if ($category && $category !== '') {
            if ($category === 'other') {
                $knownCategories = array_keys(self::ROOM_TYPE_LABELS);
                $roomsQuery->where(function ($query) use ($knownCategories) {
                    $query->whereNull('type')
                          ->orWhereNotIn('type', $knownCategories);
                });
            } else {
                $roomsQuery->where('type', $category);
            }
        }
        $rooms = $roomsQuery->orderBy('name')->get();

        $roomStats = $rooms->map(function (Room $room) use ($dateFrom, $dateTo) {
            $bookings = $room->bookings()
                ->whereBetween('booking_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
                ->whereIn('status', [Booking::STATUS_APPROVED, Booking::STATUS_COMPLETED])
                ->get();

            $totalBookings = $bookings->count();
            $totalMinutes = $bookings->sum(function (Booking $booking) {
                $start = Carbon::parse($booking->start_time);
                $end = Carbon::parse($booking->end_time);
                return max(0, $start->diffInMinutes($end));
            });

            $totalHours = round($totalMinutes / 60, 2);
            $daysRange = max(1, $dateFrom->diffInDays($dateTo) + 1);
            $availableHours = $daysRange * self::DAILY_OPERATIONAL_HOURS;
            $utilization = $availableHours > 0
                ? round(($totalHours / $availableHours) * 100, 2)
                : 0;

            return [
                'room' => $room,
                'total_bookings' => $totalBookings,
                'total_hours' => $totalHours,
                'available_hours' => $availableHours,
                'utilization' => $utilization,
            ];
        });

        $data = [
            'date_from' => $dateFrom->translatedFormat('d F Y'),
            'date_to' => $dateTo->translatedFormat('d F Y'),
            'generated_at' => now()->translatedFormat('d F Y H:i'),
            'generated_by' => auth::user()->name,
            'roomStats' => $roomStats,
        ];

        if ($request->type === 'summary') {
            $bookingsQuery = Booking::whereBetween('booking_date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString()
            ]);

            $data['summary'] = [
                'total_bookings' => (clone $bookingsQuery)->count(),
                'approved' => (clone $bookingsQuery)->where('status', 'approved')->count(),
                'rejected' => (clone $bookingsQuery)->where('status', 'rejected')->count(),
                'cancelled' => (clone $bookingsQuery)->where('status', 'cancelled')->count(),
            ];

            $pdf = Pdf::loadView('reports.summary-pdf', $data)
                     ->setPaper('a4', 'portrait');

            $filename = 'laporan-ringkasan-' . $dateFrom->format('Y-m-d') . '-to-' . $dateTo->format('Y-m-d') . '.pdf';
        } else {
            // Detail type
            $bookings = Booking::with(['room', 'user'])
                ->whereBetween('booking_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
                ->whereIn('status', ['approved', 'completed'])
                ->orderBy('booking_date')
                ->orderBy('start_time')
                ->get();

            $data['bookings'] = $bookings;
            $data['total'] = $bookings->count();

            $pdf = Pdf::loadView('reports.detail-pdf', $data)
                     ->setPaper('a4', 'landscape');

            $filename = 'laporan-detail-' . $dateFrom->format('Y-m-d') . '-to-' . $dateTo->format('Y-m-d') . '.pdf';
        }

        return $pdf->download($filename);
    }

    /**
     * Export Laporan ke Excel
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);
        $category = $request->input('category');

        // Ambil data roomStats (sama seperti di exportPDF)
        $roomsQuery = Room::active();
        if ($category && $category !== '') {
            if ($category === 'other') {
                $knownCategories = array_keys(self::ROOM_TYPE_LABELS);
                $roomsQuery->where(function ($query) use ($knownCategories) {
                    $query->whereNull('type')
                          ->orWhereNotIn('type', $knownCategories);
                });
            } else {
                $roomsQuery->where('type', $category);
            }
        }
        $rooms = $roomsQuery->orderBy('name')->get();

        $roomStats = $rooms->map(function (Room $room) use ($dateFrom, $dateTo) {
            $bookings = $room->bookings()
                ->whereBetween('booking_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
                ->whereIn('status', [Booking::STATUS_APPROVED, Booking::STATUS_COMPLETED])
                ->get();

            $totalBookings = $bookings->count();
            $totalMinutes = $bookings->sum(function (Booking $booking) {
                $start = Carbon::parse($booking->start_time);
                $end = Carbon::parse($booking->end_time);
                return max(0, $start->diffInMinutes($end));
            });

            $totalHours = round($totalMinutes / 60, 2);
            $daysRange = max(1, $dateFrom->diffInDays($dateTo) + 1);
            $availableHours = $daysRange * self::DAILY_OPERATIONAL_HOURS;
            $utilization = $availableHours > 0
                ? round(($totalHours / $availableHours) * 100, 2)
                : 0;

            return [
                'room' => $room,
                'total_bookings' => $totalBookings,
                'total_hours' => $totalHours,
                'available_hours' => $availableHours,
                'utilization' => $utilization,
            ];
        });

        $filename = 'laporan-utilisasi-' . $dateFrom->format('Y-m-d') . '-to-' . $dateTo->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new BookingsExport($dateFrom, $dateTo, $roomStats),
            $filename
        );
    }
}
