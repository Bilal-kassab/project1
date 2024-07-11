<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Country;
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
        $countries=['Syria','France','Germany'];

        foreach ($countries as $country) {
            Country::create(['name' => $country]);
        }
        // Creating Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'SA@gmail.com',
            'password' => Hash::make('123456789'),
            'position'=>1,
            'is_approved'=>true
        ]);
        $superAdmin->assignRole('Super Admin');
        Bank::create([
            'email'=>$superAdmin->email,
            'money'=>50000,
            'payments'=>0,
        ]);
        // Creating Admin
        $admin = User::create([
            'name' => 'Trip manger',
            'email' => 'Trip@gmail.com',
            'password' => Hash::make('123456789'),
            'position'=>1,
            'is_approved'=>true
        ]);
        $admin->assignRole('Trip manger');
        Bank::create([
            'email'=>$admin->email,
            'money'=>20000,
            'payments'=>0,
        ]);
        $admin = User::create([
            'name' => 'Hotel admin',
            'email' => 'Hotel@gmail.com',
            'password' => Hash::make('123456789'),
            'position'=>1,
            'is_approved'=>true
        ]);
        $admin->assignRole('Hotel admin');
        Bank::create([
            'email'=>$admin->email,
            'money'=>20000,
            'payments'=>0,
        ]);
        $user = User::create([
            'name' => 'Airport admin',
            'email' => 'Airport@gmail.com',
            'password' => Hash::make('123456789'),
            'is_approved'=>true,
            'position'=>1,
        ]);
        $user->assignRole('Airport admin');

        Bank::create([
            'email'=>$user->email,
            'money'=>20000,
            'payments'=>0,
        ]);
        // $user = User::create([
        //     'name' => 'Airport admin2',
        //     'email' => 'Airport2@gmail.com',
        //     'password' => Hash::make('123456789'),
        //     'is_approved'=>true,
        //     'position'=>2,
        // ]);

        // $user->assignRole('Airport admin');
        // Bank::create([
        //     'email'=>$user->email,
        //     'money'=>20000,
        //     'payments'=>0,
        // ]);

        $user = User::create([
            'name' => 'User',
            'email' => 'User@gmail.com',
            'password' => Hash::make('123456789'),
            'is_approved'=>true,
            'position'=>1,
        ]);
        $user->assignRole('User');
        Bank::create([
            'email'=>$user->email,
            'money'=>20000,
            'payments'=>0,
        ]);
    }
}
