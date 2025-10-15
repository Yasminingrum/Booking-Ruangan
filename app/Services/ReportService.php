<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ReportService
{
    /**
     * Generate dashboard summary statistics
     *
     * @return array
     */
    public function getDashboardSummary(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'total_bookings_today' => Booking::whereDate('booking_date', $today)->count(),
            'total_bookings_this_month' => Booking::where('booking_date', '>=', $thisMonth)->count(),
            'pending_approvals' => Booking::where('status', Booking::STATUS_PENDING)->count(),
            'approved_bookings' => Booking::where('status', Booking::STATUS_APPROVED)
                ->where('booking_date', '>=', $today)
                ->count(),
            'total_rooms' => Room::where('is_active', true)->count(),
            'total_users' => User::where('is_active', true)->count(),
            'most_popular_room' => $this->getMostPopularRoom(),
            'average_utilization' => $this->getAverageUtilization(),
        ];
    }

    /**
     * Get most popular room
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array|null
     */
    public function getMostPopularRoom(?string $startDate = null, ?string $endDate = null): ?array
    {
        $query = Booking::select('room_id', DB::raw('COUNT(*) as booking_count'))
            ->where('status', '!=', Booking::STATUS_REJECTED)
            ->groupBy('room_id')
            ->orderBy('booking_count', 'desc');

        if ($startDate) {
            $query->where('booking_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('booking_date', '<=', $endDate);
        }

        $result = $query->first();

        if (!$result) {
            return null;
        }

        $room = Room::find($result->room_id);

        return [
            'room_id' => $result->room_id,
            'room_name' => $room->name,
            'booking_count' => $result->booking_count,
        ];
    }

    /**
     * Get average utilization across all rooms
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return float
     */
    public function getAverageUtilization(?string $startDate = null, ?string $endDate = null): float
    {
        $rooms = Room::where('is_active', true)->get();
        $totalUtilization = 0;

        foreach ($rooms as $room) {
            $totalUtilization += $this->getRoomUtilization($room->id, $startDate, $endDate);
        }

        return $rooms->count() > 0 ? round($totalUtilization / $rooms->count(), 2) : 0;
    }

    /**
     * Get room utilization percentage
     *
     * @param int $roomId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return float
     */
    public function getRoomUtilization(int $roomId, ?string $startDate = null, ?string $endDate = null): float
    {
        $start = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate) : Carbon::now()->endOfMonth();

        // Hitung total hari kerja (Senin-Jumat)
        $workDays = 0;
        $current = $start->copy();
        while ($current <= $end) {
            if ($current->isWeekday()) {
                $workDays++;
            }
            $current->addDay();
        }

        // Asumsi jam operasional: 07:00 - 17:00 (10 jam per hari)
        $totalAvailableHours = $workDays * 10;

        if ($totalAvailableHours == 0) {
            return 0;
        }

        // Hitung total jam yang digunakan
        $bookings = Booking::where('room_id', $roomId)
            ->where('status', Booking::STATUS_APPROVED)
            ->whereBetween('booking_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get();

        $totalUsedHours = 0;
        foreach ($bookings as $booking) {
            $startTime = Carbon::parse($booking->start_time);
            $endTime = Carbon::parse($booking->end_time);
            $totalUsedHours += $startTime->diffInHours($endTime);
        }

        return round(($totalUsedHours / $totalAvailableHours) * 100, 2);
    }

    /**
     * Generate detailed room utilization report
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getRoomUtilizationReport(string $startDate, string $endDate): array
    {
        $rooms = Room::where('is_active', true)->get();
        $report = [];

        foreach ($rooms as $room) {
            $bookingCount = Booking::where('room_id', $room->id)
                ->where('status', Booking::STATUS_APPROVED)
                ->whereBetween('booking_date', [$startDate, $endDate])
                ->count();

            $bookings = Booking::where('room_id', $room->id)
                ->where('status', Booking::STATUS_APPROVED)
                ->whereBetween('booking_date', [$startDate, $endDate])
                ->get();

            $totalHours = 0;
            foreach ($bookings as $booking) {
                $startTime = Carbon::parse($booking->start_time);
                $endTime = Carbon::parse($booking->end_time);
                $totalHours += $startTime->diffInHours($endTime);
            }

            $utilization = $this->getRoomUtilization($room->id, $startDate, $endDate);

            $report[] = [
                'room_id' => $room->id,
                'room_name' => $room->name,
                'room_type' => $room->type,
                'capacity' => $room->capacity,
                'total_bookings' => $bookingCount,
                'total_hours' => $totalHours,
                'utilization_percentage' => $utilization,
            ];
        }

        // Sort by utilization descending
        usort($report, function ($a, $b) {
            return $b['utilization_percentage'] <=> $a['utilization_percentage'];
        });

        return $report;
    }

    /**
     * Generate user booking report
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getUserBookingReport(string $startDate, string $endDate): array
    {
        // Get all bookings in date range grouped by user
        $bookingsByUser = Booking::with('user')
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->get()
            ->groupBy('user_id');

        $report = [];

        foreach ($bookingsByUser as $userId => $bookings) {
            $user = $bookings->first()->user;

            $totalBookings = $bookings->count();
            $approvedBookings = $bookings->where('status', Booking::STATUS_APPROVED)->count();
            $rejectedBookings = $bookings->where('status', Booking::STATUS_REJECTED)->count();

            $report[] = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'total_bookings' => $totalBookings,
                'approved_bookings' => $approvedBookings,
                'rejected_bookings' => $rejectedBookings,
                'pending_bookings' => $bookings->where('status', Booking::STATUS_PENDING)->count(),
                'cancelled_bookings' => $bookings->where('status', Booking::STATUS_CANCELLED)->count(),
                'approval_rate' => $totalBookings > 0
                    ? round(($approvedBookings / $totalBookings) * 100, 2)
                    : 0,
            ];
        }

        // Sort by total bookings descending
        usort($report, function ($a, $b) {
            return $b['total_bookings'] <=> $a['total_bookings'];
        });

        return $report;
    }

    /**
     * Generate booking status distribution
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getBookingStatusDistribution(string $startDate, string $endDate): array
    {
        $statuses = [
            Booking::STATUS_PENDING,
            Booking::STATUS_APPROVED,
            Booking::STATUS_REJECTED,
            Booking::STATUS_CANCELLED,
            Booking::STATUS_COMPLETED,
        ];

        $distribution = [];
        $total = 0;

        foreach ($statuses as $status) {
            $count = Booking::where('status', $status)
                ->whereBetween('booking_date', [$startDate, $endDate])
                ->count();

            $distribution[$status] = $count;
            $total += $count;
        }

        // Add percentage
        foreach ($distribution as $status => $count) {
            $distribution[$status] = [
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
            ];
        }

        return $distribution;
    }

    /**
     * Generate booking timeline (per day)
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getBookingTimeline(string $startDate, string $endDate): array
    {
        $bookings = Booking::selectRaw('DATE(booking_date) as date, COUNT(*) as count')
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $bookings->map(function ($item) {
            return [
                'date' => Carbon::parse($item->date)->format('Y-m-d'),
                'count' => $item->count,
            ];
        })->toArray();
    }

    /**
     * Generate room type distribution
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getRoomTypeDistribution(string $startDate, string $endDate): array
    {
        $data = Booking::join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->selectRaw('rooms.type, COUNT(bookings.id) as booking_count')
            ->whereBetween('bookings.booking_date', [$startDate, $endDate])
            ->where('bookings.status', '!=', Booking::STATUS_REJECTED)
            ->groupBy('rooms.type')
            ->get();

        $total = $data->sum('booking_count');

        return $data->map(function ($item) use ($total) {
            return [
                'type' => $item->type,
                'booking_count' => $item->booking_count,
                'percentage' => $total > 0 ? round(($item->booking_count / $total) * 100, 2) : 0,
            ];
        })->toArray();
    }

    /**
     * Generate peak usage hours
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getPeakUsageHours(string $startDate, string $endDate): array
    {
        $bookings = Booking::where('status', Booking::STATUS_APPROVED)
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->get();

        $hourlyDistribution = array_fill(0, 24, 0);

        foreach ($bookings as $booking) {
            $startHour = Carbon::parse($booking->start_time)->hour;
            $endHour = Carbon::parse($booking->end_time)->hour;

            for ($hour = $startHour; $hour < $endHour; $hour++) {
                $hourlyDistribution[$hour]++;
            }
        }

        $result = [];
        foreach ($hourlyDistribution as $hour => $count) {
            $result[] = [
                'hour' => sprintf('%02d:00', $hour),
                'booking_count' => $count,
            ];
        }

        // Sort by count descending
        usort($result, function ($a, $b) {
            return $b['booking_count'] <=> $a['booking_count'];
        });

        return $result;
    }

    /**
     * Export report data to array (for PDF/Excel)
     *
     * @param string $reportType
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function exportReport(string $reportType, string $startDate, string $endDate): array
    {
        $data = [
            'report_type' => $reportType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'generated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'generated_by' => auth::user()?->name ?? 'System',
        ];

        switch ($reportType) {
            case 'room_utilization':
                $data['data'] = $this->getRoomUtilizationReport($startDate, $endDate);
                break;

            case 'user_bookings':
                $data['data'] = $this->getUserBookingReport($startDate, $endDate);
                break;

            case 'status_distribution':
                $data['data'] = $this->getBookingStatusDistribution($startDate, $endDate);
                break;

            case 'timeline':
                $data['data'] = $this->getBookingTimeline($startDate, $endDate);
                break;

            case 'room_type':
                $data['data'] = $this->getRoomTypeDistribution($startDate, $endDate);
                break;

            case 'peak_hours':
                $data['data'] = $this->getPeakUsageHours($startDate, $endDate);
                break;

            case 'comprehensive':
                $data['room_utilization'] = $this->getRoomUtilizationReport($startDate, $endDate);
                $data['user_bookings'] = $this->getUserBookingReport($startDate, $endDate);
                $data['status_distribution'] = $this->getBookingStatusDistribution($startDate, $endDate);
                $data['room_type'] = $this->getRoomTypeDistribution($startDate, $endDate);
                $data['peak_hours'] = array_slice($this->getPeakUsageHours($startDate, $endDate), 0, 10);
                break;

            default:
                throw new \Exception("Unknown report type: {$reportType}");
        }

        return $data;
    }
}
