<?php

namespace App\Repositories;

use App\Models\ActivityBook;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\BookPlace;
use App\Models\BookPlane;
use App\Models\Place;
use App\Models\PlaneTrip;
use App\Models\Room;
use DateTime;
use Exception;

class BookRepository implements BookRepositoryInterface
{
    public function store_Admin($request)
    {
        $trip_price=0;
        // to check if there are an enough rooms in this hotel
        //if($request['hotel_id'] != null){
            $room_count = $request['number_of_people'] / $request['trip_capacity'];
            if ($request['number_of_people'] % $request['trip_capacity'] > 0) $room_count++;
            $rooms = Room::available($request['start_date'], $request['end_date'])
                ->where('hotel_id', $request['hotel_id'])
                ->where('capacity', $request['trip_capacity'])
                ->count();
            if ($rooms < $room_count) {
               return 1;
            }
        //}

        $plane_trip = PlaneTrip::where('id', $request['plane_trip'])->first();
        if ($plane_trip['available_seats'] < $request['number_of_people']) {
             return 2;
        }
        $plane_trip_away = PlaneTrip::where('id', $request['plane_trip_away'])->first();
        if ($plane_trip_away['available_seats'] < $request['number_of_people']) {
            return 3;
        }

        $plane_trip['available_seats'] -= $request['number_of_people'];
        $plane_trip->save();
        $plane_trip_away['available_seats'] -= $request['number_of_people'];
        $plane_trip_away->save();
        try {
            $booking = Booking::create([
                'user_id' => auth()->user()->id,
                'source_trip_id' => $request['source_trip_id'],
                'destination_trip_id' => $request['destination_trip_id'],
                'trip_name' => $request['trip_name'],
                //'price' => $request['price'],
                'number_of_people' => $request['number_of_people'],
                'trip_capacity' => $request['trip_capacity'],
                'start_date' => $plane_trip['flight_date'],// to submit the flight date same as start date trip
                'end_date' => $plane_trip_away['flight_date'],// to submit the flight date same as end date trip
                'trip_note' => $request['trip_note'],
                'type' => 'static',
            ]);
            foreach ($request['places'] as $place) {
                $book_place = BookPlace::create([
                    'book_id' => $booking->id,
                    'place_id' => $place,
                    'current_price' => Place::where('id', $place)->first()->place_price,
                ]);
                $trip_price+=$book_place['current_price'];
            }
            ###
            foreach ($request['activities'] as $activity) {
               ActivityBook::create([
                    'booking_id' => $booking->id,
                    'activity_id' => $activity,
                ]);
            }

            // go away
            $book_plane = BookPlane::create([
                'book_id' => $booking->id,
                'plane_trip_id' => $request['plane_trip'],
            ]);
            // back away
            $book_plane_away = BookPlane::create([
                'book_id' => $booking->id,
                'plane_trip_id' => $request['plane_trip_away'],
            ]);


            // if($request['hotel_id'] != null){
                // rooms
                $rooms = Room::available($request['start_date'], $request['end_date'])
                ->where('hotel_id', $request['hotel_id'])
                ->where('capacity', $request['trip_capacity'])
                ->get();
                for ($i = 0; $i < $room_count; $i++) {
                    $book_room=BookingRoom::create([
                        'book_id' => $booking->id,
                        'room_id' => $rooms[$i]['id'],
                        'current_price' => $rooms[$i]['price'],
                        'start_date' => $booking['start_date'],
                        'end_date' => $booking['end_date']
                    ]);
                }
                //to calculate the trip price but the place price is above
                $trip_price+=$plane_trip['current_price'];
                $trip_price+=$plane_trip_away['current_price'];
                $datetime1 = new DateTime($booking['start_date']);
                $datetime2 = new DateTime($booking['end_date']);
                $interval = $datetime1->diff($datetime2);
                $days = $interval->format('%a');
                $trip_price+=($book_room['current_price']*$days);
                $trip_price-=($trip_price*$request['ratio']);// if there is an ratio from the price
                $booking['price']=$trip_price;
                $booking->save();
            // }
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
        }

         $static_book=Booking::where('id',$booking->id)->first();

        return $this->showStaticTrip($static_book['id']);
    }

    public function editAdmin($request,$id)
    {
        try
        {
            $booking= Booking::findOrFail($id);
            $trip_price=0;
            // to check if there are an enough rooms in this hotel
            $bookRoomCount=BookingRoom::where('book_id',$booking['id'])->count();
            $numberOfOldSeat=$booking['number_of_people'];
            if(($request['start_date'] == $booking['start_date']) && ($request['end_date'] == $booking['end_date'])){
                $bookRoomCount=0;
                $numberOfOldSeat=0;
            }
            $hotel_id=$booking->rooms->first()['hotel']['id'];// get hotel id from existing booking room
            $room_count = $request['number_of_people'] / $booking['trip_capacity'];
            // show if there are rooms to book
            if ($request['number_of_people'] % $booking['trip_capacity'] > 0) $room_count++;

                $rooms = Room::available($request['start_date'], $request['end_date'])
                                ->where('hotel_id', $hotel_id)
                                ->where('capacity', $booking['trip_capacity'])
                                ->count();
            if ($rooms < $room_count+$bookRoomCount) {
                return 1;
            }
            // show if there are available_seats to book in going trip
            $plane_trip = PlaneTrip::where('id', $request['plane_trip'])->first();
            if ($plane_trip['available_seats'] < $request['number_of_people']) {
                 return 2;
            }
            // show if there are available_seats to book in return trip
            $plane_trip_away = PlaneTrip::where('id', $request['plane_trip_away'])->first();
            if ($plane_trip_away['available_seats'] < $request['number_of_people']) {
                return 3;
            }

            if(($request['start_date'] != $booking['start_date']) || ($request['end_date'] != $booking['end_date']))
            {
                ##### delete old booking room
                BookingRoom::where('book_id',$booking['id'])->delete();
            }
            // // rooms
            $rooms = Room::available($request['start_date'], $request['end_date'])
                            ->where('hotel_id', $hotel_id)#####
                            ->where('capacity', $booking['trip_capacity'])
                            ->get();
            $bookRoom=BookingRoom::where('book_id',$booking['id'])->first();
            for ($i = 0; $i < $room_count+$bookRoomCount; $i++) {
                BookingRoom::create([
                    'book_id' => $booking->id,
                    'room_id' => $rooms[$i]['id'],
                    'current_price' => $bookRoom['current_price'],
                    'start_date' => $request['start_date'],
                    'end_date' => $request['end_date']
                ]);
            }

            $plane_trip['available_seats'] -= $request['number_of_people']+$numberOfOldSeat;
            $plane_trip->save();
            $plane_trip_away['available_seats'] -= $request['number_of_people']+$numberOfOldSeat;
            $plane_trip_away->save();

            ##### update the seats in this trip
            $bookplane=$booking->plane_trips;
            $bookplane[0]['available_seats']+=$numberOfOldSeat;
            $bookplane[1]['available_seats']+=$numberOfOldSeat;
            $bookplane[0]->save();
            $bookplane[1]->save();
            BookPlane::where('book_id',$booking['id'])->delete();

            $booking->trip_name = $request['trip_name']?? $booking['trip_name'];
            $booking->number_of_people = $request['number_of_people']+$booking['number_of_people'];
            $booking->start_date = $request['start_date']?? $booking['start_date'];
            $booking->end_date = $request['end_date']?? $booking['end_date'];
            $booking->trip_note = $request['trip_note']?? $booking['trip_note'];
            $booking->save();

            if($request['places'] != null){
                foreach ($request['places'] as $place) {
                    $book_place=BookPlace::firstOrCreate(
                    [
                        'place_id' => $place,
                        'book_id' => $booking->id,
                        'current_price' => Place::where('id', $place)->first()->place_price,
                    ]);

                }
            }

            // go away
            $book_plane = BookPlane::create([
                'book_id' => $booking->id,
                'plane_trip_id' => $request['plane_trip'],
            ]);

            // back away
            $book_plane_away = BookPlane::create([
                'book_id' => $booking->id,
                'plane_trip_id' => $request['plane_trip_away'],
            ]);
        }catch(Exception $e){
            //return 4;
            return response()->json([
                'message'=>$e->getMessage()
            ]);
        }
          return Booking::with(['places:id,name,place_price,text','places.images:id,image',
                                        'plane_trips:id,airport_source_id,airport_destination_id,current_price,available_seats,flight_date,landing_date',
                                        'plane_trips.airport_source:id,name',
                                        'plane_trips.airport_destination:id,name',
                                    ])
                                    ->AvailableRooms()
                                    ->where('id',$booking->id)->get();
    }

    public function showStaticTrip($id)
    {
        try{
            $book=Booking::where('type','static')
                        ->AvailableRooms()
                        ->findOrFail($id);

            $bookData=[
                'id'=>$book['id'],
                'source_trip_id'=>$book['source_trip_id'],
                'destination_trip_id'=>$book['destination_trip_id'],
                'trip_name'=>$book['trip_name'],
                'price'=>$book['price'],
                'number_of_people'=>$book['number_of_people'],
                'trip_capacity'=>$book['trip_capacity'],
                'start_date'=>$book['start_date'],
                'end_date'=>$book['end_date'],
                'stars'=>$book['stars'],
                'trip_note'=>$book['trip_note'],
                'type'=>$book['type'],
                'rooms_count'=>$book['rooms_count'],
            ];
            $activities=$book?->activities;
            $going_trip=[
                'airport_source'=>[
                    'id'=>$book->plane_trips[0]->airport_source->id?? null,
                    'name'=>$book->plane_trips[0]->airport_source->name?? null,
                ]??null,
                'airport_destination'=>[
                    'id'=>$book->plane_trips[0]->airport_destination->id?? null,
                    'name'=>$book->plane_trips[0]->airport_destination->name?? null,
                ]??null,
            ];
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
            $hotel=[
                'id'=>$book->rooms?->first()['hotel']['id']?? null,
                'name'=>$book->rooms?->first()['hotel']['name']?? null,
            ];
            $static_trip=[
                'static_trip'=>$bookData,
                'activities'=>$activities,
                'source_trip'=>$book->source_trip,
                'destination_trip'=>$book->destination_trip,
                'places'=>$book->places,
                'going_trip'=>$going_trip,
                'return_trip'=>$return_trip,
                'hotel'=>$hotel
            ];
        }catch(Exception $e)
        {
            return 1;
        }

        return $static_trip;
    }

    public function index()
    {
        $static_book=Booking::where('type','static')
                             ->AvailableRooms()
                             ->select('id','trip_name','price','number_of_people','trip_capacity','start_date','end_date','stars','trip_note')
                             ->get();
        return $static_book;

    }

}
