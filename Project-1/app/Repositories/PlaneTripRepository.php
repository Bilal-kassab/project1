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
            // create Going trip
            $flightDate=new Carbon($data['going_flight_date']);
            $landingDate=$flightDate->addHours($data['flight_duration']);
            $goingTrip=PlaneTrip::create([
                'plane_id'=>$data['plane_id'],
                // 'airport_source_id'=>$data['airport_source_id'],
                'airport_source_id'=>$airport['id'],
                'airport_destination_id'=>$data['airport_destination_id'],
                'country_source_id'=>Airport::where('id',$airport['id'])->first()['country_id'],
                'country_destination_id'=>Airport::where('id',$data['airport_destination_id'])->first()['country_id'],
                'current_price'=>$plane['ticket_price'],
                'available_seats'=>$plane['number_of_seats'],
                'flight_date'=>$data['going_flight_date'],
                'landing_date'=>$landingDate,
                'flight_duration'=>$data['flight_duration']
            ]);
            $planetrip1=PlaneTrip::getTripDetails()
                                 ->where('id',$goingTrip->id)->first();

            // create return trip
            $flightDate=new Carbon($data['return_flight_date']);
            $landingDate=$flightDate->addHours($data['flight_duration']);
            $returnTrip=PlaneTrip::create([
                'plane_id'=>$data['plane_id'],
                // 'airport_source_id'=>$data['airport_source_id'],
                'airport_source_id'=>$data['airport_destination_id'],
                'airport_destination_id'=>$airport['id'],
                'country_source_id'=>Airport::where('id',$data['airport_destination_id'])->first()['country_id'],
                'country_destination_id'=>Airport::where('id',$airport['id'])->first()['country_id'],
                'current_price'=>$plane['ticket_price'],
                'available_seats'=>$plane['number_of_seats'],
                'flight_date'=>$data['return_flight_date'],
                'landing_date'=>$landingDate,
                'flight_duration'=>$data['flight_duration']
            ]);
            $planetrip2=PlaneTrip::getTripDetails()
                                 ->where('id',$returnTrip->id)->first();
            return [
                'going_trip'=>$planetrip1,
                'return_trip'=>$planetrip2,
            ];
        }
        else{
            return null;
        }

    }

    public function getAllTripForCountry($data)
    {
        $going_trip= PlaneTrip::getTripDetails()
                            ->where('country_source_id',$data['country_source_id'])
                            ->where('country_destination_id',$data['country_destination_id'])
                            ->where('flight_date','>=',$data['flight_date'])
                            ->get();
        $return_trip= PlaneTrip::getTripDetails()
                            ->where('country_source_id',$data['country_destination_id'])
                            ->where('country_destination_id',$data['country_source_id'])
                            ->where('flight_date','>=',$data['flight_date'])
                            ->get();
        return [
          'going_trip'=>$going_trip,
          'return_trip'=>$return_trip
        ];
    }


}
