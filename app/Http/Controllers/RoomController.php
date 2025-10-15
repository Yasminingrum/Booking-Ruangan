<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomRequest;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of all rooms
     * FR-01: Sistem harus dapat menampilkan daftar semua ruangan yang tersedia
     */
    public function index(Request $request)
    {
        $query = Room::query();

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        } else {
            // Default: only show active rooms
            $query->where('is_active', true);
        }

        // FR-04: Filter by room type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by minimum capacity
        if ($request->filled('min_capacity')) {
            $query->where('capacity', '>=', $request->min_capacity);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $rooms = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $rooms,
        ], 200);
    }

    /**
     * Display the specified room
     */
    public function show($id)
    {
        $room = Room::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $room,
        ], 200);
    }

    /**
     * FR-02 & FR-03: Check room availability for specific date and time
     * Menampilkan status ketersediaan ruangan
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'room_id' => 'nullable|exists:rooms,id',
            'type' => 'nullable|in:laboratorium,ruang_musik,audio_visual,lapangan_basket,kolam_renang',
        ]);

        $query = Room::where('is_active', true);

        // Filter by room_id or type
        if ($request->filled('room_id')) {
            $query->where('id', $request->room_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $rooms = $query->get();

        $availableRooms = [];
        $bookedRooms = [];

        foreach ($rooms as $room) {
            $isAvailable = $room->isAvailable(
                $request->date,
                $request->start_time,
                $request->end_time
            );

            if ($isAvailable) {
                $availableRooms[] = $room;
            } else {
                // Get conflicting booking info
                $conflict = Booking::where('room_id', $room->id)
                    ->where('booking_date', $request->date)
                    ->whereIn('status', ['pending', 'approved'])
                    ->where(function ($q) use ($request) {
                        $q->whereBetween('start_time', [$request->start_time, $request->end_time])
                          ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                          ->orWhere(function ($q2) use ($request) {
                              $q2->where('start_time', '<=', $request->start_time)
                                 ->where('end_time', '>=', $request->end_time);
                          });
                    })
                    ->with('user:id,name')
                    ->first();

                $bookedRooms[] = [
                    'room' => $room,
                    'conflict' => $conflict,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'available' => $availableRooms,
                'booked' => $bookedRooms,
            ],
        ], 200);
    }

    /**
     * Store a newly created room (Admin only)
     * FR-16: CRUD ruangan
     */
    public function store(RoomRequest $request)
    {
        $room = Room::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Ruangan berhasil ditambahkan',
            'data' => $room,
        ], 201);
    }

    /**
     * Update the specified room (Admin only)
     * FR-16: CRUD ruangan
     */
    public function update(RoomRequest $request, $id)
    {
        $room = Room::findOrFail($id);
        $room->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Ruangan berhasil diperbarui',
            'data' => $room,
        ], 200);
    }

    /**
     * Remove the specified room (Admin only)
     * FR-16: CRUD ruangan
     */
    public function destroy($id)
    {
        $room = Room::findOrFail($id);

        // Check if room has upcoming approved bookings
        $hasUpcomingBookings = Booking::where('room_id', $room->id)
            ->where('status', 'approved')
            ->where('booking_date', '>=', now()->toDateString())
            ->exists();

        if ($hasUpcomingBookings) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak dapat dihapus karena masih memiliki peminjaman yang sudah disetujui.',
            ], 422);
        }

        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ruangan berhasil dihapus',
        ], 200);
    }

    /**
     * Get bookings for a specific room
     */
    public function bookings($id, Request $request)
    {
        $room = Room::findOrFail($id);

        $request->validate([
            'date' => 'nullable|date',
            'status' => 'nullable|in:pending,approved,rejected,cancelled,completed',
        ]);

        $query = $room->bookings()->with('user:id,name,email');

        if ($request->filled('date')) {
            $query->where('booking_date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('booking_date')
                          ->orderBy('start_time')
                          ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'room' => $room,
                'bookings' => $bookings,
            ],
        ], 200);
    }
}
