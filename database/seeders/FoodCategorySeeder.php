<?php

namespace Database\Seeders;

use App\Models\FoodCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FoodCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories=[
            [
                'name'=>'Блюды',
            ],
            [
                'name'=>'Молочные блюда',
            ],
            [
                'name'=>'Чайные напитки',
            ],

        ];

        foreach ($categories as $category) {
            FoodCategory::create([
                'name'=>$category['name'],
            ]);
        }
    }
}
