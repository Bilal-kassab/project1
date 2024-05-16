<?php

namespace App\Repositories;

use App\Helpers\ImageProcess;
use App\Models\Airport;
use App\Models\AirportImage;
use App\Models\Plane;
use App\Models\PlaneTrip;
use App\Repositories\Interfaces\PlaneTripRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlaneTripRepository implements PlaneTripRepositoryInterface
{
    public function addTrip($data)
    {
        $planetrip=null;
        $plane=Plane::where('id',$data['plane_id'])->first();
        $airport=Airport::where('id',$plane->airport_id)->first();
        if($plane['visible'] && $airport['visible'] && auth()->id() == $airport->user_id)
        {
            $trip=PlaneTrip::create([
                'plane_id'=>$data['plane_id'],
                'airport_source_id'=>$data['airport_source_id'],
                'airport_destination_id'=>$data['airport_destination_id'],
                'country_source_id'=>Airport::where('id',$data['airport_source_id'])->first()['country_id'],
                'country_destination_id'=>Airport::where('id',$data['airport_destination_id'])->first()['country_id'],
                'current_price'=>$data['current_price'],
                'available_seats'=>$data['available_seats'],
                'flight_date'=>$data['flight_date'],
                'landing_date'=>$data['landing_date'],
            ]);
            $planetrip=PlaneTrip::getTripDetails()
                                 ->where('id',$trip->id)->first();
        }
        // Airport::with('trips')->where('country_id',1)->get();

        return $planetrip;

    }

    public function getAllTripForCountry($data)
    {
        return PlaneTrip::getTripDetails()
                        ->where('country_source_id',$data['country_source_id'])
                        ->where('country_destination_id',$data['country_destination_id'])
                        ->where('flight_date','>=',$data['flight_date'])
                        ->get();

    }


}
