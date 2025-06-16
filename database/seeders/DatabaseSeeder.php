<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PaymentStatusSeeder::class,
            RegionSeeder::class,
            UserSeeder::class,
            TariffSeeder::class,
            WardSeeder::class,
            PaymentTypeSeeder::class,
            // MealSeeder::class,
            // ProcedureSeeder::class,
            // LabTestSeeder::class,
        ]);
    }
}
