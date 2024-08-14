<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // #JSOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOON
        $records=Storage::json('/public/users.json');
        foreach($records as $record){
            $user=User::create([
            'name'=>$record['name'],
            'email'=>$record['email'],
            'phone_number'=>$record['phone_number'],
            'email_verified_at'=>$record['email_verified_at'],
            'password'=>bcrypt(str($record['password'])),
            'image'=>$record['image'],
            'point'=>$record['point'],
            'is_approved'=>$record['is_approved'],
            ]);
            $user->assignRole('User');
        }
        $records_countries=Storage::json('/public/countries.json');
        foreach($records_countries as $record){
            Country::create([
                'name'=>$record['name']
            ]);
        }
        $records_area=Storage::json('/public/areas.json');
        foreach($records_area as $record){
            Area::create([
                'name'=>$record['name'],
                'country_id'=>$record['country_id']
            ]);
        }
    }
}
