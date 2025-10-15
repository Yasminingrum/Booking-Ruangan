<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BookingHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'booking_histories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'booking_id',
        'changed_by_user_id',
        'old_status',
        'new_status',
        'notes',
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    /**
     * Relationship: BookingHistory belongs to Booking
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /**
     * Relationship: BookingHistory belongs to User (who made the change)
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    /**
     * Get formatted history
     */
    public function getFormattedHistory(): string
    {
        $date = Carbon::parse($this->created_at)->format('d M Y H:i');
        $userName = $this->changedBy->name;
        $oldStatus = $this->old_status ?? 'N/A';
        $newStatus = $this->new_status;

        return "{$date}: {$userName} mengubah status dari '{$oldStatus}' ke '{$newStatus}'";
    }

    /**
     * Scope: Get history for specific booking
     */
    public function scopeForBooking($query, int $bookingId)
    {
        return $query->where('booking_id', $bookingId);
    }

    /**
     * Scope: Get history by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('changed_by_user_id', $userId);
    }

    /**
     * Scope: Filter by status change
     */
    public function scopeStatusChange($query, string $status)
    {
        return $query->where('new_status', $status);
    }
}
