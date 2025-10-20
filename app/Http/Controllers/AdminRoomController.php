<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminRoomController extends Controller
{
    /**
     * Display a listing of the rooms.
     */
    public function index()
    {
        $rooms = Room::orderBy('name')->paginate(10);

        $totalRooms = Room::count();
        $availableRooms = Room::where('is_active', true)->count();
        $unavailableRooms = Room::where('is_active', false)->count();

        return view('admin.rooms.index', compact('rooms', 'totalRooms', 'availableRooms', 'unavailableRooms'));
    }

    /**
     * Show the form for creating a new room.
     */
    public function create()
    {
        return view('admin.rooms.create');
    }

    /**
     * Store a newly created room in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:rooms',
            'type' => 'required|in:laboratorium,ruang_musik,audio_visual,lapangan_basket,kolam_renang',
            'capacity' => 'required|integer|min:1',
            'location' => 'required|string|max:255',
            'facilities' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.rooms.create')
                ->withErrors($validator)
                ->withInput();
        }

        Room::create([
            'name' => $request->name,
            'type' => $request->type,
            'capacity' => $request->capacity,
            'location' => $request->location,
            'facilities' => $request->facilities,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil ditambahkan');
    }

    /**
     * Display the specified room.
     */
    public function show($id)
    {
        $room = Room::findOrFail($id);
        $bookings = Booking::where('room_id', $id)
            ->with('user')
            ->orderBy('booking_date', 'desc')
            ->paginate(10);

        return view('admin.rooms.show', compact('room', 'bookings'));
    }

    /**
     * Show the form for editing the specified room.
     */
    public function edit($id)
    {
        $room = Room::findOrFail($id);
        return view('admin.rooms.edit', compact('room'));
    }

    /**
     * Update the specified room in storage.
     */
    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:rooms,name,' . $id,
            'capacity' => 'required|integer|min:1',
            'location' => 'required|string|max:255',
            'facilities' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.rooms.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        $room->update([
            'name' => $request->name,
            'capacity' => $request->capacity,
            'location' => $request->location,
            'facilities' => $request->facilities,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil diperbarui');
    }

    /**
     * Remove the specified room from storage.
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
            return redirect()
                ->route('admin.rooms.index')
                ->with('error', 'Ruangan tidak dapat dihapus karena masih memiliki peminjaman yang sudah disetujui.');
        }

        $room->delete();

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil dihapus');
    }
}
