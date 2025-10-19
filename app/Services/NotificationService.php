<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\User;
use App\Mail\BookingCreated;
use App\Services\BrevoEmailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificationService
{
    /**
     * @var BrevoEmailService
     */
    protected $brevo;

    public function __construct(BrevoEmailService $brevoEmailService)
    {
        $this->brevo = $brevoEmailService;
    }

    /**
     * Kirim notifikasi booking baru dibuat (ke semua admin)
     *
     * @param Booking $booking
     * @return void
     */
    public function sendBookingCreatedNotification(Booking $booking): void
    {
        try {
            // Load relationships
            $booking->load(['user', 'room']);

            // Get all admin users
            $admins = User::where('role', User::ROLE_ADMIN)
                ->where('is_active', true)
                ->get();

            foreach ($admins as $admin) {
                // Create in-app notification
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => Notification::TYPE_BOOKING_CREATED,
                    'title' => 'Pengajuan Peminjaman Baru',
                    'message' => sprintf(
                        '%s mengajukan peminjaman %s untuk tanggal %s pukul %s - %s',
                        $booking->user->name,
                        $booking->room->name,
                        $this->formatDate($booking->booking_date),
                        $this->formatTime($booking->start_time),
                        $this->formatTime($booking->end_time)
                    ),
                    'related_booking_id' => $booking->id,
                    'is_read' => false,
                ]);

                // Send email (queued)
                try {
                    Mail::to($admin->email)->queue(new BookingCreated($booking, $admin));
                } catch (\Exception $e) {
                    Log::error('Failed to send booking created email', [
                        'admin_id' => $admin->id,
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Booking created notifications sent', [
                'booking_id' => $booking->id,
                'admin_count' => $admins->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send booking created notifications', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Kirim notifikasi booking disetujui (ke peminjam)
     *
     * @param Booking $booking
     * @return void
     */
    public function sendBookingApprovedNotification(Booking $booking): void
    {
        try {
            $booking->load(['user', 'room', 'approver']);

            // Create in-app notification
            Notification::create([
                'user_id' => $booking->user_id,
                'type' => Notification::TYPE_BOOKING_APPROVED,
                'title' => 'Peminjaman Disetujui',
                    'message' => sprintf(
                        'Peminjaman %s pada tanggal %s pukul %s - %s telah disetujui',
                        $booking->room->name,
                        $this->formatDate($booking->booking_date),
                        $this->formatTime($booking->start_time),
                        $this->formatTime($booking->end_time)
                    ),
                'related_booking_id' => $booking->id,
                'is_read' => false,
            ]);

            $params = [
                'nama_user' => $booking->user->name,
                'nama_proyek' => $booking->purpose ?? $booking->room->name,
                'tanggal_pengajuan' => $this->formatDate($booking->created_at, 'd F Y'),
                'tanggal_approval' => $this->formatDate($booking->approved_at ?? now(), 'd F Y'),
            ];

            $this->brevo->sendTemplate(
                'emails.brevo.booking-approved',
                'Pengajuan Peminjaman Disetujui',
                $booking->user->email,
                $booking->user->name,
                $params,
                [
                    'button_url' => $this->borrowerStatusUrl(),
                ]
            );

            Log::info('Booking approved notification sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send booking approved notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Kirim notifikasi booking ditolak (ke peminjam)
     *
     * @param Booking $booking
     * @return void
     */
    public function sendBookingRejectedNotification(Booking $booking): void
    {
        try {
            $booking->load(['user', 'room']);

            // Create in-app notification
            Notification::create([
                'user_id' => $booking->user_id,
                'type' => Notification::TYPE_BOOKING_REJECTED,
                'title' => 'Peminjaman Ditolak',
                    'message' => sprintf(
                        'Peminjaman %s pada tanggal %s pukul %s - %s ditolak. Alasan: %s',
                        $booking->room->name,
                        $this->formatDate($booking->booking_date),
                        $this->formatTime($booking->start_time),
                        $this->formatTime($booking->end_time),
                        $booking->rejection_reason ?? 'Tidak disebutkan'
                    ),
                'related_booking_id' => $booking->id,
                'is_read' => false,
            ]);

            $params = [
                'nama_user' => $booking->user->name,
                'nama_proyek' => $booking->purpose ?? $booking->room->name,
                'tanggal_pengajuan' => $this->formatDate($booking->created_at, 'd F Y'),
            ];

            $viewData = [
                'button_url' => $this->borrowerStatusUrl(),
            ];

            if (!empty($booking->rejection_reason)) {
                $viewData['alasan_penolakan'] = $booking->rejection_reason;
                $params['alasan_penolakan'] = $booking->rejection_reason;
            }

            $this->brevo->sendTemplate(
                'emails.brevo.booking-rejected',
                'Pengajuan Peminjaman Ditolak',
                $booking->user->email,
                $booking->user->name,
                $params,
                $viewData
            );

            Log::info('Booking rejected notification sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send booking rejected notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Kirim notifikasi booking dibatalkan
     *
     * @param Booking $booking
     * @param bool $notifyAdmins
     * @return void
     */
    public function sendBookingCancelledNotification(Booking $booking, bool $notifyAdmins = true): void
    {
        try {
            $booking->load(['user', 'room']);

            if ($notifyAdmins) {
                // Notify admins
                $admins = User::where('role', User::ROLE_ADMIN)
                    ->where('is_active', true)
                    ->get();

                foreach ($admins as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => Notification::TYPE_BOOKING_CANCELLED,
                        'title' => 'Peminjaman Dibatalkan',
                        'message' => sprintf(
                            '%s membatalkan peminjaman %s pada tanggal %s',
                            $booking->user->name,
                            $booking->room->name,
                            $this->formatDate($booking->booking_date)
                        ),
                        'related_booking_id' => $booking->id,
                        'is_read' => false,
                    ]);
                }
            }

            Log::info('Booking cancelled notifications sent', [
                'booking_id' => $booking->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send booking cancelled notifications', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Kirim reminder booking (1 hari sebelum)
     *
     * @param Booking $booking
     * @return void
     */
    public function sendBookingReminder(Booking $booking): void
    {
        try {
            $booking->load(['user', 'room']);

            Notification::create([
                'user_id' => $booking->user_id,
                'type' => Notification::TYPE_BOOKING_REMINDER,
                'title' => 'Pengingat Peminjaman',
                'message' => sprintf(
                    'Reminder: Anda memiliki peminjaman %s besok (%s) pukul %s - %s',
                    $booking->room->name,
                    $this->formatDate($booking->booking_date),
                    $this->formatTime($booking->start_time),
                    $this->formatTime($booking->end_time)
                ),
                'related_booking_id' => $booking->id,
                'is_read' => false,
            ]);

            Log::info('Booking reminder sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send booking reminder', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mark notification as read
     *
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        try {
            $notification = Notification::where('id', $notificationId)
                ->where('user_id', $userId)
                ->firstOrFail();

            $notification->markAsRead();

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Mark all notifications as read for user
     *
     * @param int $userId
     * @return int
     */
    public function markAllAsRead(int $userId): int
    {
        try {
            return Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Get unread notification count
     *
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get recent notifications for user
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentNotifications(int $userId, int $limit = 10)
    {
        return Notification::where('user_id', $userId)
            ->with(['booking.room'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Delete old read notifications (cleanup)
     *
     * @param int $daysOld
     * @return int
     */
    public function deleteOldNotifications(int $daysOld = 30): int
    {
        try {
            $date = now()->subDays($daysOld);

            return Notification::where('is_read', true)
                ->where('created_at', '<', $date)
                ->delete();

        } catch (\Exception $e) {
            Log::error('Failed to delete old notifications', [
                'days_old' => $daysOld,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Safely format date value regardless of underlying type
     */
    private function formatDate($value, string $format = 'd/m/Y'): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format($format);
        }

        return Carbon::parse($value)->format($format);
    }

    /**
     * Safely format time value regardless of underlying type
     */
    private function formatTime($value, string $format = 'H:i'): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format($format);
        }

        return Carbon::parse($value)->format($format);
    }

    private function borrowerStatusUrl(): string
    {
        try {
            return route('bookings.history');
        } catch (\Throwable $e) {
            return url('/dashboard');
        }
    }
}
