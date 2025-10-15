<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ConflictValidationService
{
    /**
     * Validasi konflik jadwal booking
     *
     * @param int $roomId
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeBookingId
     * @return bool True jika ada konflik, False jika aman
     */
    public function validateConflict(
        int $roomId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeBookingId = null
    ): bool {
        try {
            // Query booking yang sudah approved atau pending di ruangan yang sama dan tanggal yang sama
            $query = Booking::where('room_id', $roomId)
                ->where('booking_date', $date)
                ->whereIn('status', [
                    Booking::STATUS_PENDING,
                    Booking::STATUS_APPROVED
                ]);

            // Exclude booking tertentu (untuk update)
            if ($excludeBookingId) {
                $query->where('id', '!=', $excludeBookingId);
            }

            $existingBookings = $query->get();

            // Cek overlap dengan setiap booking yang ada
            foreach ($existingBookings as $existingBooking) {
                if ($this->hasTimeOverlap($startTime, $endTime, $existingBooking->start_time, $existingBooking->end_time)) {
                    Log::warning('Booking conflict detected', [
                        'room_id' => $roomId,
                        'date' => $date,
                        'new_time' => "$startTime - $endTime",
                        'conflict_with_booking_id' => $existingBooking->id,
                        'existing_time' => "{$existingBooking->start_time} - {$existingBooking->end_time}",
                    ]);

                    return true; // Ada konflik
                }
            }

            return false; // Tidak ada konflik

        } catch (\Exception $e) {
            Log::error('Error in conflict validation', [
                'error' => $e->getMessage(),
                'room_id' => $roomId,
                'date' => $date,
            ]);
            throw $e;
        }
    }

    /**
     * Cek apakah dua rentang waktu overlap
     *
     * @param string $start1
     * @param string $end1
     * @param string $start2
     * @param string $end2
     * @return bool
     */
    public function hasTimeOverlap(string $start1, string $end1, string $start2, string $end2): bool
    {
        $start1 = Carbon::parse($start1);
        $end1 = Carbon::parse($end1);
        $start2 = Carbon::parse($start2);
        $end2 = Carbon::parse($end2);

        // Case 1: start1 berada di antara start2 dan end2
        // Case 2: end1 berada di antara start2 dan end2
        // Case 3: start1 sebelum start2 dan end1 setelah end2 (melingkupi)
        // Case 4: start1 sama dengan start2 atau end1 sama dengan end2

        return ($start1->between($start2, $end2, false) ||
                $end1->between($start2, $end2, false) ||
                ($start1->lessThanOrEqualTo($start2) && $end1->greaterThanOrEqualTo($end2)));
    }

    /**
     * Get conflicting bookings untuk tanggal dan waktu tertentu
     *
     * @param int $roomId
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeBookingId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getConflictingBookings(
        int $roomId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeBookingId = null
    ) {
        $query = Booking::with(['user', 'room'])
            ->where('room_id', $roomId)
            ->where('booking_date', $date)
            ->whereIn('status', [
                Booking::STATUS_PENDING,
                Booking::STATUS_APPROVED
            ]);

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        $bookings = $query->get();

        // Filter hanya yang overlap
        return $bookings->filter(function ($booking) use ($startTime, $endTime) {
            return $this->hasTimeOverlap(
                $startTime,
                $endTime,
                $booking->start_time,
                $booking->end_time
            );
        });
    }

    /**
     * Validasi batch bookings (untuk recurring)
     *
     * @param int $roomId
     * @param array $dates Array of ['date' => 'Y-m-d', 'start_time' => 'H:i:s', 'end_time' => 'H:i:s']
     * @return array ['valid' => bool, 'conflicts' => array]
     */
    public function validateBatchBookings(int $roomId, array $dates): array
    {
        $conflicts = [];
        $allValid = true;

        foreach ($dates as $index => $dateInfo) {
            $hasConflict = $this->validateConflict(
                $roomId,
                $dateInfo['date'],
                $dateInfo['start_time'],
                $dateInfo['end_time']
            );

            if ($hasConflict) {
                $allValid = false;
                $conflicts[] = [
                    'index' => $index,
                    'date' => $dateInfo['date'],
                    'time' => "{$dateInfo['start_time']} - {$dateInfo['end_time']}",
                ];
            }
        }

        return [
            'valid' => $allValid,
            'conflicts' => $conflicts,
        ];
    }

    /**
     * Check if booking dapat di-reschedule ke waktu baru
     *
     * @param int $bookingId
     * @param string $newDate
     * @param string $newStartTime
     * @param string $newEndTime
     * @return array
     */
    public function canReschedule(
        int $bookingId,
        string $newDate,
        string $newStartTime,
        string $newEndTime
    ): array {
        $booking = Booking::findOrFail($bookingId);

        $hasConflict = $this->validateConflict(
            $booking->room_id,
            $newDate,
            $newStartTime,
            $newEndTime,
            $bookingId
        );

        if ($hasConflict) {
            $conflicts = $this->getConflictingBookings(
                $booking->room_id,
                $newDate,
                $newStartTime,
                $newEndTime,
                $bookingId
            );

            return [
                'can_reschedule' => false,
                'reason' => 'Waktu baru bertabrakan dengan booking lain',
                'conflicting_bookings' => $conflicts,
            ];
        }

        return [
            'can_reschedule' => true,
            'reason' => null,
            'conflicting_bookings' => [],
        ];
    }
}
