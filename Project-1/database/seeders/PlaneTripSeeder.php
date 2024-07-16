<?php

namespace Database\Seeders;

use App\Models\PlaneTrip;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlaneTripSeeder extends Seeder
{
     /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nowDate=Carbon::now()->format('Y-m-d');
        $date= new Carbon($nowDate);
        PlaneTrip::create([
            'plane_id'=>1,
            'airport_source_id'=>1,
            'airport_destination_id'=>2,
            'country_source_id'=>1,
            'country_destination_id'=>2,
            'current_price'=>50,
            'available_seats'=>25,
            'flight_duration'=>24,
            'flight_date'=>$date->format('Y-m-d'),
            'landing_date'=>$date->addDays(1)->format('Y-m-d')
        ]);
        $date->addDays(1);
        PlaneTrip::create([
            'plane_id'=>1,
            'airport_source_id'=>1,
            'airport_destination_id'=>2,
            'country_source_id'=>1,
            'country_destination_id'=>2,
            'current_price'=>50,
            'available_seats'=>25,
            'flight_duration'=>24,
            'flight_date'=>$date->format('Y-m-d'),
            'landing_date'=>$date->addDays(1)->format('Y-m-d')
        ]);
        $date->addDays(1);
        PlaneTrip::create([
            'plane_id'=>3,
            'airport_source_id'=>2,
            'airport_destination_id'=>1,
            'country_source_id'=>2,
            'country_destination_id'=>1,
            'current_price'=>50,
            'available_seats'=>25,
            'flight_duration'=>24,
            'flight_date'=>$date->format('Y-m-d'),
            'landing_date'=>$date->addDays(1)->format('Y-m-d')
        ]);
        $date->addDays(1);
        PlaneTrip::create([
            'plane_id'=>3,
            'airport_source_id'=>2,
            'airport_destination_id'=>1,
            'country_source_id'=>2,
            'country_destination_id'=>1,
            'current_price'=>50,
            'available_seats'=>25,
            'flight_duration'=>24,
            'flight_date'=>$date->format('Y-m-d'),
            'landing_date'=>$date->addDays(1)->format('Y-m-d')
        ]);
    }
}
