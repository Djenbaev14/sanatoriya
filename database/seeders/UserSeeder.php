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
        
        $role1=Role::create([
            'name'=>'Доктор'
        ]);
        
        $role2=Role::create([
            'name'=>'Касса'
        ]);
        $role3=Role::create([
            'name'=>'Ассистент'
        ]);
        
        $role4=Role::create([
            'name'=>'Медсестра'
        ]);

        $role->syncPermissions(Permission::all());
        $role1->syncPermissions(Permission::all());
        $role2->syncPermissions(Permission::all());
        $role3->syncPermissions(Permission::all());
        $role4->syncPermissions(Permission::all());
        User::create([
            'name'=>'admin',
            'username'=>'admin123',
            'password'=>Hash::make('admin')
        ])->assignRole('Админ');
    }
}
