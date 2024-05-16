<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creating Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'SA@gmail.com',
            'password' => Hash::make('123456789'),
            'is_approved'=>true
        ]);
        $superAdmin->assignRole('Super Admin');

        // Creating Admin
        $admin = User::create([
            'name' => 'Trip manger',
            'email' => 'Trip@gmail.com',
            'password' => Hash::make('123456789'),
            'is_approved'=>true
        ]);
        $admin->assignRole('Trip manger');

        $admin = User::create([
            'name' => 'Hotel admin',
            'email' => 'Hotel@gmail.com',
            'password' => Hash::make('123456789'),
            'is_approved'=>true
        ]);
        $admin->assignRole('Hotel admin');

        $user = User::create([
            'name' => 'Airport admin',
            'email' => 'Airport@gmail.com',
            'password' => Hash::make('123456789'),
            'is_approved'=>true
        ]);
        $user->assignRole('Airport admin');

        $user = User::create([
            'name' => 'User',
            'email' => 'User@gmail.com',
            'password' => Hash::make('123456789'),
            'is_approved'=>true
        ]);
        $user->assignRole('User');
    }
}
