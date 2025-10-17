<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            UserSeeder::class,
            RoomSeeder::class,
        ]);

        $this->command->info('Database seeding completed successfully!');
        $this->command->info('Default credentials:');
        $this->command->info('Admin: admin@palembangharapan.sch.id / password123');
        $this->command->info('Kepala Sekolah: kepsek@palembangharapan.sch.id / password123');
        $this->command->info('Peminjam: budi.santoso@palembangharapan.sch.id / password123');
    }
}
