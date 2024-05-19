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


class StaticBookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            return response()->json([
                'data'=>Booking::where('type','static')->get(),
            ],200);
        }catch(Exception $e){
            return response()->json([
                'message'=>$e->getMessage(),
            ],404);

        }
    }

    public function store_Admin(Request $request)
    {
        $date=Carbon::now()->format('Y-m-d');
        $validatedData =Validator::make($request->all(),[
            'source_trip_id'=>'required|exists:countries,id',
            'destination_trip_id'=>'required|exists:countries,id',
            'hotel_id'=>'required|exists:hotels,id',
            'trip_name'=>'required|string',
            'price'=>'required|numeric',
            'number_of_people'=>'required|min:3|numeric',
            'trip_capacity'=>'required|numeric',
            'start_date'=>"required|date|after_or_equal:$date",
            'end_date'=>'required|date|after_or_equal:end_date',
            'trip_note'=>'string',
            'places'=>'array|min:1',
            'places.*'=>"required|exists:places,id",
            'plane_trip'=>"required|exists:plane_trips,id",
            'plane_trip_away'=>'required|exists:plane_trips,id',
       ]);
        if( $validatedData->fails() ){
            return response()->json([
                'message'=> $validatedData->errors()->first(),
            ],422);
        }

        $plane_trip=PlaneTrip::where('id',$request->plane_trip)->first();
        if( $plane_trip['available_seats']>=$request->number_of_people){
                $plane_trip['available_seats']-=$request->number_of_people;
                $plane_trip->save();
        }
        else{
            return response()->json([
                'message'=>'the seats of this trip plane lower than number of person'
            ],400);
        }

        $plane_trip_away=PlaneTrip::where('id',$request->plane_trip_away)->first();
        if( $plane_trip_away['available_seats']>=$request->number_of_people){
                $plane_trip_away['available_seats']-=$request->number_of_people;
                $plane_trip_away->save();
        }
        else{
            return response()->json([
                'message'=>'the seats of this trip plane lower than number of person'
            ],400);
        }

        // to check if there are an enough rooms in this hotel
        $room_count=$request->number_of_people / $request->trip_capacity ;
        if($request->number_of_people % $request->trip_capacity > 0 ) $room_count++;
        $rooms=Room::available($request->start_date,$request->end_date)
                    ->where('hotel_id',$request->hotel_id)
                    ->where('capacity',$request->trip_capacity)
                    ->count();
        if( !$rooms || $rooms<$room_count)
        {
            return response()->json([
                'message'=>'there is not enough room in this hotel',
                'count'=>$rooms

            ],400);
        }
           try{
           $booking=Booking::create([
            'user_id'=>auth()->user()->id,
            'source_trip_id'=>$request->source_trip_id,
            'destination_trip_id'=>$request->destination_trip_id,
            'trip_name'=>$request->trip_name,
            'price'=>$request->price,
            'number_of_people'=>$request->number_of_people,
            'trip_capacity'=>$request->trip_capacity,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date,
            'trip_note'=>$request->trip_note,
            'type'=>'static',

        ]);
        foreach($request->places as $place)
        {
            $book_place=BookPlace::create([
                'book_id'=>$booking->id,
                'place_id'=>$place,
                'current_price'=>Place::where('id',$place)->first()->place_price,
            ]);
        }

        //go away
        $book_plane=BookPlane::create([
            'book_id'=>$booking->id,
            'plane_trip_id'=>$request->plane_trip,
        ]);

        //back away
        $book_plane_away=BookPlane::create([
            'book_id'=>$booking->id,
            'plane_trip_id'=>$request->plane_trip_away,
        ]);
        //rooms
        $rooms=Room::available($request->start_date,$request->end_date)
                    ->where('hotel_id',$request->hotel_id)
                    ->where('capacity',$request->trip_capacity)
                    ->get();
        for($i=0;$i<$room_count;$i++)
        {
            BookingRoom::create([
                'book_id'=>$booking->id,
                'room_id'=>$rooms[$i]['id'],
                'current_price'=>$rooms[$i]['price'],
                'start_date'=>$request->start_date,
                'end_date'=>$request->end_date
            ]);
        }
        }catch(Exception $exception){
            return response()->json([
                'message'=>$exception->getMessage(),
            ],400);

        }
        $static_book=Booking::with(['places:id,name,place_price,text','places.images:id,image',
                                    'plane_trips:id,airport_source_id,airport_destination_id,current_price,available_seats,flight_date,landing_date',
                                    'plane_trips.airport_source:id,name',
                                    'plane_trips.airport_destination:id,name',
                                ])->where('id',$booking->id)->get();
        return response()->json([
            'data'=>$static_book[0]['plane_trips'][0]
        ],200);
    }



    
    public function update_Admin(Request $request,$id)
    {
        try{
            $booking= Booking::findOrFail($id);
            if(auth()->id() != $booking->user_id)
            {
                return response()->json([
                    'message'=>'You do not have the permission'
                ],200);
            }
        }catch(\Exception $e){
            return response()->json([
                'message'=> 'Not found',
            ],404);
        }
        $date=Carbon::now()->format('Y-m-d');
        $validator = Validator::make($request->all(), [
            'source_trip_id'=>'required|exists:countries,id',
            'destination_trip_id'=>'required|exists:countries,id',
            'trip_name'=>'required|string',
            'price'=>'required|numeric',
            'number_of_people'=>'required|min:1|numeric',
            'start_date'=>"required|date|unique:bookings,start_date|after_or_equal:$date",
            'end_date'=>'required|date|after_or_equal:end_date',
            'trip_note'=>'required|string',
          ]);

          if($validator->fails()){
              return response()->json([
                  'message'=> $validator->errors()->first(),
              ],422);
          }

          $booking->source_trip_id = $request->source_trip_id;
          $booking->destination_trip_id = $request->destination_trip_id;
          $booking->trip_name = $request->trip_name;
          $booking->price = $request->price;
          $booking->number_of_people = $request->number_of_people;
          $booking->start_date = $request->start_date;
          $booking->end_date = $request->end_date;
          $booking->trip_note = $request->trip_note;
          $booking->save();
          return response()->json([
            'message'=> 'booking has been updated successfully',
            'data'=>booking::with('country:id,name','area:id,name','user:id,name,email,image,position')
                            ->select('id','name','user_id','area_id','country_id')
                            ->where('id',$booking->id)
                            ->get(),
          ],200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
    }
}
