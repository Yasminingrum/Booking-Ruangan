<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'room_id',
        'booking_date',
        'start_time',
        'end_time',
        'purpose',
        'participants',
        'status',
        'rejection_reason',
        'is_recurring',
        'recurring_pattern',
        'approved_by',
        'approved_at',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'approved_at' => 'datetime',
            'is_recurring' => 'boolean',
            'participants' => 'integer',
        ];
    }

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    /**
     * Relationship: Booking belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Booking belongs to Room
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Relationship: Booking belongs to Admin (approver)
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relationship: Booking has many Histories
     */
    public function histories()
    {
        return $this->hasMany(BookingHistory::class, 'booking_id');
    }

    /**
     * Relationship: Booking has many Notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'related_booking_id');
    }

    /**
     * Check if booking is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if booking is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if booking is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if booking is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if booking is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Approve booking
     */
    public function approve(int $adminId): bool
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        // Create history
        BookingHistory::create([
            'booking_id' => $this->id,
            'changed_by_user_id' => $adminId,
            'old_status' => $oldStatus,
            'new_status' => self::STATUS_APPROVED,
            'notes' => 'Booking disetujui oleh admin',
        ]);

        return true;
    }

    /**
     * Reject booking
     */
    public function reject(int $adminId, string $reason): bool
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        // Create history
        BookingHistory::create([
            'booking_id' => $this->id,
            'changed_by_user_id' => $adminId,
            'old_status' => $oldStatus,
            'new_status' => self::STATUS_REJECTED,
            'notes' => 'Booking ditolak. Alasan: ' . $reason,
        ]);

        return true;
    }

    /**
     * Cancel booking
     */
    public function cancel(int $userId): bool
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);

        // Create history
        BookingHistory::create([
            'booking_id' => $this->id,
            'changed_by_user_id' => $userId,
            'old_status' => $oldStatus,
            'new_status' => self::STATUS_CANCELLED,
            'notes' => 'Booking dibatalkan oleh user',
        ]);

        return true;
    }

    /**
     * Mark booking as completed
     */
    public function complete(): bool
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_COMPLETED,
        ]);

        // Create history
        BookingHistory::create([
            'booking_id' => $this->id,
            'changed_by_user_id' => $this->user_id,
            'old_status' => $oldStatus,
            'new_status' => self::STATUS_COMPLETED,
            'notes' => 'Booking selesai',
        ]);

        return true;
    }

    /**
     * Validate conflict with other bookings
     */
    public function validateConflict(?int $excludeBookingId = null): bool
    {
        $query = self::where('room_id', $this->room_id)
            ->where('booking_date', $this->booking_date)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED])
            ->where(function ($q) {
                $q->whereBetween('start_time', [$this->start_time, $this->end_time])
                  ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                  ->orWhere(function ($q2) {
                      $q2->where('start_time', '<=', $this->start_time)
                         ->where('end_time', '>=', $this->end_time);
                  });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return !$query->exists();
    }

    /**
     * Get formatted booking details
     */
    public function getDetails(): array
    {
        return [
            'id' => $this->id,
            'room' => $this->room->name,
            'date' => Carbon::parse($this->booking_date)->format('d M Y'),
            'time' => $this->start_time . ' - ' . $this->end_time,
            'purpose' => $this->purpose,
            'participants' => $this->participants,
            'status' => $this->status,
            'user' => $this->user->name,
        ];
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Get pending bookings
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Get approved bookings
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope: Filter by date
     */
    public function scopeForDate($query, string $date)
    {
        return $query->where('booking_date', $date);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeForDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('booking_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Get upcoming bookings
     */
    public function scopeUpcoming($query)
    {
        return $query->where('booking_date', '>=', now()->toDateString())
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
