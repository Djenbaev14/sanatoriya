<?php

namespace Database\Seeders;

use App\Models\PaymentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types=[
            [
                'name'=>'Нак',
            ],
            [
                'name'=>'Терминал',
            ],
        ];

        foreach ($types as $type) {
            PaymentType::create([
                'name'=>$type['name'],
            ]);
        }
    }
}
