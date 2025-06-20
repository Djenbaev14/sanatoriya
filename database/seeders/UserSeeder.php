<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role=Role::create([
            'name'=>'Админ'
        ]);

        $role->syncPermissions(Permission::all());
        User::create([
            'name'=>'admin',
            'username'=>'admin123',
            'password'=>Hash::make('admin')
        ])->assignRole('Админ');
    }
}
