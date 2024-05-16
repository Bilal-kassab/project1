<?php

namespace Database\Seeders;

use App\Models\PlaneTrip;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlaneTripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PlaneTrip::create([
            'plane_id'=>1,
            'airport_source_id'=>1,
            'airport_destination_id'=>3,
            'country_source_id'=>1,
            'country_destination_id'=>2,
            'current_price'=>200,
            'available_seats'=>25,
            'flight_date'=>"2024-5-15",
            'landing_date'=>"2024-5-20"
        ]);
        PlaneTrip::create([
            'plane_id'=>1,
            'airport_source_id'=>1,
            'airport_destination_id'=>3,
            'country_source_id'=>1,
            'country_destination_id'=>2,
            'current_price'=>200,
            'available_seats'=>25,
            'flight_date'=>"2024-5-25",
            'landing_date'=>"2024-5-29"
        ]);
        PlaneTrip::create([
            'plane_id'=>3,
            'airport_source_id'=>3,
            'airport_destination_id'=>1,
            'country_source_id'=>2,
            'country_destination_id'=>1,
            'current_price'=>200,
            'available_seats'=>25,
            'flight_date'=>"2024-5-15",
            'landing_date'=>"2024-5-20"
        ]);
        PlaneTrip::create([
            'plane_id'=>3,
            'airport_source_id'=>3,
            'airport_destination_id'=>1,
            'country_source_id'=>2,
            'country_destination_id'=>1,
            'current_price'=>200,
            'available_seats'=>25,
            'flight_date'=>"2024-5-25",
            'landing_date'=>"2024-5-29"
        ]);
    }
}
