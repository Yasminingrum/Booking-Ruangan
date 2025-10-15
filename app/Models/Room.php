<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'type',
        'capacity',
        'location',
        'description',
        'facilities',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'capacity' => 'integer',
        ];
    }

    /**
     * Room type constants
     */
    const TYPE_LABORATORIUM = 'laboratorium';
    const TYPE_RUANG_MUSIK = 'ruang_musik';
    const TYPE_AUDIO_VISUAL = 'audio_visual';
    const TYPE_LAPANGAN_BASKET = 'lapangan_basket';
    const TYPE_KOLAM_RENANG = 'kolam_renang';

    /**
     * Relationship: Room has many Bookings
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'room_id');
    }

    /**
     * Relationship: Get approved bookings only
     */
    public function approvedBookings()
    {
        return $this->hasMany(Booking::class, 'room_id')
            ->where('status', Booking::STATUS_APPROVED);
    }

    /**
     * Check if room is available at specific date and time
     */
    public function isAvailable(string $date, string $startTime, string $endTime, ?int $excludeBookingId = null): bool
    {
        $query = $this->bookings()
            ->where('booking_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($startTime, $endTime) {
                // Check for time overlaps
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            });

        // Exclude specific booking (for updates)
        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return !$query->exists();
    }

    /**
     * Get bookings for specific date
     */
    public function getBookingsForDate(string $date)
    {
        return $this->bookings()
            ->where('booking_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Scope: Get active rooms only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get rooms by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filter by minimum capacity
     */
    public function scopeMinCapacity($query, int $capacity)
    {
        return $query->where('capacity', '>=', $capacity);
    }
}
