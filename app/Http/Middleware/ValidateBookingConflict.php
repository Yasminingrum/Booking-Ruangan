<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ValidateBookingConflict
{
    /**
     * Handle an incoming request.
     * Middleware ini memvalidasi apakah ada konflik jadwal peminjaman ruangan
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Hanya validate untuk method POST dan PUT pada route booking
        if (!in_array($request->method(), ['POST', 'PUT'])) {
            return $next($request);
        }

        // Ambil data dari request
        $roomId = $request->input('room_id');
        $bookingDate = $request->input('booking_date');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        // ID booking untuk update (null jika create)
        $excludeBookingId = $request->route('id') ?? $request->route('booking');

        // Validasi input terlebih dahulu
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Cek konflik jadwal
        $conflict = $this->checkConflict(
            $roomId,
            $bookingDate,
            $startTime,
            $endTime,
            $excludeBookingId
        );

        if ($conflict) {
            $errorMessage = sprintf(
                'Konflik jadwal terdeteksi! Ruangan sudah dipesan pada %s dari %s sampai %s oleh %s.',
                $conflict->booking_date->format('d M Y'),
                Carbon::parse($conflict->start_time)->format('H:i'),
                Carbon::parse($conflict->end_time)->format('H:i'),
                $conflict->user->name
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time conflict detected',
                    'error' => $errorMessage,
                    'conflict_booking' => [
                        'id' => $conflict->id,
                        'user' => $conflict->user->name,
                        'date' => $conflict->booking_date->format('Y-m-d'),
                        'start_time' => Carbon::parse($conflict->start_time)->format('H:i:s'),
                        'end_time' => Carbon::parse($conflict->end_time)->format('H:i:s'),
                        'status' => $conflict->status
                    ]
                ], 409); // 409 Conflict
            }

            return back()
                ->withInput()
                ->with('error', $errorMessage);
        }

        // Jika tidak ada konflik, lanjutkan request
        return $next($request);
    }

    /**
     * Cek apakah ada booking yang konflik dengan waktu yang diminta
     *
     * @param  int  $roomId
     * @param  string  $bookingDate
     * @param  string  $startTime
     * @param  string  $endTime
     * @param  int|null  $excludeBookingId  ID booking yang dikecualikan (untuk update)
     * @return \App\Models\Booking|null
     */
    private function checkConflict(
        int $roomId,
        string $bookingDate,
        string $startTime,
        string $endTime,
        ?int $excludeBookingId = null
    ): ?Booking
    {
        $query = Booking::where('room_id', $roomId)
            ->where('booking_date', $bookingDate)
            ->whereIn('status', ['pending', 'approved']) // Hanya cek booking yang aktif
            ->where(function ($query) use ($startTime, $endTime) {
                // Cek overlap waktu:
                // Case 1: Start time baru ada di antara booking existing
                $query->whereBetween('start_time', [$startTime, $endTime])
                    // Case 2: End time baru ada di antara booking existing
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    // Case 3: Booking baru mencakup keseluruhan booking existing
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '>=', $startTime)
                          ->where('end_time', '<=', $endTime);
                    })
                    // Case 4: Booking existing mencakup keseluruhan booking baru
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            });

        // Exclude booking yang sedang di-update
        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->with('user')->first();
    }
}
