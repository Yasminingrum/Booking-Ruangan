<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'related_booking_id',
        'is_read',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Notification type constants
     */
    const TYPE_BOOKING_CREATED = 'booking_created';
    const TYPE_BOOKING_APPROVED = 'booking_approved';
    const TYPE_BOOKING_REJECTED = 'booking_rejected';
    const TYPE_BOOKING_REMINDER = 'booking_reminder';
    const TYPE_BOOKING_CANCELLED = 'booking_cancelled';

    /**
     * Relationship: Notification belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Notification belongs to Booking
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'related_booking_id');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): bool
    {
        return $this->update(['is_read' => false]);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->is_read;
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return !$this->is_read;
    }

    /**
     * Static method: Create booking created notification
     */
    public static function createBookingCreated(int $userId, int $bookingId, string $roomName, string $userName): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_BOOKING_CREATED,
            'title' => 'Pengajuan Peminjaman Baru',
            'message' => "{$userName} mengajukan peminjaman {$roomName}",
            'related_booking_id' => $bookingId,
            'is_read' => false,
        ]);
    }

    /**
     * Static method: Create booking approved notification
     */
    public static function createBookingApproved(int $userId, int $bookingId, string $roomName): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_BOOKING_APPROVED,
            'title' => 'Peminjaman Disetujui',
            'message' => "Peminjaman {$roomName} Anda telah disetujui",
            'related_booking_id' => $bookingId,
            'is_read' => false,
        ]);
    }

    /**
     * Static method: Create booking rejected notification
     */
    public static function createBookingRejected(int $userId, int $bookingId, string $roomName, string $reason): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_BOOKING_REJECTED,
            'title' => 'Peminjaman Ditolak',
            'message' => "Peminjaman {$roomName} ditolak. Alasan: {$reason}",
            'related_booking_id' => $bookingId,
            'is_read' => false,
        ]);
    }

    /**
     * Static method: Create booking cancelled notification
     */
    public static function createBookingCancelled(int $userId, int $bookingId, string $roomName): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_BOOKING_CANCELLED,
            'title' => 'Peminjaman Dibatalkan',
            'message' => "Peminjaman {$roomName} telah dibatalkan",
            'related_booking_id' => $bookingId,
            'is_read' => false,
        ]);
    }

    /**
     * Scope: Get unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: Get read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Get recent notifications (last 30 days)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }
}
