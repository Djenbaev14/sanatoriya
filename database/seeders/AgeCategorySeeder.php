<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $types=[
            [
                'name'=>'4-10 yosh',
            ],
            [
                'name'=>'11-18 yosh',
            ],
            [
                'name'=>'18yoshdan katta',
            ],
        ];

        foreach ($types as $type) {
            \App\Models\AgeCategory::create([
                'name'=>$type['name'],
            ]);
        }
    }
}