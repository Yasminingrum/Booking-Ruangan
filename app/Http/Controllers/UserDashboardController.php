<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'type' => ['nullable', Rule::in([
                Room::TYPE_LABORATORIUM,
                Room::TYPE_RUANG_MUSIK,
                Room::TYPE_AUDIO_VISUAL,
                Room::TYPE_LAPANGAN_BASKET,
                Room::TYPE_KOLAM_RENANG,
            ])],
            'min_capacity' => ['nullable', 'integer', 'min:0'],
            'keyword' => ['nullable', 'string', 'max:255'],
            'available_only' => ['nullable', 'boolean'],
        ]);

        $filters = [
            'date' => $request->input('date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'type' => $request->input('type'),
            'min_capacity' => $request->input('min_capacity', ''),
            'keyword' => $request->input('keyword'),
            'available_only' => $request->boolean('available_only'),
        ];

        $roomQuery = Room::query()->where('is_active', true);

        if ($filters['type']) {
            $roomQuery->where('type', $filters['type']);
        }

        if ($filters['min_capacity'] !== '' && $filters['min_capacity'] !== null) {
            $roomQuery->where('capacity', '>=', (int) $filters['min_capacity']);
        }

        if ($filters['keyword']) {
            $keyword = '%' . $filters['keyword'] . '%';
            $roomQuery->where(function ($query) use ($keyword) {
                $query->where('name', 'like', $keyword)
                    ->orWhere('location', 'like', $keyword)
                    ->orWhere('facilities', 'like', $keyword);
            });
        }

        $rooms = $roomQuery->orderBy('name')->get();

        $hasTimeRange = $filters['date'] && $filters['start_time'] && $filters['end_time'];

        $roomsEvaluated = $rooms->map(function ($room) use ($filters, $hasTimeRange) {
            $isAvailable = true;

            if ($hasTimeRange) {
                $isAvailable = $room->isAvailable(
                    $filters['date'],
                    $filters['start_time'],
                    $filters['end_time']
                );
            }

            $room->availability_is_available = $isAvailable;
            $room->availability_label = $isAvailable ? 'Tersedia' : 'Terpesan';
            $room->availability_badge = $isAvailable
                ? 'bg-yellow-100 text-yellow-800'
                : 'bg-slate-200 text-slate-600';
            $room->facility_list = collect(preg_split('/[,;]+/', (string) $room->facilities))
                ->map(fn ($item) => trim($item))
                ->filter()
                ->values();

            return $room;
        });

        $availableCount = $roomsEvaluated->filter(fn ($room) => $room->availability_is_available)->count();
        $totalActiveRooms = Room::where('is_active', true)->count();

        if (!$hasTimeRange) {
            $availableCount = $totalActiveRooms;
            $bookedCount = 0;
        } else {
            $bookedCount = max($roomsEvaluated->count() - $availableCount, 0);
        }

        $filteredRooms = $filters['available_only']
            ? $roomsEvaluated->filter(fn ($room) => $room->availability_is_available)->values()
            : $roomsEvaluated;

        $recentBookingsQuery = Booking::where('user_id', Auth::id());

        $summary = [
            'available' => $availableCount,
            'booked' => $bookedCount,
            'total_rooms' => $totalActiveRooms,
            'my_requests' => $recentBookingsQuery->count(),
        ];

        $recentBookings = $recentBookingsQuery
            ->with('room')
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->limit(5)
            ->get();

        $roomTypes = [
            '' => 'Semua',
            Room::TYPE_LABORATORIUM => 'Laboratorium',
            Room::TYPE_RUANG_MUSIK => 'Ruang Musik',
            Room::TYPE_AUDIO_VISUAL => 'Audio Visual',
            Room::TYPE_LAPANGAN_BASKET => 'Lapangan Basket',
            Room::TYPE_KOLAM_RENANG => 'Kolam Renang',
        ];

        return view('dashboard', [
            'filters' => $filters,
            'rooms' => $filteredRooms,
            'roomsFound' => $filteredRooms->count(),
            'hasTimeRange' => $hasTimeRange,
            'summary' => $summary,
            'recentBookings' => $recentBookings,
            'roomTypes' => $roomTypes,
        ]);
    }
}
