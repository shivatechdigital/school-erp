<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Model events stay enabled so observers (fees, marks, library stock) run during seeding.
     */
    public function run(): void
    {
        $this->call([
            CoreSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
