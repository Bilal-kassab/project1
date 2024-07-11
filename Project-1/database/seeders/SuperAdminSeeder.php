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

        // Creating Admin
        $admin = User::create([
            'name' => 'Trip manger',
            'email' => 'Trip@gmail.com',
            'password' => Hash::make('123456789'),
            'position'=>1,
            'is_approved'=>true
        ]);
        $admin->assignRole('Trip manger');

        // Hotel Admin
        $admin = User::create([
            'name' => 'Hotel admin',
            'email' => 'Hotel@gmail.com',
            'password' => Hash::make('123456789'),
            'position'=>1,
            'is_approved'=>true
        ]);
        $admin->assignRole('Hotel admin');

        $admin = User::create([
            'name' => 'Hotel admin2',
            'email' => 'Hotel2@gmail.com',
            'password' => Hash::make('123456789'),
            'position'=>1,
            'is_approved'=>true
        ]);
        $admin->assignRole('Hotel admin');


        // Airport Admin
        $user = User::create([
            'name' => 'Airport admin',
            'email' => 'Airport@gmail.com',
            'password' => Hash::make('123456789'),
            'is_approved'=>true,
            'position'=>1,
        ]);
        $user->assignRole('Airport admin');


        $user = User::create([
            'name' => 'Airport admin2',
            'email' => 'Airport2@gmail.com',
            'password' => Hash::make('123456789'),
            'is_approved'=>true,
            'position'=>2,
        ]);

        $user->assignRole('Airport admin');


        // user Admin
        $user = User::create([
            'name' => 'User',
            'email' => 'User@gmail.com',
            'password' => Hash::make('123456789'),
            'is_approved'=>true,
            'position'=>1,
        ]);
        $user->assignRole('User');

         // Hotel Admin
         $admin = User::create([
            'name' => 'Hotel admin3',
            'email' => 'Hotel3@gmail.com',
            'password' => Hash::make('123456789'),
            'position'=>1,
            'is_approved'=>true
        ]);
        $admin->assignRole('Hotel admin');

        $admin = User::create([
            'name' => 'Hotel admin4',
            'email' => 'Hotel4@gmail.com',
            'password' => Hash::make('123456789'),
            'position'=>1,
            'is_approved'=>true
        ]);
        $admin->assignRole('Hotel admin');

        $users=User::get();
        foreach($users as $user){
            Bank::create([
                'email'=>$user->email,
                'money'=>20000,
                'payments'=>0,
            ]);
        }
    }
}
