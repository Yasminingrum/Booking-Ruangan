<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            // Laboratorium Komputer
            [
                'name' => 'Lab Komputer',
                'type' => 'laboratorium',
                'capacity' => 40,
                'location' => 'Lantai 2, Gedung A',
                'description' => 'Laboratorium komputer dengan PC terbaru untuk pembelajaran pemrograman',
                'facilities' => 'AC, Proyektor, 40 Unit PC, Whiteboard, Internet',
                'is_active' => true,
            ],

            // Ruang Musik
            [
                'name' => 'Ruang Musik',
                'type' => 'ruang_musik',
                'capacity' => 30,
                'location' => 'Lantai 1, Gedung B',
                'description' => 'Ruang musik dengan kedap suara untuk latihan band dan paduan suara',
                'facilities' => 'AC, Sound System, Piano, Drum Set, Gitar, Keyboard, Microphone',
                'is_active' => true,
            ],

            // Audio Visual
            [
                'name' => 'Ruang Audio Visual',
                'type' => 'audio_visual',
                'capacity' => 100,
                'location' => 'Lantai 1, Gedung C',
                'description' => 'Ruang serbaguna dengan fasilitas multimedia lengkap',
                'facilities' => 'AC, Proyektor HD, Sound System, Layar Besar, Kursi Auditorium',
                'is_active' => true,
            ],

            // Lapangan Basket
            [
                'name' => 'Lapangan Basket Outdoor 1',
                'type' => 'lapangan_basket',
                'capacity' => 100,
                'location' => 'Area Outdoor',
                'description' => 'Lapangan basket outdoor untuk latihan',
                'facilities' => 'Ring Basket, Lampu Penerangan',
                'is_active' => true,
            ],
            [
                'name' => 'Lapangan Basket Outdoor 2',
                'type' => 'lapangan_basket',
                'capacity' => 100,
                'location' => 'Area Outdoor',
                'description' => 'Lapangan basket outdoor untuk latihan',
                'facilities' => 'Ring Basket, Lampu Penerangan',
                'is_active' => true,
            ],
            [
                'name' => 'Lapangan Basket Outdoor 3',
                'type' => 'lapangan_basket',
                'capacity' => 100,
                'location' => 'Area Outdoor',
                'description' => 'Lapangan basket outdoor untuk latihan',
                'facilities' => 'Ring Basket, Lampu Penerangan',
                'is_active' => true,
            ],

            // Kolam Renang
            [
                'name' => 'Kolam Renang',
                'type' => 'kolam_renang',
                'capacity' => 50,
                'location' => 'Area Olahraga',
                'description' => 'Kolam renang semi olimpik untuk pembelajaran dan latihan',
                'facilities' => 'Kolam Semi Olimpik, Ruang Ganti, Shower, Peralatan Safety',
                'is_active' => true,
            ],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
