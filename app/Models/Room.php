<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'capacity',
        'location',
        'description',
        'facilities',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    const TYPE_LABORATORIUM = 'laboratorium';
    const TYPE_RUANG_MUSIK = 'ruang_musik';
    const TYPE_AUDIO_VISUAL = 'audio_visual';
    const TYPE_LAPANGAN_BASKET = 'lapangan_basket';
    const TYPE_KOLAM_RENANG = 'kolam_renang';

    public static function getTypes(): array
    {
        return [
            self::TYPE_LABORATORIUM,
            self::TYPE_RUANG_MUSIK,
            self::TYPE_AUDIO_VISUAL,
            self::TYPE_LAPANGAN_BASKET,
            self::TYPE_KOLAM_RENANG,
        ];
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function approvedBookings()
    {
        return $this->hasMany(Booking::class)->where('status', Booking::STATUS_APPROVED);
    }

    public function isAvailable(string $date, string $startTime, string $endTime, ?int $excludeBookingId = null): bool
    {
        $query = $this->bookings()
            ->where('booking_date', $date)
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_APPROVED])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->count() === 0;
    }

    public function getBookingsForDate(string $date)
    {
        return $this->bookings()
            ->where('booking_date', $date)
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_APPROVED])
            ->orderBy('start_time')
            ->get();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeMinCapacity($query, int $capacity)
    {
        return $query->where('capacity', '>=', $capacity);
    }
}
