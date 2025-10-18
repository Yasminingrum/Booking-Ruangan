<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Role constants
     */
    const ROLE_PEMINJAM = 'peminjam';
    const ROLE_ADMIN = 'admin';
    const ROLE_KEPALA_SEKOLAH = 'kepala_sekolah';
    const ROLE_CLEANING_SERVICE = 'cleaning_service';
    const ROLE_GURU = 'guru';

    /**
     * Relationship: User has many Bookings
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    /**
     * Relationship: User has many Notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Relationship: User has many BookingHistories as changer
     */
    public function bookingHistories()
    {
        return $this->hasMany(BookingHistory::class, 'changed_by_user_id');
    }

    /**
     * Relationship: User has many approved bookings
     */
    public function approvedBookings()
    {
        return $this->hasMany(Booking::class, 'approved_by');
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is peminjam
     */
    public function isPeminjam(): bool
    {
        return $this->role === self::ROLE_PEMINJAM;
    }

    /**
     * Check if user is kepala sekolah
     */
    public function isKepalaSekolah(): bool
    {
        return $this->role === self::ROLE_KEPALA_SEKOLAH;
    }

    /**
     * Check if user is cleaning service
     */
    public function isCleaningService(): bool
    {
        return $this->role === self::ROLE_CLEANING_SERVICE;
    }

    /**
     * Scope: Get active users only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get users by role
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
