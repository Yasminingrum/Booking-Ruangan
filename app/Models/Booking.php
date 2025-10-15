<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

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

    protected $casts = [
        'booking_date' => 'date',
        'is_recurring' => 'boolean',
        'participants' => 'integer',
        'approved_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETED,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function histories()
    {
        return $this->hasMany(BookingHistory::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'related_booking_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function approve(int $approvedBy): bool
    {
        $oldStatus = $this->status;

        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $approvedBy;
        $this->approved_at = now();

        if ($this->save()) {
            $this->createHistory($approvedBy, $oldStatus, self::STATUS_APPROVED, 'Booking approved');
            return true;
        }

        return false;
    }

    public function reject(int $rejectedBy, string $reason): bool
    {
        $oldStatus = $this->status;

        $this->status = self::STATUS_REJECTED;
        $this->rejection_reason = $reason;

        if ($this->save()) {
            $this->createHistory($rejectedBy, $oldStatus, self::STATUS_REJECTED, "Booking rejected: {$reason}");
            return true;
        }

        return false;
    }

    public function cancel(int $cancelledBy): bool
    {
        $oldStatus = $this->status;

        $this->status = self::STATUS_CANCELLED;

        if ($this->save()) {
            $this->createHistory($cancelledBy, $oldStatus, self::STATUS_CANCELLED, 'Booking cancelled by user');
            return true;
        }

        return false;
    }

    public function complete(): bool
    {
        $oldStatus = $this->status;

        $this->status = self::STATUS_COMPLETED;

        if ($this->save()) {
            $this->createHistory($this->user_id, $oldStatus, self::STATUS_COMPLETED, 'Booking automatically completed');
            return true;
        }

        return false;
    }

    public function validateConflict(?int $excludeBookingId = null): bool
    {
        return $this->room->isAvailable(
            $this->booking_date->format('Y-m-d'),
            $this->start_time,
            $this->end_time,
            $excludeBookingId ?? $this->id
        );
    }

    public function getDetails(): array
    {
        return [
            'id' => $this->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'room_name' => $this->room->name,
            'room_type' => $this->room->type,
            'booking_date' => $this->booking_date->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'purpose' => $this->purpose,
            'participants' => $this->participants,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }

    protected function createHistory(int $changedBy, ?string $oldStatus, string $newStatus, ?string $notes = null): void
    {
        BookingHistory::create([
            'booking_id' => $this->id,
            'changed_by_user_id' => $changedBy,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
        ]);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('booking_date', $date);
    }

    public function scopeForDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('booking_date', [$startDate, $endDate]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('booking_date', '>=', now()->toDateString())
                     ->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }
}
