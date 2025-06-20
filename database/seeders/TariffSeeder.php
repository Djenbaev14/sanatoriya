<?php

namespace Database\Seeders;

use App\Models\Tariff;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TariffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $tariffs=[
            [
                'name'=>'Стандарт',
                'daily_price'=>'55000'
            ],
            [
                'name'=>'Люкс',
                'daily_price'=>'70000'
            ],
        ];

        foreach ($tariffs as $tariff) {
            Tariff::create([
                'name'=>$tariff['name'],
                'daily_price'=>$tariff['daily_price'],
            ]);
        }
    }
}
