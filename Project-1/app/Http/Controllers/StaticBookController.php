<?php

namespace App\Http\Controllers;

use App\Http\Requests\Trip\DynamicTripRequest;
use App\Http\Requests\Trip\StoreStaticTripRequest;
use App\Http\Requests\Trip\UpdateStaticTripRequest;
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
            $static_trips=$this->bookrepository->index();
            return response()->json([
                'data'=>$static_trips
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
            'ratio'=>$request->ratio,
            'number_of_people'=>$request->number_of_people,
            'trip_capacity'=>$request->trip_capacity,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date,
            'trip_note'=>$request->trip_note,
            'places'=>$request->places,
            'activities'=>$request->activities,##
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
    public function update_Admin(UpdateStaticTripRequest $request,$id)
    {
        $data=[
            'hotel_id'=>$request->hotel_id,
            'trip_name'=>$request->trip_name,
            'price'=>$request->price,
            'number_of_people'=>$request->add_new_people,
            'trip_capacity'=>$request->trip_capacity,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date,
            'trip_note'=>$request->trip_note,
            'places'=>$request->places,
            'plane_trip'=>$request->plane_trip,
            'plane_trip_away'=>$request->plane_trip_away,
        ];
        $booking= Booking::findOrFail($id);
        if(auth()->id() != $booking->user_id)
        {
            return response()->json([
                'message'=>'You do not have the permission',
            ],200);
        }
        $edit=$this->bookrepository->editAdmin($data,$id);
        if($edit === 1){
            return response()->json([
                'message'=>'there is not enough room in this hotel',
            ],400);
        }
        if($edit === 2){
            return response()->json([
                'message' => 'the seats of the going trip plane lower than number of person'
            ], 400);
        }
        if($edit === 3){
            return response()->json([
                'message' => 'the seats of the return trip plane lower than number of person'
            ], 400);
        }
        if($edit === 4)
        {
            return response()->json([
                'message'=>'updated failed'
            ],404);
        }
        if ($edit === 5) 
        {
            return response()->json([
                'message' => 'You should choose a period similar to the ancient period'
            ], 400);
        }
        return response()->json([
            'message'=> 'booking has been updated successfully',
            'data'=>$edit,
          ],200);
    }

    public function showStaticTrip($id)
    {
        $trip=$this->bookrepository->showStaticTrip($id);
        if($trip===1)
        {
            return response()->json([
                'message'=>'Not Found'
            ],404);
        }

        // $book=Booking::where('type','static')
        //                 ->AvailableRooms()
        //                 ->findOrFail($id);

        // $bookData=[
        //     'id'=>$book['id'],
        //     'source_trip_id'=>$book['source_trip_id'],
        //     'destination_trip_id'=>$book['destination_trip_id'],
        //     'trip_name'=>$book['trip_name'],
        //     'price'=>$book['price'],
        //     'number_of_people'=>$book['number_of_people'],
        //     'trip_capacity'=>$book['trip_capacity'],
        //     'start_date'=>$book['start_date'],
        //     'end_date'=>$book['end_date'],
        //     'stars'=>$book['stars'],
        //     'trip_note'=>$book['trip_note'],
        //     'type'=>$book['type'],
        //     'rooms_count'=>$book['rooms_count'],
        // ];

        // $going_trip=[
        //     'airport_source'=>[
        //         'id'=>$book->plane_trips[0]->airport_source->id?? null,
        //         'name'=>$book->plane_trips[0]->airport_source->name?? null,
        //     ]??null,
        //     'airport_destination'=>[
        //         'id'=>$book->plane_trips[0]->airport_destination->id?? null,
        //         'name'=>$book->plane_trips[0]->airport_destination->name?? null,
        //     ]??null,
        // ];
        // $return_trip=[
        //     'airport_source'=>[
        //         'id'=>$book->plane_trips[1]->airport_source->id?? null,
        //         'name'=>$book->plane_trips[1]->airport_source->name?? null,
        //     ]??null,
        //     'airport_destination'=>[
        //         'id'=>$book->plane_trips[1]->airport_destination->id?? null,
        //         'name'=>$book->plane_trips[1]->airport_destination->name?? null,
        //     ]??null,
        // ];

        return response()->json($trip,200);

    }

}
