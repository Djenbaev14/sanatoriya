<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnionSeeder extends Seeder
{
     public function run(): void
    {
        $units = [
            ['name' => 'Kilogramm', 'symbol' => 'kg'],
            ['name' => 'Litr', 'symbol' => 'l'],
            ['name' => 'Dona', 'symbol' => 'dona'],
        ];

        DB::table('unions')->insert($units);
    }
}
