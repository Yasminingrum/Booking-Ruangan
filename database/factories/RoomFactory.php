<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            'laboratorium' => [
                'names' => ['Lab Komputer', 'Lab Fisika', 'Lab Kimia', 'Lab Biologi'],
                'capacity' => [30, 35, 40],
                'facilities' => 'AC, Proyektor, Whiteboard, Internet, Peralatan Lab',
            ],
            'ruang_musik' => [
                'names' => ['Ruang Musik', 'Studio Musik'],
                'capacity' => [25, 30, 35],
                'facilities' => 'AC, Sound System, Piano, Drum Set, Gitar, Microphone',
            ],
            'audio_visual' => [
                'names' => ['Ruang Audio Visual', 'Aula'],
                'capacity' => [80, 100, 150],
                'facilities' => 'AC, Proyektor HD, Sound System, Layar Besar, Kursi Auditorium',
            ],
            'lapangan_basket' => [
                'names' => ['Lapangan Basket'],
                'capacity' => [100, 150, 200],
                'facilities' => 'Ring Basket, Scorer Table, Tribun Penonton',
            ],
            'kolam_renang' => [
                'names' => ['Kolam Renang'],
                'capacity' => [40, 50, 60],
                'facilities' => 'Kolam Semi Olimpik, Ruang Ganti, Shower, Peralatan Safety',
            ],
        ];

        $type = fake()->randomElement(array_keys($types));
        $typeData = $types[$type];

        return [
            'name' => fake()->randomElement($typeData['names']) . ' ' . fake()->numberBetween(1, 10),
            'type' => $type,
            'capacity' => fake()->randomElement($typeData['capacity']),
            'location' => 'Lantai ' . fake()->numberBetween(1, 3) . ', Gedung ' . fake()->randomElement(['A', 'B', 'C', 'D']),
            'description' => fake()->sentence(10),
            'facilities' => $typeData['facilities'],
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the room is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the room is a laboratory.
     */
    public function laboratory(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'laboratorium',
            'name' => 'Lab ' . fake()->randomElement(['Komputer', 'Fisika', 'Kimia']) . ' ' . fake()->numberBetween(1, 5),
            'capacity' => fake()->randomElement([30, 35, 40]),
            'facilities' => 'AC, Proyektor, Whiteboard, Internet, Peralatan Lab',
        ]);
    }
}
