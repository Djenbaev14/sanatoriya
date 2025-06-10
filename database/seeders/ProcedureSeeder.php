<?php

namespace Database\Seeders;

use App\Models\Procedure;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProcedureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $procedures=[
            [
                'name'=>'Физиотерапевтические процедуры',
                'price'=>85000
            ],
            [
                'name'=>'Массаж',
                'price'=>50000
            ],
            [
                'name'=>'Водолечебные процедуры',
                'price'=>65000
            ],
            [
                'name'=>'Климато и спелеотерапия',
                'price'=>40000
            ],
            [
                'name'=>'Грязелечение и парафинолечение',
                'price'=>35000
            ],
            [
                'name'=>'ЛФК и дыхательная гимнастика',
                'price'=>50000
            ],
        ];

        foreach ($procedures as $procedure) {
            Procedure::create([
                'name'=>$procedure['name'],
                'price_per_day'=>$procedure['price'],
            ]);
        }
    }
}
