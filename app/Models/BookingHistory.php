<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingHistory extends Model
{
    use HasFactory;

    protected $table = 'booking_histories';

    public $timestamps = false;

    protected $dates = ['created_at'];

    protected $fillable = [
        'booking_id',
        'changed_by_user_id',
        'old_status',
        'new_status',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->created_at) {
                $model->created_at = now();
            }
        });
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    public function getFormattedHistory(): string
    {
        $userName = $this->changedBy->name ?? 'System';
        $date = $this->created_at->format('d M Y H:i');

        $message = "{$userName} changed status";

        if ($this->old_status) {
            $message .= " from '{$this->old_status}'";
        }

        $message .= " to '{$this->new_status}'";

        if ($this->notes) {
            $message .= " - {$this->notes}";
        }

        return "{$date}: {$message}";
    }

    public function scopeForBooking($query, int $bookingId)
    {
        return $query->where('booking_id', $bookingId);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('changed_by_user_id', $userId);
    }

    public function scopeStatusChange($query, string $status)
    {
        return $query->where('new_status', $status);
    }
}
