<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    const ROLE_PEMINJAM = 'peminjam';
    const ROLE_ADMIN = 'admin';
    const ROLE_KEPALA_SEKOLAH = 'kepala_sekolah';
    const ROLE_CLEANING_SERVICE = 'cleaning_service';

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function bookingHistories()
    {
        return $this->hasMany(BookingHistory::class, 'changed_by_user_id');
    }

    public function approvedBookings()
    {
        return $this->hasMany(Booking::class, 'approved_by');
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPeminjam(): bool
    {
        return $this->role === self::ROLE_PEMINJAM;
    }

    public function isKepalaSekolah(): bool
    {
        return $this->role === self::ROLE_KEPALA_SEKOLAH;
    }

    public function isCleaningService(): bool
    {
        return $this->role === self::ROLE_CLEANING_SERVICE;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
