<?php

namespace App\Http\Controllers;

use App\Http\Requests\Trip\DynamicTripRequest;
use App\Http\Requests\Trip\HotelBookRequest;
use App\Http\Requests\Trip\PlaneBookRequest;
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
use App\Repositories\DynamicBookRepository; 

class DynamicBookController extends Controller
{
 
    private $bookrepository;

    public function __construct(DynamicBookRepository $bookrepository)
    {
        $this->bookrepository = $bookrepository;
    }
    public function store_User(DynamicTripRequest $request){
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
            'place_ids'=>$request->place_ids,
            'plane_trip_id'=>$request->plane_trip_id,
            'plane_trip_away_id'=>$request->plane_trip_away_id,
            'count_room_C2'=>$request->count_room_C2,
            'count_room_C4'=>$request->count_room_C4,
            'count_room_C6'=>$request->count_room_C6
        ];
        $Dynamic_book=$this->bookrepository->store_User($data);
        if($Dynamic_book === 1){
            return response()->json([
                'message'=>'there is not enough room in this hotel',
            ],400);
        }
        if($Dynamic_book === 2){
            return response()->json([
                'message' => 'the seats of the going trip plane lower than number of person'
            ], 400);
        }
        if($Dynamic_book === 3){
            return response()->json([
                'message' => 'the seats of the return trip plane lower than number of person'
            ], 400);
        }
        if($Dynamic_book === 4){
            return response()->json([
                'message' => 'Failed to create a trip',
            ], 400);
        }
        if($Dynamic_book === 5){
            return response()->json([
                'message'=>'the room count  of this capacity 2 not enough'
            ],400);
        }
        if($Dynamic_book === 6){
            return response()->json([
                'message'=>'the room count  of this capacity 4 not enough'
            ],400);
        }
        if($Dynamic_book === 7){
            return response()->json([
                'message'=>'the room count  of this capacity 6 not enough'
            ],400);
        }
        if($Dynamic_book === 8){
            return response()->json([
                'message' => 'bad request',
            ], 400);
        }
        return response()->json([
            'data'=>$Dynamic_book
        ],200);
    }

    public function hotel_book(HotelBookRequest $request){
        $data=[
            // 'source_trip_id'=>$request->source_trip_id,
            'destination_trip_id'=>$request->destination_trip_id,
            'hotel_id'=>$request->hotel_id,
            'trip_name'=>$request->trip_name,
            //'price'=>$request->price,
            'number_of_people'=>$request->number_of_people,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date,
            'trip_note'=>$request->trip_note,
            'count_room_C2'=>$request->count_room_C2,
            'count_room_C4'=>$request->count_room_C4,
            'count_room_C6'=>$request->count_room_C6
        ];
        $Dynamic_book=$this->bookrepository->hotel_book($data);
        if($Dynamic_book === 1){
            return response()->json([
                'message'=>'there is not enough room in this hotel',
            ],400);
        }
        if($Dynamic_book === 2){
            return response()->json([
                'message' => 'the seats of the going trip plane lower than number of person'
            ], 400);
        }
        if($Dynamic_book === 3){
            return response()->json([
                'message' => 'the seats of the return trip plane lower than number of person'
            ], 400);
        }
        if($Dynamic_book === 4){
            return response()->json([
                'message' => 'Failed to create a trip',
            ], 400);
        }
        if($Dynamic_book === 5){
            return response()->json([
                'message'=>'the room count  of this capacity 2 not enough'
            ],400);
        }
        if($Dynamic_book === 6){
            return response()->json([
                'message'=>'the room count  of this capacity 4 not enough'
            ],400);
        }
        if($Dynamic_book === 7){
            return response()->json([
                'message'=>'the room count  of this capacity 6 not enough'
            ],400);
        }
        if($Dynamic_book === 8){
            return response()->json([
                'message' => 'bad request',
            ], 400);
        }
        return response()->json([
            'data'=>$Dynamic_book
        ],200);
    }

    public function plane_book(PlaneBookRequest $request){
        $data=[
            'source_trip_id'=>$request->source_trip_id,
            'destination_trip_id'=>$request->destination_trip_id,
            'trip_name'=>$request->trip_name,
            'number_of_people'=>$request->number_of_people,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date,
            'trip_note'=>$request->trip_note,
            'plane_trip_id'=>$request->plane_trip_id,
            'plane_trip_away_id'=>$request->plane_trip_away_id,
        ];
        $Dynamic_book=$this->bookrepository->plane_book($data);
        if($Dynamic_book === 1){
            return response()->json([
                'message'=>'there is not enough room in this hotel',
            ],400);
        }
        if($Dynamic_book === 2){
            return response()->json([
                'message' => 'the seats of the going trip plane lower than number of person'
            ], 400);
        }
        if($Dynamic_book === 3){
            return response()->json([
                'message' => 'the seats of the return trip plane lower than number of person'
            ], 400);
        }
        if($Dynamic_book === 4){
            return response()->json([
                'message' => 'Failed to create a trip',
            ], 400);
        }
        if($Dynamic_book === 5){
            return response()->json([
                'message'=>'the room count  of this capacity 2 not enough'
            ],400);
        }
        if($Dynamic_book === 6){
            return response()->json([
                'message'=>'the room count  of this capacity 4 not enough'
            ],400);
        }
        if($Dynamic_book === 7){
            return response()->json([
                'message'=>'the room count  of this capacity 6 not enough'
            ],400);
        }
        if($Dynamic_book === 8){
            return response()->json([
                'message' => 'bad request',
            ], 400);
        }
        return response()->json([
            'data'=>$Dynamic_book
        ],200);
    }

}
