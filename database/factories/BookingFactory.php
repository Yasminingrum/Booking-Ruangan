<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = fake()->numberBetween(7, 15);
        $duration = fake()->randomElement([1, 2, 3, 4]);
        $endHour = $startHour + $duration;

        $purposes = [
            'Praktikum Pemrograman Web',
            'Latihan Paduan Suara',
            'Rapat OSIS',
            'Latihan Basket',
            'Pembelajaran Renang',
            'Presentasi Project',
            'Workshop Desain Grafis',
            'Ujian Praktik',
            'Latihan Drama',
            'Seminar Parenting',
        ];

        return [
            'user_id' => User::factory(),
            'room_id' => Room::factory(),
            'booking_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', $endHour),
            'purpose' => fake()->randomElement($purposes),
            'participants' => fake()->numberBetween(10, 40),
            'status' => 'pending',
            'rejection_reason' => null,
            'is_recurring' => false,
            'recurring_pattern' => null,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the booking is approved.
     */
    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            $admin = User::where('role', 'admin')->first() ?? User::factory()->admin()->create();

            return [
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the booking is rejected.
     */
    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            $admin = User::where('role', 'admin')->first() ?? User::factory()->admin()->create();

            return [
                'status' => 'rejected',
                'rejection_reason' => fake()->randomElement([
                    'Ruangan sudah dipesan untuk acara sekolah',
                    'Waktu peminjaman bertabrakan dengan jadwal lain',
                    'Ruangan sedang dalam perbaikan',
                    'Tidak sesuai dengan peraturan peminjaman',
                ]),
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the booking is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the booking is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'booking_date' => fake()->dateTimeBetween('-30 days', '-1 day')->format('Y-m-d'),
            ];
        });
    }

    /**
     * Indicate that the booking is recurring.
     */
    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'recurring_pattern' => fake()->randomElement([
                'weekly_monday',
                'weekly_wednesday',
                'weekly_friday',
                'biweekly',
            ]),
        ]);
    }

    /**
     * Indicate that the booking is for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'booking_date' => Carbon::today()->format('Y-m-d'),
        ]);
    }
}
