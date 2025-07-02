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
            'name'=>'Кассир'
        ]);
        
        $role3=Role::create([
            'name'=>'Приемный врач'
        ]);
        $role4=Role::create([
            'name'=>'Ассистент'
        ]);
        
        $role5=Role::create([
            'name'=>'Медсестра'
        ]);
        
        $role6=Role::create([
            'name'=>'приемная медсестра'
        ]);
        $permissions1=[
            // 'отделение',
            // 'больные',
            // 'история болезни',
            'создать отделение осмотр',
            'создать анализы',
            'создать процедуры',
        ];
        $permissions2=[
            'остаток в кассе',
            'сдано в банк',
            'касса',
            'отделение',
            'больные',
            'создать больной',
            'история болезни',
            'создать историю болезни',
        ];
        $permissions3=[
            'отделение',
            'больные',
            'история болезни',
            'создать условия размещения',
            'создать приемный осмотр',
            'создать отделение осмотр',
            'создать анализы',
            'создать процедуры',
        ];
        $permissions6=[
            'отделение',
            'больные',
            'создать больной',
            'история болезни',
            'создать историю болезни',
        ];

        $role->syncPermissions(Permission::all());
        $role1->syncPermissions($permissions1);
        $role2->syncPermissions($permissions2);
        $role3->syncPermissions($permissions3);
        $role6->syncPermissions($permissions6);
        User::create([
            'name'=>'admin',
            'username'=>'admin123',
            'password'=>Hash::make('admin')
        ])->assignRole('Админ');
    }
}
