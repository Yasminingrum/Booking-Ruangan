<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Admin Tata Usaha',
            'email' => 'admin@palembangharapan.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        // Kepala Sekolah
        User::create([
            'name' => 'Kepala Sekolah',
            'email' => 'kepsek@palembangharapan.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sekolah',
            'phone' => '08123456788',
            'is_active' => true,
        ]);

        // Cleaning Service
        User::create([
            'name' => 'Petugas Kebersihan',
            'email' => 'cleaning@palembangharapan.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'cleaning_service',
            'phone' => '08123456787',
            'is_active' => true,
        ]);

        // Guru/Peminjam 1
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@palembangharapan.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'peminjam',
            'phone' => '08123456786',
            'is_active' => true,
        ]);

        // Guru/Peminjam 2
        User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti.nurhaliza@palembangharapan.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'peminjam',
            'phone' => '08123456785',
            'is_active' => true,
        ]);

        // Pelatih Ekstrakurikuler
        User::create([
            'name' => 'Andi Wijaya',
            'email' => 'andi.wijaya@palembangharapan.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'peminjam',
            'phone' => '08123456784',
            'is_active' => true,
        ]);
    }
}
