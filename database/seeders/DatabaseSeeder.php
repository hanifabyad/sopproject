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
        // Memanggil seeders agar dijalankan saat migrate --seed
        $this->call([
            UserSeeder::class,
            LibraryFolderSeeder::class,
        ]);
    }
}