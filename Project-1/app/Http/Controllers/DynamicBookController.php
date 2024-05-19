<?php

namespace App\Http\Controllers;

use App\Http\Requests\Trip\DynamicTripRequest;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\BookPlace;
use App\Models\BookPlane;
use App\Models\Place;
use App\Models\PlaneTrip;
use App\Models\Room;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DynamicBookController extends Controller
{
    public function store_User(DynamicTripRequest $request)
    {
        $date=Carbon::now()->format('Y-m-d');
        try{
           $booking=Booking::create([
            'user_id'=>auth()->user()->id,
            'source_trip_id'=>$request->source_trip_id,
            'destination_trip_id'=>$request->destination_trip_id,
            'trip_name'=>$request->trip_name,
            'number_of_people'=>$request->number_of_people,
            'trip_note'=>$request->trip_note,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date,
            'type'=>'dynamic'
        ]);
        }catch(Exception $exception){
            return response()->json([
                'message'=>$exception->getMessage(),
            ]);

        }
        return response()->json([
            'data'=>$booking
        ],200);
    }

}
