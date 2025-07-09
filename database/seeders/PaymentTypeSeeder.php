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
                'commission_percent'=>0.3, // % komissiya
            ],
            [
                'name'=>'Терминал',
                'commission_percent'=>0.2, // % komissiya
            ],
            [
                'name'=>'Перечисление',
                'commission_percent'=>0, // % komissiya
            ],
        ];

        foreach ($types as $type) {
            PaymentType::create([
                'name'=>$type['name'],
                'commission_percent'=>$type['commission_percent'],
            ]);
        }
    }
}
