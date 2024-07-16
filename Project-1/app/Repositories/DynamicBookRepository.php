<?php

namespace App\Repositories;

use App\Models\ActivityBook;
use App\Models\Bank;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\BookPlace;
use App\Models\BookPlane;
use App\Models\Hotel;
use App\Models\Place;
use App\Models\PlaneTrip;
use App\Models\Room;
use App\Models\User;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\DynamicBookRepositoryInterface;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Auth;
use Throwable;

use function PHPUnit\Framework\throwException;

class DynamicBookRepository implements DynamicBookRepositoryInterface
{
    public function hotel_book($request){
        try {
            $trip_price=0;
        if($request['hotel_id'] != null)
        {
            if($request['count_room_C2']==0 && $request['count_room_C4']==0 &&$request['count_room_C6']==0){
                return 8;
            }
                $data=[
                    'start_date'=>$request['start_date'],
                    'end_date'=>$request['end_date'],
                    'hotel_id'=>$request['hotel_id'],
                    'count_room_C2'=>$request['count_room_C2'],
                    'count_room_C4'=>$request['count_room_C4'],
                    'count_room_C6'=>$request['count_room_C6'],
                ];
                $check=$this->checkHotel($data,null);
                if($check==5 || $check==6 ||$check==7 ||$check==8){ return $check;}
                $trip_price+=$check;
                if($trip_price>Bank::where('email',auth()->user()['email'])->first()->money){
                    return 55;
                }
                $booking = Booking::create([
                    'user_id' => auth()->user()->id,
                    'source_trip_id' =>$request['source_trip_id'],
                    'destination_trip_id' => $request['destination_trip_id'],
                    'trip_name' => $request['trip_name'],
                    'start_date' => $request['start_date'],
                    'end_date' => $request['end_date'],
                    'trip_note' => $request['trip_note'],
                    'type' => 'hotel',
                    ]);
                    $data=[
                        'start_date'=>$request['start_date'],
                        'end_date'=>$request['end_date'],
                        'hotel_id'=>$request['hotel_id'],
                        'count_room_C2'=>$request['count_room_C2'],
                        'count_room_C4'=>$request['count_room_C4'],
                        'count_room_C6'=>$request['count_room_C6']
                        ];
                        $this->bookHotel($data,$booking->id);
                        $booking->price=$trip_price;
                        $booking->save();
                        $my_account=Bank::where('email',auth()->user()['email'])->first();
                        $my_account['money']-=$booking->price;
                        $my_account['payments']+=$booking->price;
                        $my_account->save();
                        $dynamic_trip=$this->show_hotel_trip($booking['id']);
                        return $dynamic_trip;
        }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }
    public function plane_book($request){
        try {
            $trip_price=0;
            $plane_trip= PlaneTrip::where('id', $request['plane_trip_id'])->first();
            $plane_trip_away = PlaneTrip::where('id', $request['plane_trip_away_id'])->first()??null;
            if($plane_trip_away && $plane_trip['flight_date']>=$plane_trip_away['flight_date']){
                return 27;
            }
            $data=[
                'plane_trip_id'=>$request['plane_trip_id']??null,
                'number_of_people'=>$request['number_of_people'],
            ];
            $check =$this->checkPlaneTrip($data,null);
            if($check==1 ||$check==8){
                return $check;
            }
            $trip_price+=$check;
            $data=[
                'plane_trip_id'=>$request['plane_trip_away_id']??null,
                'number_of_people'=>$request['number_of_people'],
            ];
            $check =$this->checkPlaneTripaway($data,null);
            if($check==2 ||$check==8){
                return $check;
            }
            $trip_price+=$check;
            if($trip_price>Bank::where('email',auth()->user()['email'])->first()->money){
                return 55;
            }
            $booking = Booking::create([
                'user_id' => auth()->user()->id,
                'source_trip_id' => $plane_trip['country_source_id'],
                'destination_trip_id' =>$plane_trip['country_destination_id'],
                'trip_name' => $request['trip_name'],
                'number_of_people' => $request['number_of_people'],
                'start_date' => $plane_trip['flight_date'],
                'end_date' => $plane_trip_away['flight_date']??$plane_trip['landing_date'],
                'trip_note' => $request['trip_note'],
                'type' => 'plane',
            ]);
            $data=[
                'plane_trip_id'=>$request['plane_trip_id']??null,
                'number_of_people'=>$request['number_of_people'],
            ];
            $this->bookPlaneTrip($data,$booking->id);
            $data=[
                'plane_trip_id'=>$request['plane_trip_away_id']??null,
                'number_of_people'=>$request['number_of_people'],
            ];
            $this->bookPlaneTrip($data,$booking->id);

            $booking->price=$trip_price;
            $booking->save();
            $my_account=Bank::where('email',auth()->user()['email'])->first();
            $my_account['money']-=$booking->price;
            $my_account['payments']+=$booking->price;
            $my_account->save();
            $dynamic_trip=$this->show_plane_trip($booking['id']);
            return $dynamic_trip;

        }catch(Exception $exception){
            throw new Exception($exception->getMessage());
        }
    }
    public function store_User($request){
        try {
        $trip_price=0;
        $data=[
            'start_date'=>$request['start_date'],
            'end_date'=>$request['end_date'],
            'hotel_id'=>$request['hotel_id'],
            'count_room_C2'=>$request['count_room_C2'],
            'count_room_C4'=>$request['count_room_C4'],
            'count_room_C6'=>$request['count_room_C6'],
        ];
        $check=$this->checkHotel($data,null);
        if($check==5 || $check==6 ||$check==7 ||$check==8){ return $check;}
        $trip_price+=$check;

        $data=[
            'plane_trip_id'=>$request['plane_trip_id']??null,
            'number_of_people'=>$request['number_of_people'],
        ];
        $check =$this->checkPlaneTrip($data,null);
        if($check==1 ||$check==8){
            return $check;
        }
        $trip_price+=$check;
        $data=[
            'plane_trip_id'=>$request['plane_trip_away_id'],
            'number_of_people'=>$request['number_of_people'],
        ];
        $check =$this->checkPlaneTripaway($data,null);
        if($check==2 ||$check==8){
            return $check;
        }
        $trip_price+=$check;

        if($request['place_ids'] !=null)
        {
            foreach($request['place_ids'] as $place) {
                $trip_price+=Place::where('id', $place)->first()->place_price;
            }
        }
        if($trip_price>Bank::where('email',auth()->user()['email'])->first()->money){
            return 55;
        }
        $booking = Booking::create([
            'user_id' => auth()->user()->id,
            'source_trip_id' => $request['source_trip_id'],
            'destination_trip_id' => $request['destination_trip_id'],
            'trip_name' => $request['trip_name'],
            'number_of_people' => $request['number_of_people'],
            'trip_capacity' => $request['trip_capacity'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'trip_note' => $request['trip_note'],
            'type' => 'dynamic',
        ]);
        $data=[
            'plane_trip_id'=>$request['plane_trip_id'],
            'number_of_people'=>$request['number_of_people'],
        ];
        $this->bookPlaneTrip($data,$booking->id);
        $data=[
            'plane_trip_id'=>$request['plane_trip_away_id'],
            'number_of_people'=>$request['number_of_people'],
        ];
        $this->bookPlaneTrip($data,$booking->id);
        if($request['place_ids'] !=null)
        {
            foreach($request['place_ids'] as $place) {
                $book_place = BookPlace::create([
                    'book_id' => $booking->id,
                    'place_id' => $place,
                    'current_price' => Place::where('id', $place)->first()->place_price,
                ]);
            }
        }
        $data=[
            'start_date'=>$request['start_date'],
            'end_date'=>$request['end_date'],
            'hotel_id'=>$request['hotel_id'],
            'count_room_C2'=>$request['count_room_C2'],
            'count_room_C4'=>$request['count_room_C4'],
            'count_room_C6'=>$request['count_room_C6']
        ];
        $this->bookHotel($data,$booking->id);
        foreach ($request['activities'] as $activity) {
            ActivityBook::create([
                 'booking_id' => $booking->id,
                 'activity_id' => $activity,
             ]);
         }
        $booking->price=$trip_price;
        $booking->save();
        $my_account=Bank::where('email',auth()->user()['email'])->first();
        $my_account['money']-=$booking->price;
        $my_account->save();
        $dynamic_trip=$this->showDynamicTrip($booking['id']);
        return $dynamic_trip;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

    }
    public function showDynamicTrip($id)
    {
        try{
        $book=Booking::where('type','dynamic')
                    // ->where('user_id',auth()->id())
                    ->AvailableRooms()
                    ->findOrFail($id);

                    // $place_cost=0;
                    // $plane_cost=0;
                    // if($book->plane_trips){
                    // $plane_cost=$book['number_of_people']*($book->plane_trips[0]['current_price']??null+$book->plane_trips[1]['current_price']??null)??null;
                    // }
                    // foreach($book->places as $place){
                    //      $place_cost+=$place['place_price'];
                    // }
                    // $price=[
                    //     'plane_cost'=>$plane_cost??null,
                    //     'places_cost'=>$place_cost??null,
                    //     'hotel_cost'=>$book['price']-$plane_cost??null-$place_cost??null,
                    // ];

        $bookData=[
            'id'=>$book['id'],
            'source_trip_id'=>$book['source_trip_id'],
            'destination_trip_id'=>$book['destination_trip_id'],
            'trip_name'=>$book['trip_name'],
            'price'=>$book['price'],
            'number_of_people'=>$book['number_of_people'],
            // 'trip_capacity'=>$book['trip_capacity'],
            'start_date'=>$book['start_date'],
            'end_date'=>$book['end_date'],
            // 'stars'=>$book['stars'],
            'trip_note'=>$book['trip_note'],
            // 'type'=>$book['type'],
            'rooms_count'=>$book['rooms_count'],
           // 'total_price'=>$price,
        ];
        $activities=$book?->activities;
        $going_trip=[];
        if($book->plane_trips[0]?->airport_source->id?? null){
            $going_trip=[
                'airport_source'=>[
                    'id'=>$book->plane_trips[0]->airport_source->id?? null,
                    'name'=>$book->plane_trips[0]->airport_source->name?? null,
                ],
                'airport_destination'=>[
                    'id'=>$book->plane_trips[0]->airport_destination->id?? null,
                    'name'=>$book->plane_trips[0]->airport_destination->name?? null,
                ],
            ];
        }
        $return_trip=[];
        if($book->plane_trips[1]?->airport_source->id?? null){
            $return_trip=[
                'airport_source'=>[
                    'id'=>$book->plane_trips[1]->airport_source->id?? null,
                    'name'=>$book->plane_trips[1]->airport_source->name?? null,
                ]??null,
                'airport_destination'=>[
                    'id'=>$book->plane_trips[1]->airport_destination->id?? null,
                    'name'=>$book->plane_trips[1]->airport_destination->name?? null,
                ]??null,
            ];
        }

        $hotel=[
            'id'=>$book->rooms?->first()['hotel']['id']??null,
            'name'=>$book->rooms?->first()['hotel']['name']?? null ,
        ];
        if(is_null($hotel['id'])){
            $hotel=[];
        }
        $dynamic_trip=[
            'dynamic_trip'=>$bookData,
             'activities'=>$activities,
            'source_trip'=>$book->source_trip,
            'destination_trip'=>$book->destination_trip,
            'places'=>$book->places,
            'going_trip'=>$going_trip,
            'return_trip'=>$return_trip,
            // 'activities'=>$activities,
            'hotel'=>$hotel,
            'rooms'=>$book->rooms->select('id','capacity','price'),
        ];
    }catch(Exception $e)
    {
        throw new Exception($e->getMessage());
        //return 1;
    }

    return $dynamic_trip;
    }
    public function show_hotel_trip($id){
        try{
            $book=Booking::where('type','hotel')
                        // ->where('user_id',auth()->id())
                        ->AvailableRooms()
                        ->findOrFail($id);

            $bookData=[
                'id'=>$book['id'],
                'source_trip_id'=>$book['source_trip_id'],
                'destination_trip_id'=>$book['destination_trip_id'],
                'trip_name'=>$book['trip_name'],
                'price'=>$book['price'],
                'start_date'=>$book['start_date'],
                'end_date'=>$book['end_date'],
                // 'stars'=>$book['stars'],
                'trip_note'=>$book['trip_note'],
                // 'type'=>$book['type'],
                'rooms_count'=>$book['rooms_count'],
            ];
            $hotel=[
                'id'=>$book->rooms?->first()['hotel']['id']??null,
                'name'=>$book->rooms?->first()['hotel']['name']?? null ,
            ];
            if(is_null($hotel['id'])){
                $hotel=[];
            }
            $dynamic_trip=[
                'dynamic_trip'=>$bookData,
                'source_trip'=>$book->source_trip,
                'destination_trip'=>$book->destination_trip,
                'hotel'=>$hotel,
                'rooms'=>$book->rooms->select('id','capacity','price'),

            ];
            return $dynamic_trip;
        }catch(Exception $exception)
        {
            throw new Exception($exception->getMessage());
        }
    }
    public function show_plane_trip($id){
        try{
            $book=Booking::where('type','plane')
                        // ->where('user_id',auth()->id())
                        ->AvailableRooms()
                        ->findOrFail($id);
            $bookData=[
                'id'=>$book['id'],
                'source_trip_id'=>$book['source_trip_id'],
                'destination_trip_id'=>$book['destination_trip_id'],
                'trip_name'=>$book['trip_name'],
                'price'=>$book['price'],
                'number_of_people'=>$book['number_of_people'],
                'start_date'=>$book['start_date'],
                'end_date'=>$book['end_date'],
                'trip_note'=>$book['trip_note'],
            ];
            $going_trip=[];
            if($book->plane_trips[0]?->airport_source->id?? null){
                $going_trip=[
                    'airport_source'=>[
                        'id'=>$book->plane_trips[0]->airport_source->id?? null,
                        'name'=>$book->plane_trips[0]->airport_source->name?? null,
                    ],
                    'airport_destination'=>[
                        'id'=>$book->plane_trips[0]->airport_destination->id?? null,
                        'name'=>$book->plane_trips[0]->airport_destination->name?? null,
                    ],
                ];
            }
            $return_trip=[];
            if($book->plane_trips[1]?->airport_source->id?? null){
                $return_trip=[
                    'airport_source'=>[
                        'id'=>$book->plane_trips[1]->airport_source->id?? null,
                        'name'=>$book->plane_trips[1]->airport_source->name?? null,
                    ]??null,
                    'airport_destination'=>[
                        'id'=>$book->plane_trips[1]->airport_destination->id?? null,
                        'name'=>$book->plane_trips[1]->airport_destination->name?? null,
                    ]??null,
                ];
            }
            $dynamic_trip=[
                'dynamic_trip'=>$bookData,
                'source_trip'=>$book->source_trip,
                'destination_trip'=>$book->destination_trip,
                'going_trip'=>$going_trip,
                'return_trip'=>$return_trip,
            ];
        }catch(Exception $e)
        {
            return 1;
        }
        return $dynamic_trip;

    }
    public function index()
    {//where('type','dynamic')->
        $dynamic_book=Booking::where('user_id',auth()->id())
                             ->AvailableRooms()
                            //   ->select('id','trip_name','price','number_of_people','start_date','end_date','trip_note')
                             ->get();
        return $dynamic_book;

        // with('rooms:id,capacity,hotel_id','rooms.hotel:id,name','source_trip:id,name',
        //                             'destination_trip','plane_trips:id,plane_id,current_price,flight_date,landing_date',
        //                             'places:id,name,place_price,text,area_id','places.area:id,name')
        //                        ->

    }
    public function get_all_plane_trip()
    {
        $dynamic_book=Booking::where('type','plane')->where('user_id',auth()->id())
                             ->AvailableRooms()
                             ->select('id','user_id','source_trip_id','destination_trip_id','trip_name','price','number_of_people','start_date','end_date','trip_note')
                             ->get();
        return $dynamic_book;
    }
    public function get_all_hotel_trip()
    {
        $dynamic_book=Booking::with('source_trip','destination_trip')->where('type','hotel')->where('user_id',auth()->id())
                             ->AvailableRooms()
                            //  ->select('id','user_id','source_trip_id','destination_trip_id','trip_name','price','number_of_people','start_date','end_date','trip_note','rooms_count')
                             ->get();
        return $dynamic_book;
    }
    public function get_all_dynamic_trip()
    {
        $dynamic_book=Booking::where('type','dynamic')->where('user_id',auth()->id())
                             ->AvailableRooms()
                             ->get();
        return $dynamic_book;
    }
    public function checkPlaneTrip($request,$id){
         try{
            $plane_price=0;
            $numberOfSeat=$request['number_of_people'];
            if($request['plane_trip_id'] != null){
                $plane_trip = PlaneTrip::where('id', $request['plane_trip_id'])->first();
                $plane_price+=($plane_trip['current_price']*$numberOfSeat);
                if ($plane_trip['available_seats'] < $numberOfSeat){
                    return 1;
                }
                return $plane_price;
            }
        }catch(Exception $exception){
            throw new Exception($exception->getMessage());
        }
    }
    public function checkPlaneTripaway($request,$id){
        try{
            $plane_price=0;
             $numberOfSeat=$request['number_of_people'];
            if($request['plane_trip_id'] != null){
            $plane_trip = PlaneTrip::where('id', $request['plane_trip_id'])->first();
            $plane_price+=($plane_trip['current_price']*$numberOfSeat);
            if ($plane_trip['available_seats'] < $numberOfSeat) {
                return 2;
            }
            return $plane_price;
        }
       }catch(Exception $exception){
        throw new Exception($exception->getMessage());
       }
    }
    public function checkHotel($request,$id){
        try{
            if($request['hotel_id'] != null)
            {
                $datetime1 = new DateTime($request['start_date']);
                $datetime2 = new DateTime($request['end_date']);
                $interval = $datetime1->diff($datetime2);
                $period = $interval->format('%a');
                $hotel_price=0;
                $rooms_2 = Room::available($request['start_date'], $request['end_date'])
                            ->where('hotel_id', $request['hotel_id'])
                            ->where('capacity', 2)
                            ->count();
                $price_rooms_2=Room::available($request['start_date'], $request['end_date'])
                ->where('hotel_id', $request['hotel_id'])
                ->where('capacity', 2)->first()['price']??null;
                if ($request['count_room_C2'] > $rooms_2 || ($price_rooms_2==null && $request['count_room_C2']!=0) ) {
                    return 5;
                }
                $hotel_price+=$request['count_room_C2']*$price_rooms_2*$period;
                $rooms_4 = Room::available($request['start_date'], $request['end_date'])
                                ->where('hotel_id', $request['hotel_id'])
                                ->where('capacity', 4)
                                ->count();
                $price_rooms_4=Room::available($request['start_date'], $request['end_date'])
                ->where('hotel_id', $request['hotel_id'])
                ->where('capacity', 4)->first()['price']??null;
                if ($request['count_room_C4'] > $rooms_4 || ($price_rooms_4==null && $request['count_room_C4']!=0)) {
                    return 6;
                }
                $hotel_price+=$request['count_room_C4']*$price_rooms_4*$period;

                $rooms_6 = Room::available($request['start_date'], $request['end_date'])
                                ->where('hotel_id', $request['hotel_id'])
                                ->where('capacity', 6)
                                ->count();
                $price_rooms_6=Room::available($request['start_date'], $request['end_date'])
                ->where('hotel_id', $request['hotel_id'])
                ->where('capacity', 6)->first()['price']??null;
                if ($request['count_room_C6'] > $rooms_6 || ($price_rooms_6==null && $request['count_room_C6']!=0)) {
                    return 7;
                }
                $hotel_price+=$request['count_room_C6']*$price_rooms_6*$period;
                return $hotel_price;
            }
            }catch(Exception $exception){
                //return 8;
                throw new Exception($exception->getMessage());
            }
    }
    public function bookPlaneTrip($request,$id){
    try{
        $booking=Booking::findOrFail($id);
        $trip_price=0;
        if($request['plane_trip_id'] != null){
            $plane_trip = PlaneTrip::where('id', $request['plane_trip_id'])->first();
            $trip_price+=($plane_trip['current_price'] * $request['number_of_people']);
            $plane_trip['available_seats'] -= $request['number_of_people'];
            $plane_trip->save();
            // go away
            BookPlane::create([
                'book_id' => $booking->id,
                'plane_trip_id' => $request['plane_trip_id'],
            ]);
        }
    }catch(Exception $exception){
        throw new Exception($exception->getMessage());
    }
    }
    public function bookHotel($request,$id){
        try{
            $booking=Booking::findOrFail($id);
            $trip_price=0;
            $datetime1 = new DateTime($request['start_date']);
            $datetime2 = new DateTime($request['end_date']);
            $interval = $datetime1->diff($datetime2);
            $period = $interval->format('%a');
            if($request['hotel_id'] != null)
            {   // rooms
                            if($request['count_room_C2']!=null)
                            {
                            $rooms = Room::available($request['start_date'], $request['end_date'])
                            ->where('hotel_id', $request['hotel_id'])
                            ->where('capacity', 2)
                            ->get();
                            for ($i = 0; $i < $request['count_room_C2']; $i++) {
                                $book_room=BookingRoom::create([
                                    'book_id' => $booking->id,
                                    'room_id' => $rooms[$i]['id'],
                                    'current_price' => $rooms[$i]['price'],
                                    'start_date' => $request['start_date'],
                                    'end_date' => $request['end_date']
                                ]);

                            $trip_price+=($book_room['current_price']*$period);
                            }
                            }
                            if($request['count_room_C4']!=null)
                            {
                            $rooms = Room::available($request['start_date'], $request['end_date'])
                            ->where('hotel_id', $request['hotel_id'])
                            ->where('capacity', 4)
                            ->get();
                            for ($i = 0; $i < $request['count_room_C4']; $i++) {
                                $book_room=BookingRoom::create([
                                    'book_id' => $booking->id,
                                    'room_id' => $rooms[$i]['id'],
                                    'current_price' => $rooms[$i]['price'],
                                    'start_date' => $request['start_date'],
                                    'end_date' => $request['end_date']
                                ]);
                                $trip_price+=($book_room['current_price']*$period);
                            }
                            }
                            if($request['count_room_C6']!=null)
                            {
                            $rooms = Room::available($request['start_date'], $request['end_date'])
                            ->where('hotel_id', $request['hotel_id'])
                            ->where('capacity', 6)
                            ->get();
                            for ($i = 0; $i < $request['count_room_C6']; $i++) {
                                $book_room=BookingRoom::create([
                                    'book_id' => $booking->id,
                                    'room_id' => $rooms[$i]['id'],
                                    'current_price' => $rooms[$i]['price'],
                                    'start_date' => $request['start_date'],
                                    'end_date' => $request['end_date']
                                ]);
                                $trip_price+=($book_room['current_price']*$period);
                            }
                            }
            }
        }catch(Exception $exception){
            throw new Exception($exception->getMessage());
        }
    }
    public function update_dynamic_trip($request,$id){
        try{
            $booking =Booking::findOrFail($id);
            if($request['end_date'] < $booking['end_date']){
                return 9;
            }
            $last_price=$booking->price;
            $trip_price=0;
            // $datetime1 = new DateTime($booking['start_date']);
            // $datetime2 = new DateTime($request['end_date']);
            // $interval = $datetime1->diff($datetime2);
            // $period = $interval->format('%a');
            $hotel_id=null;
            $plane_trip_id=null;
            $plane_trip_away_id=null;
            // $numberOfOldSeat=$booking['number_of_people'];
            if(Bookingroom::where('book_id',$booking->id)->first()){
                $hotel_id=$booking->rooms->first()['hotel']['id']??null;// get hotel id from existing booking room
            }
            if($booking->plane_trips){
                $plane_trip_id=$booking?->plane_trips[0]['id']??null;
                $plane_trip_away_id=$booking?->plane_trips[1]['id']??null;
            }
            if($plane_trip_id ==null && $request['plane_trip_away_id']!=null){
                    if($request['plane_trip_id']==null){
                        return 8;
                    }
            }
            //check places
            if($request['place_ids'] != null){
                foreach ($request['place_ids'] as $place) {
                    $trip_price+= Place::where('id', $place)->first()->place_price;
                }
            }
            $old_price_places=0;
            if($booking->places!=null){
            foreach ($booking->places as $place) {
                $old_price_places+= Place::where('id', $place->id)->first()->place_price;// here
            }
            }
            // check go trip
            if($plane_trip_id!=null){
                    $data=[
                        'plane_trip_id'=>$plane_trip_id,
                        'number_of_people'=>$request['number_of_people'],
                    ];
                    $check =$this->checkPlaneTrip($data,$booking->id);
                    if($check==1 ||$check==8){
                        return $check;
                }
                $trip_price+=$check;
            }else{
                $data=[
                    'plane_trip_id'=>$request['plane_trip_id']??null,
                    'number_of_people'=>$request['number_of_people']+$booking['number_of_people'],
                ];
                $check =$this->checkPlaneTrip($data,$booking->id);
                if($check==1 ||$check==8){
                    return $check;
                }
                $trip_price+=$check;
            }
            // check return trip
            if($plane_trip_away_id!=null){
                if($request['end_date'] != $booking['end_date']){
                    $data=[
                        'plane_trip_id'=>$request['plane_trip_away_id']??null,
                        'number_of_people'=>$request['number_of_people']+$booking['number_of_people'],
                    ];
                    $check=$this->checkPlaneTripaway($data,$booking->id);
                    if($check==2 ||$check==8){
                        return $check;
                    }
                    $trip_price+=$check;
                    $trip_price-=$booking['number_of_people']*PlaneTrip::where('id', $plane_trip_away_id)->first()['current_price'];
                }else{
                    $data=[
                        'plane_trip_id'=>$plane_trip_away_id,
                        'number_of_people'=>$request['number_of_people'],
                    ];
                    $check=$this->checkPlaneTripaway($data,$booking->id);
                    if($check==2 ||$check==8){
                        return $check;
                    }
                    $trip_price+=$check;
                }
            }else{
                $data=[
                    'plane_trip_id'=>$request['plane_trip_away_id']??null,
                    'number_of_people'=>$request['number_of_people']+$booking['number_of_people'],
                ];
                $check=$this->checkPlaneTripaway($data,$booking->id);
                if($check==2 ||$check==8){
                    return $check;
                }
                $trip_price+=$check;
            }
            // check + booking hotel
            if($hotel_id!=null)
            {
                // to check if there are an enough rooms in this hotel
                $bookRoomCount=BookingRoom::where('book_id',$booking['id'])->get();
                $count_2=0;
                $count_4=0;
                $count_6=0;
                $date = Carbon::createFromFormat('Y-m-d',$booking['end_date']);
               //$date = $date->addDays(1);
                // if the date is changed => calculate the count of old room
                if($request['end_date'] != $booking['end_date'])
                {
                    foreach($bookRoomCount as $count){
                        if(Room::where('id',$count->room_id)->first()->capacity ==2){
                            $count_2++;
                        }
                        if(Room::where('id',$count->room_id)->first()->capacity ==4){
                            $count_4++;
                        }
                        if(Room::where('id',$count->room_id)->first()->capacity ==6){
                            $count_6++;
                        }
                    }
                    // calculate date for old room
                    $data=[
                        'start_date'=>$booking['end_date'],
                        'end_date'=>$request['end_date'],
                        'hotel_id'=>$hotel_id,
                        'count_room_C2'=>$count_2,
                        'count_room_C4'=>$count_4,
                        'count_room_C6'=>$count_6,
                    ];
                    $check=$this->checkHotel($data,$booking->id);
                    if($check==5 || $check==6 ||$check==7 ||$check==8){ return $check;}
                    $trip_price+=$check;

                    if($trip_price>Bank::where('email',auth()->user()['email'])->first()->money){
                        return 55;
                    }

                }
                    // calculate date for new room
                $data=[
                    'start_date'=>$booking['start_date'],
                    'end_date'=>$request['end_date'],
                    'hotel_id'=>$hotel_id,
                    'count_room_C2'=>$request['count_room_C2'],
                    'count_room_C4'=>$request['count_room_C4'],
                    'count_room_C6'=>$request['count_room_C6']
                ];
                $check =$this->checkHotel($data,$booking->id);
                if($check==5 || $check==6 ||$check==7 ||$check==8){ return $check;}
                $trip_price+=$check;
                if($trip_price>Bank::where('email',auth()->user()['email'])->first()->money){
                    return 55;
                }
                if($request['end_date'] != $booking['end_date']){
                      ##### delete old booking room
                       BookingRoom::where('book_id',$booking['id'])->delete();
                }

                    // booking hotel
                    $data=[
                        'start_date'=>$booking['start_date'],
                        'end_date'=>$request['end_date'],
                        'hotel_id'=>$hotel_id,
                        'count_room_C2'=>$request['count_room_C2']+$count_2,
                        'count_room_C4'=>$request['count_room_C4']+$count_4,
                        'count_room_C6'=>$request['count_room_C6']+$count_6
                    ];
                    $this->bookHotel($data,$booking['id']);

            }else{
                    if($request['hotel_id'] != null)
                    {
                        if($request['count_room_C2']==null && $request['count_room_C4']==null &&$request['count_room_C6']==null){
                            return 8;
                        }
                        $data=[
                            'start_date'=>$booking['start_date'],
                            'end_date'=>$request['end_date'],
                            'hotel_id'=>$request['hotel_id'],
                            'count_room_C2'=>$request['count_room_C2'],
                            'count_room_C4'=>$request['count_room_C4'],
                            'count_room_C6'=>$request['count_room_C6']
                        ];
                        $check=$this->checkHotel($data,$booking->id);
                        if($check==5 || $check==6 ||$check==7 ||$check==8){ return $check;}
                        $trip_price+=$check;
                        if($trip_price>Bank::where('email',auth()->user()['email'])->first()->money){
                            return 55;
                        }
                    }


                    if($request['hotel_id'] != null)
                    {   // rooms
                        if($trip_price > Bank::where('email',auth()->user()->email)->first()['money']){
                            return 55;
                        }

                        $data=[
                            'hotel_id'=>$request['hotel_id'],
                            'start_date'=>$booking['start_date'],
                            'end_date'=>$request['end_date'],
                            'count_room_C2'=>$request['count_room_C2'],
                            'count_room_C4'=>$request['count_room_C4'],
                            'count_room_C6'=>$request['count_room_C6']
                        ];
                        $this->bookHotel($data,$booking->id);
                    }
            }
            // booking going trip
            if($plane_trip_id!=null){
                    $plane_trip = PlaneTrip::where('id', $plane_trip_id)->first();
                    $plane_trip['available_seats'] -= $request['number_of_people'];
                    $plane_trip->save();
            }else{
                $data=[
                    'plane_trip_id'=>$request['plane_trip_id']??null,
                    'number_of_people'=>$request['number_of_people']+$booking['number_of_people'],
                ];
                $this->bookPlaneTrip($data,$booking->id);
            }
            // booking return trip
            if($plane_trip_away_id!=null){
                // if the date is changed
                if($request['end_date'] != $booking['end_date']){
                    BookPlane::where('plane_trip_id',$plane_trip_away_id)->delete();
                    $data=[
                        'plane_trip_id'=>$request['plane_trip_away_id']??null,
                        'number_of_people'=>$request['number_of_people']+$booking['number_of_people'],
                    ];
                    $this->bookPlaneTrip($data,$booking->id);

                    $plane_trip=PlaneTrip::where('id',$plane_trip_away_id)->first();
                    $plane_trip['available_seats']+=$booking['number_of_people'];
                    $plane_trip->save();
                }else{
                    $plane_trip = PlaneTrip::where('id', $plane_trip_away_id)->first();
                    $plane_trip['available_seats'] -= $request['number_of_people'];
                    $plane_trip->save();
                }
            }else{

                $data=[
                    'plane_trip_id'=>$request['plane_trip_away_id']??null,
                    'number_of_people'=>$request['number_of_people']+$booking['number_of_people'],
                ];
                $this->bookPlaneTrip($data,$booking->id);
            }
            // delete old place
            bookPlace::where('book_id',$booking->id)->delete();
            // booking places
            if($request['place_ids'] != null){
                foreach ($request['place_ids'] as $place) {
                    $book_place=BookPlace::firstOrCreate(
                    [
                        'place_id' => $place,
                        'book_id' => $booking->id,
                        'current_price' => Place::where('id', $place)->first()->place_price,
                    ]);
                }
            }
            // booking activity
            ActivityBook::where('booking_id',$booking['id'])->delete();
            foreach ($request['activities'] as $activity) {
                ActivityBook::create([
                     'booking_id' => $booking->id,
                     'activity_id' => $activity,
                 ]);
             }
                $booking['end_date']=$request['end_date'];
                $booking['number_of_people']+=$request['number_of_people'];
                $booking['price']+=$trip_price;
                $booking['price']-=$old_price_places;
                $booking['trip_name']=$request['trip_name']??$booking['trip_name'];
                $booking['trip_note']=$request['trip_note']??$booking['trip_note'];
                $booking->save();
                $my_account=Bank::where('email',auth()->user()['email'])->first();
                $my_account['money']+=$last_price;
                $my_account['money']-=$booking->price;
                $my_account['payments']-=$last_price;
                $my_account['payments']+=$booking->price;
                $my_account->save();
                $dynamic_trip=[
                    'data'=>$this->showDynamicTrip($booking['id']),
                    'added price'=>$booking['price']-$last_price];
                return $dynamic_trip;
        }catch(Exception $exception){
            throw new Exception($exception->getMessage());
        }
    }
    public function updatePlaneBook($request,$id){
        try{
            $booking =Booking::findOrFail($id);
            $plane_trip_id=null;
            $plane_trip_away_id=null;
            $trip_price=0;
            $last_price=$booking['price'];
            if($booking->plane_trips){
            $plane_trip_id=$booking?->plane_trips[0]['id']??null;
            $plane_trip_away_id=$booking?->plane_trips[1]['id']??null;
            if($plane_trip_id==null && $plane_trip_away_id==null ){
                return 8;
            }
            $data=[
                'number_of_people'=>$request['number_of_people'],
                'plane_trip_id'=>$plane_trip_id
            ];
            $check=$this->checkPlaneTrip($data,$id);
            if($check==1 ||$check==8){
                return $check;
            }
            $trip_price+= $check;
            $data=[
                'number_of_people'=>$request['number_of_people'],
                'plane_trip_id'=>$plane_trip_away_id
            ];
            $check=$this->checkPlaneTripaway($data,$id);
            if($check==1 ||$check==8){
                return $check;
            }
            $trip_price+= $check;
            if($trip_price>Bank::where('email',auth()->user()['email'])->first()->money){
                return 55;
            }
            if($plane_trip_id!=null){
            $plane_trip = PlaneTrip::where('id', $plane_trip_id)->first();
            $plane_trip['available_seats'] -= $request['number_of_people'];
            $plane_trip->save();
            }
            if($plane_trip_away_id!=null){
            $plane_trip_away = PlaneTrip::where('id', $plane_trip_away_id)->first();
            $plane_trip_away['available_seats'] -= $request['number_of_people'];
            $plane_trip_away->save();
            }
            $booking['price']+=$trip_price;
            $booking['number_of_people']+=$request['number_of_people'];
            $booking['trip_name']=$request['trip_name']??$booking['trip_name'];
            $booking['trip_note']=$request['trip_note']??$booking['trip_note'];
            $booking->save();
            $my_account=Bank::where('email',auth()->user()['email'])->first();
            $my_account['money']+=$last_price;
            $my_account['money']-=$booking->price;
            $my_account['payments']-=$last_price;
            $my_account['payments']+=$booking->price;
            $my_account->save();
            $dynamic_trip=[
                'data'=>$this->show_plane_trip($booking['id']),
                'added_price'=>$booking['price']-$last_price
            ];
            return $dynamic_trip;
        }else{
            return 8;
        }
        }catch(Exception $exception){
            throw new Exception($exception->getMessage());
        }

    }
    public function updateHotelBook($request,$id){
        try{
            $booking =Booking::findOrFail($id);
            $datetime1 = new DateTime($booking['start_date']);
            $datetime2 = new DateTime($request['end_date']);
            $interval = $datetime1->diff($datetime2);
            $period = $interval->format('%a');
            $hotel_id=null;
            $trip_price=0;
            $last_price=$booking['price'];
            if(Bookingroom::where('book_id',$booking->id)->first()){
                $hotel_id=$booking->rooms->first()['hotel']['id'];// get hotel id from existing booking room
            }
            if($request['end_date']< $booking['end_date']){
                return 9;
            }
            if($hotel_id!=null){
                $bookRoomCount=BookingRoom::where('book_id',$booking['id'])->get();
                $count_2=0;
                $count_4=0;
                $count_6=0;
                $date = Carbon::createFromFormat('Y-m-d',$booking['end_date']);
                if($request['end_date']!=$booking['end_date']){
                    foreach($bookRoomCount as $count){
                        if(Room::where('id',$count->room_id)->first()->capacity ==2){
                            $count_2++;
                        }
                        if(Room::where('id',$count->room_id)->first()->capacity ==4){
                            $count_4++;
                        }
                        if(Room::where('id',$count->room_id)->first()->capacity ==6){
                            $count_6++;
                        }
                    }
                     // calculate date for old room
                     $data=[
                        'start_date'=>$booking['end_date'],
                        'end_date'=>$request['end_date'],
                        'hotel_id'=>$hotel_id,
                        'count_room_C2'=>$count_2,
                        'count_room_C4'=>$count_4,
                        'count_room_C6'=>$count_6,
                    ];
                    $check=$this->checkHotel($data,$booking->id);
                    if($check==5 || $check==6 ||$check==7 ||$check==8){ return $check;}
                    $trip_price+=$check;


                    if($trip_price>Bank::where('email',auth()->user()['email'])->first()->money){
                        return 55;
                    }
                     // calculate date for new room
                    $data=[
                        'start_date'=>$booking['start_date'],
                        'end_date'=>$request['end_date'],
                        'hotel_id'=>$hotel_id,
                        'count_room_C2'=>$request['count_room_C2'],
                        'count_room_C4'=>$request['count_room_C4'],
                        'count_room_C6'=>$request['count_room_C6']
                    ];
                    $check =$this->checkHotel($data,$booking->id);
                    if($check==5 || $check==6 ||$check==7 ||$check==8){ return $check;}
                    $trip_price+=$check;
                    if($trip_price>Bank::where('email',auth()->user()['email'])->first()->money){
                        return 55;
                    }
                    if($request['end_date'] != $booking['end_date']){
                        ##### delete old booking room
                        BookingRoom::where('book_id',$booking['id'])->delete();
                    }

                    // booking hotel
                    $data=[
                       'start_date'=>$booking['start_date'],
                       'end_date'=>$request['end_date'],
                       'hotel_id'=>$hotel_id,
                       'count_room_C2'=>$request['count_room_C2']+$count_2,
                       'count_room_C4'=>$request['count_room_C4']+$count_4,
                       'count_room_C6'=>$request['count_room_C6']+$count_6
                   ];
                   $this->bookHotel($data,$booking['id']);

                }else{
                $data=[
                    'start_date'=>$booking['start_date'],
                    'end_date'=>$booking['end_date'],
                    'hotel_id'=>$hotel_id,
                    'count_room_C2'=>$request['count_room_C2'],
                    'count_room_C4'=>$request['count_room_C4'],
                    'count_room_C6'=>$request['count_room_C6']
                ];
                $check =$this->checkHotel($data,$id);
                if($check==5 || $check==6 ||$check==7 ||$check==8){ return $check;}
                $trip_price+=$check;
                if($trip_price>Bank::where('email',auth()->user()['email'])->first()->money){
                    return 55;
                }
                $this->bookHotel($data,$booking['id']);
                }
            }else{ return 8;
            }
            $booking['price']+=$trip_price;
            $booking['end_date']=$request['end_date'];
            $booking['trip_name']=$request['trip_name']??$booking['trip_name'];
            $booking['trip_note']=$request['trip_note']??$booking['trip_note'];
            $booking->save();
            $my_account=Bank::where('email',auth()->user()['email'])->first();
            $my_account['money']+=$last_price;
            $my_account['money']-=$booking->price;
            $my_account['payments']-=$last_price;
            $my_account['payments']+=$booking->price;
            $my_account->save();
            $dynamic_trip=[
                'data'=>$this->show_hotel_trip($booking['id']),
                'added price'=>$booking['price']-$last_price];
            return $dynamic_trip;
        }catch(Exception $exception){
            throw new Exception($exception->getMessage());
        }
    }
    public function delete_dynamic_trip($id){
        try
        {
            $date=Carbon::now()->format('Y-m-d');
            $booking =Booking::findOrFail($id);
            if($date>$booking['start_date']){
                return 20;
            }
            if(auth()->id()!= $booking['user_id']){
                return 8;
            }
            $plane_trip=null;
            $plane_trip_away=null;
            if($booking->plane_trips){
                $plane_trip=$booking?->plane_trips[0]['id']??null;
                $plane_trip_away=$booking?->plane_trips[1]['id']??null;
            }
            if( $plane_trip){
                $plane=planeTrip::where('id',$plane_trip)->first();
                $plane['available_seats']+=$booking['number_of_people'];
                $plane->save();
            }
            if( $plane_trip_away){
                $plane=planeTrip::where('id',$plane_trip_away)->first();
                $plane['available_seats']+=$booking['number_of_people'];
                $plane->save();
            }
            $datetime1 = new DateTime($booking['start_date']);
            $datetime2 = new DateTime($date);
            $interval = $datetime1->diff($datetime2);
            $period = $interval->format('%a');
            $bank=Bank::where('email',auth()->user()['email'])->first();
            if($period>7){
                    $bank->money+=$booking['price'];
            }
            elseif($period>1){
                    $bank->money+=(0.5*$booking['price']);
            }
            $bank->save();
            $booking->delete();
            return 'delete done';
        }catch(Exception $exception){
            throw new Exception($exception->getMessage());
        }
    }
    public function get_all_dynamic_book($request){
        if($request->user()->hasRole('Super Admin')){
            $dynamic_book=Booking::with('user')->where('type','dynamic')->orwhere('type','hotel')->orwhere('type','plane')
                            ->orderby('type')
                            ->AvailableRooms()
                            ->get();
        }else if($request->user()->hasRole('Trip manger')){
            $admin_trip=User::where('id',auth()->id())->first();
            $dynamic_book=Booking::where('type','dynamic')->where('destination_trip_id',$admin_trip['position'])
                            // ->orderby('type')
                            ->AvailableRooms()
                            ->get();
        }
        // $admin_trip=User::where('id',auth()->id())->first();


        // $dynamic_book=Booking::where('type','dynamic')->orwhere('type','hotel')->orwhere('type','plane')
        //                     ->orderby('type')
        //                     ->AvailableRooms()
        //                     ->get();
        return $dynamic_book;
    }
    public function get_all_hotel_book(){
        $hotel=Hotel::where('user_id',auth()->id())->first();

        $booking_romm=BookingRoom::where('rooms',function ($query) use ($hotel){
            $query->where('hotel_id',$hotel->id);})->get();

        // $admin_trip=User::where('id',auth()->id())->first();
        // $dynamic_book=Booking::where('type','hotel')->where('destination_trip_id',$admin_trip['position'])
        //                     ->AvailableRooms()
        //                     ->get();
        return $booking_romm;
    }
    public function get_all_plane_book(){
        $admin_trip=User::where('id',auth()->id())->first();
        $dynamic_book=Booking::where('type','plane')->where('destination_trip_id',$admin_trip['position'])
                            ->AvailableRooms()
                            ->select('id','user_id','source_trip_id','destination_trip_id','trip_name','price','number_of_people','start_date','end_date','trip_note')
                            ->get();
        return $dynamic_book;
    }
}
