<?php

namespace Database\Seeders;

use App\Models\DailyService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DailyServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dailyservices=[
            [
                'name'=>'Пастел',
                'price'=>85000
            ],
            [
                'name'=>'Питание',
                'price'=>38000
            ],
        ];

        foreach ($dailyservices as $dailyservice) {
            DailyService::create([
                'name'=>$dailyservice['name'],
                'price_per_day'=>$dailyservice['price'],
            ]);
        }
    }
}
