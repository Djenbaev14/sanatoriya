<?php

namespace Database\Seeders;

use App\Models\StatusPayment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types=[
            [
                'key'=>'pending',
                'name'=>'в ожидании',
            ],
            [
                'key'=>'to_cashier',
                'name'=>'В кассе',
            ],
            [
                'key'=>'completed',
                'name'=>'завершенный',
            ],
            [
                'key'=>'cancelled',
                'name'=>'отменённый',
            ],
        ];

        foreach ($types as $type) {
            StatusPayment::create([
                'key'=>$type['key'],
                'name'=>$type['name']
            ]);
        }
    }
}
