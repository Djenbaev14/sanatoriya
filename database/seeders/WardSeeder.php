<?php

namespace Database\Seeders;

use App\Models\Ward;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $tariffs=[
            [
                'name'=>'1-палата',
                'tariff_id'=>2
            ],
            [
                'name'=>'2-палата',
                'tariff_id'=>2
            ],
            [
                'name'=>'3-палата',
                'tariff_id'=>1
            ],
            [
                'name'=>'4-палата',
                'tariff_id'=>1
            ],
            [
                'name'=>'5-палата',
                'tariff_id'=>1
            ],
            [
                'name'=>'6-палата',
                'tariff_id'=>1
            ],
            [
                'name'=>'7-палата',
                'tariff_id'=>1
            ],
        ];

        foreach ($tariffs as $tariff) {
            Ward::create([
                'name'=>$tariff['name'],
                'tariff_id'=>$tariff['tariff_id'],
            ]);
        }
    }
}
