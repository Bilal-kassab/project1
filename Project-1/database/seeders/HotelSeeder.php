<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotels=['Damascus Hotel','Al Marja Hotel'];
        foreach($hotels as $hotel){
            Hotel::create([
                'user_id'=>3,
                'name'=>$hotel,
                'number_rooms'=>30,
                'country_id'=>1,
                'area_id'=>1,
                'stars'=>4
            ]);
        }
        for($i=0;$i<10;$i++){
            Room::create([
                'hotel_id'=>1,
                'capacity'=>2,
                'price'=>20
            ]);
        }
        for($i=0;$i<10;$i++){
            Room::create([
                'hotel_id'=>1,
                'capacity'=>4,
                'price'=>28
            ]);
        }
        for($i=0;$i<10;$i++){
            Room::create([
                'hotel_id'=>1,
                'capacity'=>6,
                'price'=>45
            ]);
        }



        $hotels=['Paris_1_Hotel','Paris_2_Hotel'];
        foreach($hotels as $hotel){
            Hotel::create([
                'user_id'=>3,
                'name'=>$hotel,
                'number_rooms'=>30,
                'country_id'=>2,
                'area_id'=>4,
                'stars'=>4
            ]);
        }
        for($i=0;$i<10;$i++){
            Room::create([
                'hotel_id'=>3,
                'capacity'=>2,
                'price'=>20
            ]);
        }
        for($i=0;$i<10;$i++){
            Room::create([
                'hotel_id'=>3,
                'capacity'=>4,
                'price'=>30
            ]);
        }
        for($i=0;$i<10;$i++){
            Room::create([
                'hotel_id'=>3,
                'capacity'=>6,
                'price'=>40
            ]);
        }
    }
}
