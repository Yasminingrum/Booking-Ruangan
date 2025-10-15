<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'related_booking_id',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const TYPE_BOOKING_CREATED = 'booking_created';
    const TYPE_BOOKING_APPROVED = 'booking_approved';
    const TYPE_BOOKING_REJECTED = 'booking_rejected';
    const TYPE_BOOKING_REMINDER = 'booking_reminder';
    const TYPE_BOOKING_CANCELLED = 'booking_cancelled';

    public static function getTypes(): array
    {
        return [
            self::TYPE_BOOKING_CREATED,
            self::TYPE_BOOKING_APPROVED,
            self::TYPE_BOOKING_REJECTED,
            self::TYPE_BOOKING_REMINDER,
            self::TYPE_BOOKING_CANCELLED,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'related_booking_id');
    }

    public function markAsRead(): bool
    {
        $this->is_read = true;
        return $this->save();
    }

    public function markAsUnread(): bool
    {
        $this->is_read = false;
        return $this->save();
    }

    public function isRead(): bool
    {
        return $this->is_read;
    }

    public function isUnread(): bool
    {
        return !$this->is_read;
    }

    public static function createBookingCreated(int $userId, int $bookingId, string $roomName, string $userName): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_BOOKING_CREATED,
            'title' => 'Pengajuan Peminjaman Baru',
            'message' => "{$userName} mengajukan peminjaman ruangan {$roomName}. Menunggu persetujuan admin.",
            'related_booking_id' => $bookingId,
            'is_read' => false,
        ]);
    }

    public static function createBookingApproved(int $userId, int $bookingId, string $roomName): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_BOOKING_APPROVED,
            'title' => 'Peminjaman Disetujui',
            'message' => "Peminjaman ruangan {$roomName} Anda telah disetujui oleh admin.",
            'related_booking_id' => $bookingId,
            'is_read' => false,
        ]);
    }

    public static function createBookingRejected(int $userId, int $bookingId, string $roomName, string $reason): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_BOOKING_REJECTED,
            'title' => 'Peminjaman Ditolak',
            'message' => "Peminjaman ruangan {$roomName} Anda ditolak. Alasan: {$reason}",
            'related_booking_id' => $bookingId,
            'is_read' => false,
        ]);
    }

    public static function createBookingCancelled(int $userId, int $bookingId, string $roomName): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_BOOKING_CANCELLED,
            'title' => 'Peminjaman Dibatalkan',
            'message' => "Peminjaman ruangan {$roomName} telah dibatalkan.",
            'related_booking_id' => $bookingId,
            'is_read' => false,
        ]);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }
}
