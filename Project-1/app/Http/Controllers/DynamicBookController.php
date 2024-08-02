<?php

namespace App\Http\Controllers;

use App\Http\Requests\Trip\DynamicTripRequest;
use App\Http\Requests\Trip\HotelBookRequest;
use App\Http\Requests\Trip\PlaneBookRequest;
use App\Http\Requests\Trip\UpdateDynamicTripRequest;
use App\Http\Requests\Trip\UpdateHotelBookRequest;
use App\Http\Requests\Trip\UpdatePlaneBookRequest;
use App\Http\Requests\Trip\UpdateStaticTripRequest;
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
        $this->middleware('permission:unbanned', ['only' => ['store_User','hotel_book','plane_book',
        'update_dynamic_trip','updateHotelBook','updatePlaneBook',
        ]]);
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
            'activities'=>$request->activities??null,##
            'count_room_C2'=>$request->count_room_C2,
            'count_room_C4'=>$request->count_room_C4,
            'count_room_C6'=>$request->count_room_C6
        ];
        $Dynamic_book=$this->bookrepository->store_User($data);
        if($Dynamic_book === 1){
            return response()->json([
                'message' => 'the seats of the going trip plane lower than number of person'
            ], 400);
        }
        if($Dynamic_book === 2){
            return response()->json([
                'message' => 'the seats of the return trip plane lower than number of person'
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
        if($Dynamic_book === 55){
            return response()->json([
                'message' => 'your money dont enough for create your trip',
            ], 400);
        }
        return response()->json([
            'data'=>$Dynamic_book
        ],200);
    }

    public function hotel_book(HotelBookRequest $request){
        $data=[
            'source_trip_id'=>$request->source_trip_id,
            'destination_trip_id'=>$request->destination_trip_id,
            'hotel_id'=>$request->hotel_id,
            'trip_name'=>$request->trip_name,
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
                'message' => 'bad request' ,
            ], 400);
        }
        if($Dynamic_book === 55){
            return response()->json([
                'message' => 'your money dont enough for create your trip',
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
        if($Dynamic_book === 55){
            return response()->json([
                'message' => 'your money dont enough for create your trip',
            ], 400);
        }
        if($Dynamic_book === 27){
            return response()->json([
                'message' => 'The flight date of return plane trip must be after flight date of going plane trip',
            ], 400);
        }
        return response()->json([
            'data'=>$Dynamic_book
        ],200);
    }
    public function index(){
        try{
            $D_trips=$this->bookrepository->index();
        }catch(Exception $e){
            return response()->json([
                'message'=>$e->getMessage(),
            ],404);
        }
        return response()->json([
            'data'=>$D_trips
        ],200);

    }
    public function get_all_plane_trip(){
        try{
            $D_trips=$this->bookrepository->get_all_plane_trip();
        }catch(Exception $e){
            return response()->json([
                'message'=>$e->getMessage(),
            ],404);
        }
        return response()->json([
            'data'=>$D_trips
        ],200);
    }
    public function get_all_hotel_trip(){
        try{
            $D_trips=$this->bookrepository->get_all_hotel_trip();
        }catch(Exception $e){
            return response()->json([
                'message'=>$e->getMessage(),
            ],404);
        }
        return response()->json([
            'data'=>$D_trips
        ],200);
    }
    public function get_all_dynamic_trip(){
        try{
            $D_trips=$this->bookrepository->get_all_dynamic_trip();
        }catch(Exception $e){
            return response()->json([
                'message'=>$e->getMessage(),
            ],404);
        }
        return response()->json([
            'data'=>$D_trips
        ],200);
    }


    public function showDynamicTrip($id){


        $trip=$this->bookrepository->showDynamicTrip($id);
        if($trip===1)
        {
            return response()->json([
                'message'=>'Not Found'
            ],404);
        }
        return response()->json(['data'=>$trip],200);
    }
    public function showHotelTrip($id){
        $trip=$this->bookrepository->show_hotel_trip($id);
        if($trip===1)
        {
            return response()->json([
                'message'=>'Not Found'
            ],404);
        }
        return response()->json(['data'=>$trip],200);
    }
    public function showPlaneTrip($id){
        $trip=$this->bookrepository->show_plane_trip($id);
        if($trip===1)
        {
            return response()->json([
                'message'=>'Not Found'
            ],404);
        }
        return response()->json(['data'=>$trip],200);
    }
    public function update_dynamic_trip(UpdateDynamicTripRequest $request,$id){
        $data=[
            // 'source_trip_id'=>$request->source_trip_id,
            // 'destination_trip_id'=>$request->destination_trip_id,
            'hotel_id'=>$request->hotel_id??null,
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
            'activities'=>$request->activities??null,##
            'count_room_C2'=>$request->count_room_C2??null,
            'count_room_C4'=>$request->count_room_C4??null,
            'count_room_C6'=>$request->count_room_C6??null
        ];
        try{

            $trip=$this->bookrepository->update_dynamic_trip($data,$id);
            if( $trip==9){
                return response()->json([
                    'message'=>'the new end date must be after or equal old end date'
                ],404);
            }
            if( $trip==1){
                return response()->json([
                    'message'=>'the number of people is biggest of number of seats in going trip'
                ],404);
            }
            if( $trip==2){
                return response()->json([
                    'message'=>'the number of people is biggest of number of seats in return trip'
                ],404);
            }
            if($trip === 5){
                return response()->json([
                    'message'=>'the room count  of this capacity 2 not enough'
                ],400);
            }
            if($trip === 6){
                return response()->json([
                    'message'=>'the room count  of this capacity 4 not enough'
                ],400);
            }
            if($trip === 7){
                return response()->json([
                    'message'=>'the room count  of this capacity 6 not enough'
                ],400);
            }
            if($trip===8){
                return response()->json([
                    'message'=>'bad request'
                ],400);
            }
            if($trip === 55){
                return response()->json([
                    'message' => 'your money dont enough for update your trip',
                ], 400);
            }
            if($trip === 66){
                return response()->json([
                    'message' => 'sorry but your trip have been started',
                ], 400);
            }
            return response()->json($trip,200);
        }catch(Exception $exception){
            return response()->json([
                'message'=>$exception->getMessage()
            ],422);
        }
    }

    public function updateHotelBook(UpdateHotelBookRequest $request,$id){
        $data=[
            'trip_name'=>$request->trip_name,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date,
            'trip_note'=>$request->trip_note,
            'count_room_C2'=>$request->count_room_C2??null,
            'count_room_C4'=>$request->count_room_C4??null,
            'count_room_C6'=>$request->count_room_C6??null
        ];
        try{

            $trip=$this->bookrepository->updateHotelBook($data,$id);
            if( $trip==9){
                return response()->json([
                    'message'=>'the new end date must be after or equal old end date'
                ],400);
            }
            if( $trip==1){
                return response()->json([
                    'message'=>'the number of people is biggest of number of seats in going trip'
                ],404);
            }
            if( $trip==2){
                return response()->json([
                    'message'=>'the number of people is biggest of number of seats in return trip'
                ],404);
            }
            if($trip === 5){
                return response()->json([
                    'message'=>'the room count  of this capacity 2 not enough'
                ],400);
            }
            if($trip === 6){
                return response()->json([
                    'message'=>'the room count  of this capacity 4 not enough'
                ],400);
            }
            if($trip === 7){
                return response()->json([
                    'message'=>'the room count  of this capacity 6 not enough'
                ],400);
            }
            if($trip===8){
                return response()->json([
                    'message'=>'bad request'
                ],400);
            }
            if($trip === 55){
                return response()->json([
                    'message' => 'your money dont enough for update your trip',
                ], 400);
            }
            if($trip === 66){
                return response()->json([
                    'message' => 'sorry but your trip have been started',
                ], 400);
            }
            return response()->json($trip);
        }catch(Exception $exception){
            return response()->json([
                'message'=>$exception->getMessage()
            ],422);
        }
    }

    public function check(UpdateDynamicTripRequest $request,$id){
        $data=[
            // 'source_trip_id'=>$request->source_trip_id,
            // 'destination_trip_id'=>$request->destination_trip_id,
            'hotel_id'=>$request->hotel_id??null,
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
            'count_room_C2'=>$request->count_room_C2??null,
            'count_room_C4'=>$request->count_room_C4??null,
            'count_room_C6'=>$request->count_room_C6??null
        ];
        $booking =Booking::findOrFail($id);
        $trip=$this->bookrepository->checkPlaneTrip($data,$booking->id);
        if($trip==2){
            return response()->json([
                'message'=>'wrong leader'
            ],400);
        }
        if($trip==8){
            return response()->json([
                'message'=>'bad request'
            ],404);
        }
        $this->bookrepository->bookPlaneTrip($data,$booking->id);
        return response()->json([
            'message'=>'Done!'
        ]);
    }

    public function updatePlaneBook(UpdatePlaneBookRequest $request,$id){
        $data=[
            'trip_name'=>$request->trip_name,
            'number_of_people'=>$request->number_of_people,
            'trip_note'=>$request->trip_note,
        ];
        $booking =Booking::findOrFail($id);
        $trip=$this->bookrepository->updatePlaneBook($data,$booking->id);
        if( $trip==1){
            return response()->json([
                'message'=>'the number of people is biggest of number of seats in going trip'
            ],404);
        }
        if( $trip==2){
            return response()->json([
                'message'=>'the number of people is biggest of number of seats in return trip'
            ],404);
        }
        if($trip==8){
            return response()->json([
                'message'=>'bad request'
            ],404);
        }
        if($trip === 55){
            return response()->json([
                'message' => 'your money dont enough for update your trip',
            ], 400);
        }
        if($trip === 66){
            return response()->json([
                'message' => 'sorry but your trip have been started',
            ], 400);
        }
        return response()->json($trip);
    }

    public function delete_dynamic_trip($id){
        $trip=$this->bookrepository->delete_dynamic_trip($id);
        if($trip==8){
            return response()->json([
                'message'=>'bad request'
            ],404);
        }
        if($trip === 20){
            return response()->json([
               'message'=>'sorry but your trip have been started',
            ], 400);
        }

        return response()->json($trip);
    }
    public function get_all_dynamic_book(Request $request){
        try{
            $D_trips=$this->bookrepository->get_all_dynamic_book($request);
        }catch(Exception $e){
            return response()->json([
                'message'=>$e->getMessage(),
            ],404);
        }
        return response()->json([
            'data'=>$D_trips
        ],200);
    }
    public function get_all_hotel_book(){
        try{
            $D_trips=$this->bookrepository->get_all_hotel_book();
        }catch(Exception $e){
            return response()->json([
                'message'=>$e->getMessage(),
            ],404);
        }
        return response()->json([
            'data'=>$D_trips
        ],200);
    }
    public function get_all_plane_book(){
        try{
            $D_trips=$this->bookrepository->get_all_plane_book();
        }catch(Exception $e){
            return response()->json([
                'message'=>$e->getMessage(),
            ],404);
        }
        return response()->json([
            'data'=>$D_trips
        ],200);
    }
}
