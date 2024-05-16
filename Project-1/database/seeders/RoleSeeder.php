<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        Role::create(["name"=> "Super Admin"]);
        Role::create(["name"=> "User"]);
        Role::create(["name"=> "Trip manger"]);
        Role::create(["name"=> "Hotel admin"]);
        Role::create(["name"=> "Airport admin"]);
        $admin=Role::create(["name"=> "Admin"]);



        $admin->givePermissionTo([
            'create-user',
            'edit-user',
            'delete-user',
            'create-product',
            'edit-product',
            'delete-product'
        ]);
    }
}
