<?php

namespace App\Http\Controllers;

use App\Http\Requests\Trip\DynamicTripRequest;
use App\Models\Area;
use App\Models\Booking;
use App\Models\BookPlace;
use App\Models\BookPlane;
use App\Models\Hotel;
use App\Models\Place;
use App\Models\Plane;
use App\Models\PlaneTrip;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Database\Eloquent\Builder;

use function Laravel\Prompts\select;

class BookingController extends Controller
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_Admin(Request $request)
    {
        $date=Carbon::now()->format('Y-m-d');
        $validatedData =Validator::make($request->all(),[
            'source_trip_id'=>'required|exists:countries,id',
            'destination_trip_id'=>'required|exists:countries,id',
            'trip_name'=>'required|string',
            'price'=>'required|numeric',
            'number_of_people'=>'required|min:3|numeric',
            'start_date'=>"required|date|after_or_equal:$date",
            'end_date'=>'required|date|after_or_equal:end_date',
            'trip_note'=>'string',
            'places'=>'array|min:2',
            'places.*'=>"required|exists:places,id",
            'plane_trips'=>'array',
            'plane_trips.*'=>"required|exists:plane_trips,id",
            'plane_trips_away'=>'array',
            'plane_trips_away.*'=>'required|exists:plane_trips,id',
            ''
       ]);
           if( $validatedData->fails() ){
               return response()->json([
                   'message'=> $validatedData->errors()->first(),
               ],422);
           }
           foreach($request->places as $place)
           {
            if(Area::where('id',Place::where('id',$place)->first()->area_id)->first()->country_id != $request->destination_trip_id){
                return response()->json([
                    'message'=>'the place '.Place::where('id',$place)->first()->name .' is not exists in this country'
                ]);
            }

            }

            foreach($request->plane_trips as $plane_trip)
            {
                if(PlaneTrip::where('id',$plane_trip)->first()->country_source_id != $request->source_trip_id ||
                    PlaneTrip::where('id',$plane_trip)->first()->country_destination_id != $request->destination_trip_id )
                {
                    return response()->json([
                        'message'=>'the plane '.Plane::where('id',PlaneTrip::where('id',$plane_trip)->first()->plane_id)->first()->name .' will not go up or go down in this country'
                    ]);
                }

            }

            foreach($request->plane_trips_away as $plane_trip)
            {
                if(PlaneTrip::where('id',$plane_trip)->first()->country_source_id != $request->destination_trip_id ||
                 PlaneTrip::where('id',$plane_trip)->first()->country_destination_id != $request->source_trip_id )
                {
                    return response()->json([
                        'message'=>'the plane '.Plane::where('id',PlaneTrip::where('id',$plane_trip)->first()->plane_id)->first()->name .' will not go up or go down in this country'
                    ]);
                }

            }
            // foreach($request->hotel_id as $hotel_id)
            // {
            //     $area_id=Hotel::where('id',$hotel_id)->area_id;
            //     if(Area::where('id', $area_id)->first()->country_id != $request->destination_trip_id)
            //     {
            //         return response()->json([
            //             'message'=>'the plane '.Hotel::where('id',$hotel_id)->first()->name .' did not have section in this country'
            //         ]);
            //     }

            // }
           try{
           $booking=Booking::create([
            'user_id'=>auth()->user()->id,
            'source_trip_id'=>$request->source_trip_id,
            'destination_trip_id'=>$request->destination_trip_id,
            'trip_name'=>$request->trip_name,
            'price'=>$request->price,
            'number_of_people'=>$request->number_of_people,
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
        foreach($request->plane_trips as $plane_trip)
        {
            $book_plane=BookPlane::create([
                'book_id'=>$booking->id,
                'plane_trip_id'=>$plane_trip,
            ]);
            $seats=PlaneTrip::where('id',$plane_trip)->first();
            if( $seats['available_seats']>=$request->number_of_people){
            $seats['available_seats']-=$request->number_of_people;
            }
            else{
                return response()->json([
                    'message'=>'the seats of this trip plane lower than number of person'
                ],400);
            }
        }
        foreach($request->plane_trips_away as $plane_trip_away)
        {
            $book_plane_away=BookPlane::create([
                'book_id'=>$booking->id,
                'plane_trip_id'=>$plane_trip_away,
            ]);
            $seats=PlaneTrip::where('id',$plane_trip)->first();
            if( $seats['available_seats']>=$request->number_of_people){
            $seats['available_seats']-=$request->number_of_people;
            }
            else{
                return response()->json([
                    'message'=>'the seats of this trip plane lower than number of person'
                ],400);
            }
        }
        // foreach($request->hotel_id as $hotel_id)
        // {
        //     $hotel_id=::create([
        //         'book_id'=>$booking->id,
        //         'plane_trip_id'=>$plane_trip_away,
        //     ]);
        // }


        }catch(Exception $exception){
            return response()->json([
                'message'=>$exception->getMessage(),
            ]);

        }
        $data=[
            Booking::with(['places:id,name,place_price,text','places.images:id,image',
            'plane_trips:id,airport_source_id,airport_destination_id,current_price,available_seats,flight_date,landing_date',
            'plane_trips.airport_source:id,name',
            'plane_trips.airport_destination:id,name',
            ])->where('id',$booking->id)->get(),
            // 'go plane'=>$book_plane,
            // 'return plane'=>$book_plane_away,
        ];
        return response()->json([
            'data'=>Booking::with(['places:id,name,place_price,text','places.images:id,image',
                                    'plane_trips:id,airport_source_id,airport_destination_id,current_price,available_seats,flight_date,landing_date',
                                    'plane_trips.airport_source:id,name',
                                    'plane_trips.airport_destination:id,name',
                                 ])->where('id',$booking->id)->get(),
        ],200);
    }



    public function store_User(DynamicTripRequest $request)
    {
        $date=Carbon::now()->format('Y-m-d');
        $validatedData =Validator::make($request->all(),[
            'source_trip_id'=>'required|exists:countries,id',
            'destination_trip_id'=>'required|exists:countries,id',
            'start_date'=>"required|date|after_or_equal:$date",
            'end_date'=>'required|date|after_or_equal:end_date',
            'trip_name'=>'required|string',
            'number_of_people'=>'required|min:1|numeric',
            'trip_note'=>'required|string',

            'place_id'=>'required|exists:places,id',
            'plane_trip_id'=>'exists:plane_trip,id',
            'hotel-id'=>'exists:hotels,id',

       ]);

           if( $validatedData->fails() ){
               return response()->json([
                   'message'=> $validatedData->errors()->first(),
               ],422);
           }
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
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try{
            $booking=Booking::findOrFail($id);
            return response()->json([
                'data'=>$booking
            ],200);

        }catch(Exception $e){
            return response()->json([
                'message'=>'Not Found'
            ],404);

        }
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
