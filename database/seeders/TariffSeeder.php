<?php

namespace Database\Seeders;

use App\Models\MealType;
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
                'partner_daily_price'=>'30000',
                'foreign_daily_price'=>'90000',
            ],
            [
                'name'=>'2-ўринли хона',
                'daily_price'=>'85000',
                'partner_daily_price'=>'42500',
                'foreign_daily_price'=>'127500',
            ],
        ];
        $meal_types=[
            [
                'name'=>'Питание',
                'daily_price'=>'38000',
                'partner_daily_price'=>'38000',
                'foreign_daily_price'=>'57000',
            ]
        ];
        foreach ($meal_types as $key => $meal_type) {
            MealType::create([
                'name'=>$meal_type['name'],
                'daily_price'=>$meal_type['daily_price'],
                'partner_daily_price'=>$meal_type['partner_daily_price'],
                'foreign_daily_price'=>$meal_type['foreign_daily_price'],
            ]);
        }

        foreach ($tariffs as $tariff) {
            Tariff::create([
                'name'=>$tariff['name'],
                'daily_price'=>$tariff['daily_price'],
                'partner_daily_price'=>$tariff['partner_daily_price'],
                'foreign_daily_price'=>$tariff['foreign_daily_price'],
            ]);
        }
    }
}
