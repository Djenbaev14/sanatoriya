<?php

namespace Database\Seeders;

use App\Models\LabTest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LabTestSeeder extends Seeder
{
    public function run(): void
    {
        $lab_tests=[
            ['name' => 'Umumiy qon tahlili', 'price' => 15000],
            ['name' => 'Biokimyoviy qon tahlili', 'price' => 35000],
            ['name' => 'Siydik umumiy tahlili', 'price' => 10000],
            ['name' => 'UZI – Jigar', 'price' => 50000],
            ['name' => 'Qon guruhi aniqlash', 'price' => 20000],
        ];

        foreach ($lab_tests as $lab_test) {
            LabTest::create([
                'name'=>$lab_test['name'],
                'price'=>$lab_test['price'],
            ]);
        }
    }
}
