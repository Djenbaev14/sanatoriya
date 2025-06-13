<?php

namespace Database\Seeders;

use App\Models\MealType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $meals=[
            [
                'name'=>'To\'liq ovqatlanish',
                'description'=>'3 vaqt ovqat - nonushta, tushlik, kechki ovqat',
                'daily_price'=>'55000'
            ],
            [
                'name'=>'Faqat nonushta',
                'description'=>'Faqat ertalabki ovqat',
                'daily_price'=>'15000'
            ],
            [
                'name'=>'Ovqatsiz',
                'description'=>'Ovqat kiritilmagan',
                'daily_price'=>'0'
            ],
        ];

        foreach ($meals as $meal) {
            MealType::create([
                'name'=>$meal['name'],
                'description'=>$meal['description'],
                'daily_price'=>$meal['daily_price'],
            ]);
        }
    }
}
