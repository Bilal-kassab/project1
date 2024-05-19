<?php

namespace App\Http\Controllers;

use App\Http\Requests\Trip\DynamicTripRequest;
use App\Http\Requests\Trip\StoreStaticTripRequest;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\BookPlace;
use App\Models\BookPlane;
use App\Models\Place;
use App\Models\PlaneTrip;
use App\Models\Room;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class StaticBookController extends Controller
{


    private $bookrepository;

    public function __construct(BookRepositoryInterface $bookrepository)
    {
        $this->bookrepository = $bookrepository;
    }
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

    public function store_Admin(StoreStaticTripRequest $request)
    {
        $data=[
            'source_trip_id'=>$request->source_trip_id,
            'destination_trip_id'=>$request->destination_trip_id,
            'hotel_id'=>$request->hotel_id,
            'trip_name'=>$request->trip_name,
            'price'=>$request->price,
            'number_of_people'=>$request->number_of_people,
            'trip_capacity'=>$request->trip_capacity,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date,
            'trip_note'=>$request->trip_note,
            'places'=>$request->places,
            'plane_trip'=>$request->plane_trip,
            'plane_trip_away'=>$request->plane_trip_away,
        ];

        $static_book=$this->bookrepository->store_Admin($data);

        if($static_book === 1){
            return response()->json([
                'message'=>'there is not enough room in this hotel',
            ],400);
        }
        if($static_book === 2){
            return response()->json([
                'message' => 'the seats of the going trip plane lower than number of person'
            ], 400);
        }
        if($static_book === 3){
            return response()->json([
                'message' => 'the seats of the return trip plane lower than number of person'
            ], 400);
        }
        if($static_book === 4){
            return response()->json([
                'message' => 'Failed to create a trip',
            ], 400);
        }
        return response()->json([
            'data'=>$static_book
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
