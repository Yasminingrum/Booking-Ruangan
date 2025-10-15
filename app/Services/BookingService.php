<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Models\Notification;
use App\Services\ConflictValidationService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookingService
{
    protected $conflictValidator;
    protected $notificationService;

    public function __construct(
        ConflictValidationService $conflictValidator,
        NotificationService $notificationService
    ) {
        $this->conflictValidator = $conflictValidator;
        $this->notificationService = $notificationService;
    }

    /**
     * Buat booking baru dengan validasi lengkap
     *
     * @param array $data
     * @return Booking
     * @throws \Exception
     */
    public function createBooking(array $data): Booking
    {
        DB::beginTransaction();

        try {
            // Validasi konflik jadwal
            $hasConflict = $this->conflictValidator->validateConflict(
                $data['room_id'],
                $data['booking_date'],
                $data['start_time'],
                $data['end_time']
            );

            if ($hasConflict) {
                throw new \Exception('Ruangan sudah dibooking pada waktu tersebut');
            }

            // Validasi kapasitas ruangan
            $room = Room::findOrFail($data['room_id']);
            if ($data['participants'] > $room->capacity) {
                throw new \Exception("Jumlah peserta melebihi kapasitas ruangan ({$room->capacity} orang)");
            }

            // Validasi ruangan aktif
            if (!$room->is_active) {
                throw new \Exception('Ruangan tidak tersedia untuk dipinjam');
            }

            // Buat booking baru
            $booking = Booking::create([
                'user_id' => $data['user_id'],
                'room_id' => $data['room_id'],
                'booking_date' => $data['booking_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'purpose' => $data['purpose'],
                'participants' => $data['participants'],
                'status' => Booking::STATUS_PENDING,
                'is_recurring' => $data['is_recurring'] ?? false,
                'recurring_pattern' => $data['recurring_pattern'] ?? null,
            ]);

            // Buat history awal
            $booking->histories()->create([
                'changed_by_user_id' => $data['user_id'],
                'old_status' => null,
                'new_status' => Booking::STATUS_PENDING,
                'notes' => 'Booking dibuat',
            ]);

            // Kirim notifikasi ke semua admin
            $this->notificationService->sendBookingCreatedNotification($booking);

            DB::commit();

            Log::info('Booking created successfully', [
                'booking_id' => $booking->id,
                'user_id' => $data['user_id'],
                'room_id' => $data['room_id'],
            ]);

            return $booking->load(['user', 'room']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create booking', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Update status booking
     *
     * @param int $bookingId
     * @param string $newStatus
     * @param int $changedByUserId
     * @param string|null $reason
     * @return Booking
     * @throws \Exception
     */
    public function updateBookingStatus(
        int $bookingId,
        string $newStatus,
        int $changedByUserId,
        ?string $reason = null
    ): Booking {
        DB::beginTransaction();

        try {
            $booking = Booking::with(['user', 'room'])->findOrFail($bookingId);
            $oldStatus = $booking->status;

            // Validasi status transition
            $this->validateStatusTransition($oldStatus, $newStatus);

            // Jika approve, cek konflik lagi (safety check)
            if ($newStatus === Booking::STATUS_APPROVED) {
                $hasConflict = $this->conflictValidator->validateConflict(
                    $booking->room_id,
                    $booking->booking_date,
                    $booking->start_time,
                    $booking->end_time,
                    $bookingId
                );

                if ($hasConflict) {
                    throw new \Exception('Terjadi konflik jadwal. Mungkin admin lain telah approve booking berbeda.');
                }

                $booking->approved_by = $changedByUserId;
                $booking->approved_at = now();
            }

            // Update status
            $booking->status = $newStatus;

            if ($newStatus === Booking::STATUS_REJECTED && $reason) {
                $booking->rejection_reason = $reason;
            }

            $booking->save();

            // Simpan history
            $booking->histories()->create([
                'changed_by_user_id' => $changedByUserId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'notes' => $reason ?? "Status berubah dari {$oldStatus} ke {$newStatus}",
            ]);

            // Kirim notifikasi ke peminjam
            switch ($newStatus) {
                case Booking::STATUS_APPROVED:
                    $this->notificationService->sendBookingApprovedNotification($booking);
                    break;
                case Booking::STATUS_REJECTED:
                    $this->notificationService->sendBookingRejectedNotification($booking);
                    break;
            }

            DB::commit();

            Log::info('Booking status updated', [
                'booking_id' => $bookingId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $changedByUserId,
            ]);

            return $booking->fresh(['user', 'room', 'approver']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update booking status', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cari ruangan yang tersedia pada waktu tertentu
     *
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param string|null $type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableRooms(
        string $date,
        string $startTime,
        string $endTime,
        ?string $type = null
    ) {
        $query = Room::active();

        if ($type) {
            $query->where('type', $type);
        }

        $rooms = $query->get();

        // Filter ruangan yang available
        $availableRooms = $rooms->filter(function ($room) use ($date, $startTime, $endTime) {
            return $room->isAvailable($date, $startTime, $endTime);
        });

        return $availableRooms->values();
    }

    /**
     * Validasi transisi status
     *
     * @param string $oldStatus
     * @param string $newStatus
     * @throws \Exception
     */
    protected function validateStatusTransition(string $oldStatus, string $newStatus): void
    {
        $validTransitions = [
            Booking::STATUS_PENDING => [
                Booking::STATUS_APPROVED,
                Booking::STATUS_REJECTED,
                Booking::STATUS_CANCELLED,
            ],
            Booking::STATUS_APPROVED => [
                Booking::STATUS_COMPLETED,
                Booking::STATUS_CANCELLED,
            ],
        ];

        if (!isset($validTransitions[$oldStatus]) ||
            !in_array($newStatus, $validTransitions[$oldStatus])) {
            throw new \Exception("Tidak dapat mengubah status dari {$oldStatus} ke {$newStatus}");
        }
    }

    /**
     * Batal booking (oleh user)
     *
     * @param int $bookingId
     * @param int $userId
     * @return Booking
     * @throws \Exception
     */
    public function cancelBooking(int $bookingId, int $userId): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        // Validasi ownership
        if ($booking->user_id !== $userId) {
            throw new \Exception('Anda tidak memiliki akses untuk membatalkan booking ini');
        }

        // Validasi status
        if (!in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_APPROVED])) {
            throw new \Exception('Booking tidak dapat dibatalkan');
        }

        return $this->updateBookingStatus(
            $bookingId,
            Booking::STATUS_CANCELLED,
            $userId,
            'Dibatalkan oleh peminjam'
        );
    }

    /**
     * Get booking statistics
     *
     * @param array $filters
     * @return array
     */
    public function getBookingStatistics(array $filters = []): array
    {
        $query = Booking::query();

        // Apply date range filter
        if (isset($filters['start_date'])) {
            $query->where('booking_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('booking_date', '<=', $filters['end_date']);
        }

        $totalBookings = (clone $query)->count();
        $pendingBookings = (clone $query)->where('status', Booking::STATUS_PENDING)->count();
        $approvedBookings = (clone $query)->where('status', Booking::STATUS_APPROVED)->count();
        $rejectedBookings = (clone $query)->where('status', Booking::STATUS_REJECTED)->count();
        $completedBookings = (clone $query)->where('status', Booking::STATUS_COMPLETED)->count();

        return [
            'total' => $totalBookings,
            'pending' => $pendingBookings,
            'approved' => $approvedBookings,
            'rejected' => $rejectedBookings,
            'completed' => $completedBookings,
        ];
    }
}
