<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories=[
            [
                'name'=>'Sabzavotlar',
            ],
            [
                'name'=>'Mevalar',
            ],
            [
                'name'=>'Go‘sht mahsulotlari',
            ],
            [
                'name'=>'Sut mahsulotlari',
            ],
            [
                'name'=>'Don va un mahsulotlari',
            ],
            [
                'name'=>'Non mahsulotlari',
            ],
            [
                'name'=>'Ziravorlar va qo‘shimchalar',
            ],
            [
                'name'=>'Yog‘ mahsulotlari',
            ],
            [
                'name'=>'Shakar va shirinliklar',
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::create([
                'name'=>$category['name'],
            ]);
        }
    }
}
