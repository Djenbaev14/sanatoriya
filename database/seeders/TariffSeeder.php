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
                'name'=>'4-ўринли хона',
                'daily_price'=>'60000',
                'partner_daily_price'=>'30000'
            ],
            [
                'name'=>'2-ўринли хона',
                'daily_price'=>'85000',
                'partner_daily_price'=>'42500'
            ],
        ];

        foreach ($tariffs as $tariff) {
            Tariff::create([
                'name'=>$tariff['name'],
                'daily_price'=>$tariff['daily_price'],
                'partner_daily_price'=>$tariff['partner_daily_price'],
            ]);
        }
    }
}
