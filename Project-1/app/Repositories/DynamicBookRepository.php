<?php

namespace App\Repositories;     
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\BookPlace;
use App\Models\BookPlane;
use App\Models\Place;
use App\Models\PlaneTrip;
use App\Models\Room;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\DynamicBookRepositoryInterface;
use Exception;

class DynamicBookRepository implements DynamicBookRepositoryInterface
{
    public function hotel_book($request){
        if($request['hotel_id'] != null)
        {
            $rooms_2 = Room::available($request['start_date'], $request['end_date'])
                ->where('hotel_id', $request['hotel_id'])
                ->where('capacity', 2)
                ->count();
                if ($request['count_room_C2'] > $rooms_2) {
                    return 5;
                }
                $rooms_4 = Room::available($request['start_date'], $request['end_date'])
                ->where('hotel_id', $request['hotel_id'])
                ->where('capacity', 4)
                ->count();
                if ($request['count_room_C4'] > $rooms_4) {
                    return 6;
                }
                $rooms_6 = Room::available($request['start_date'], $request['end_date'])
                ->where('hotel_id', $request['hotel_id'])
                ->where('capacity', 6)
                ->count();
                if ($request['count_room_C6'] > $rooms_6) {
                    return 7;
                }
        }
        try {
            $booking = Booking::create([
                'user_id' => auth()->user()->id,
                'source_trip_id' => $request['source_trip_id'],
                'destination_trip_id' => $request['destination_trip_id'],
                'trip_name' => $request['trip_name'],
                'price' => $request['price'],
                'number_of_people' => $request['number_of_people'],
                'trip_capacity' => $request['trip_capacity'],
                'start_date' => $request['start_date'],
                'end_date' => $request['end_date'],
                'trip_note' => $request['trip_note'],
                'type' => 'dynamic',
            ]);
            if($request['hotel_id'] != null)
            {   // rooms
                            if($request['count_room_C2']!=null)
                            {
                            $rooms = Room::available($request['start_date'], $request['end_date'])
                            ->where('hotel_id', $request['hotel_id'])
                            ->where('capacity', 2)
                            ->get();
                            for ($i = 0; $i < $request['count_room_C2']; $i++) {
                                BookingRoom::create([
                                    'book_id' => $booking->id,
                                    'room_id' => $rooms[$i]['id'],
                                    'current_price' => $rooms[$i]['price'],
                                    'start_date' => $request['start_date'],
                                    'end_date' => $request['end_date']
                                ]);
                            }
                            }
                            if($request['count_room_C4']!=null)
                            {
                            $rooms = Room::available($request['start_date'], $request['end_date'])
                            ->where('hotel_id', $request['hotel_id'])
                            ->where('capacity', 4)
                            ->get();
                            for ($i = 0; $i < $request['count_room_C4']; $i++) {
                                BookingRoom::create([
                                    'book_id' => $booking->id,
                                    'room_id' => $rooms[$i]['id'],
                                    'current_price' => $rooms[$i]['price'],
                                    'start_date' => $request['start_date'],
                                    'end_date' => $request['end_date']
                                ]);
                            }
                            }
                            if($request['count_room_C6']!=null)
                            {
                            $rooms = Room::available($request['start_date'], $request['end_date'])
                            ->where('hotel_id', $request['hotel_id'])
                            ->where('capacity', 6)
                            ->get();
                            for ($i = 0; $i < $request['count_room_C6']; $i++) {
                                BookingRoom::create([
                                    'book_id' => $booking->id,
                                    'room_id' => $rooms[$i]['id'],
                                    'current_price' => $rooms[$i]['price'],
                                    'start_date' => $request['start_date'],
                                    'end_date' => $request['end_date']
                                ]);
                            }
                            }
            }
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
    }
    $dynamic_trip=Booking::with(['places:id,name,place_price,text','places.images:id,image',
    'plane_trips:id,airport_source_id,airport_destination_id,current_price,available_seats,flight_date,landing_date',
    'plane_trips.airport_source:id,name',
    'plane_trips.airport_destination:id,name',
                                ])
                                ->AvailableRooms()
                                ->where('id',$booking->id)->get();
     return $dynamic_trip;
    }
        
    public function store_User($request){
        if($request['hotel_id'] != null)
        {
            $rooms_2 = Room::available($request['start_date'], $request['end_date'])
                ->where('hotel_id', $request['hotel_id'])
                ->where('capacity', 2)
                ->count();
                if ($request['count_room_C2'] > $rooms_2) {
                    return 5;
                }
                $rooms_4 = Room::available($request['start_date'], $request['end_date'])
                ->where('hotel_id', $request['hotel_id'])
                ->where('capacity', 4)
                ->count();
                if ($request['count_room_C4'] > $rooms_4) {
                    return 6;
                }
                $rooms_6 = Room::available($request['start_date'], $request['end_date'])
                ->where('hotel_id', $request['hotel_id'])
                ->where('capacity', 6)
                ->count();
                if ($request['count_room_C6'] > $rooms_6) {
                    return 7;
                }
            //////////////////////////////////////////////

            // $room_count = $request['number_of_people'] / $request['trip_capacity'];
            // if ($request['number_of_people'] % $request['trip_capacity'] > 0) $room_count++;
            // $rooms = Room::available($request['start_date'], $request['end_date'])
            //     ->where('hotel_id', $request['hotel_id'])
            //     ->where('capacity', $request['trip_capacity'])
            //     ->count();
            // if ($rooms < $room_count) {
            //    return 1;
            // }
        }
        if($request['plane_trip_id'] != null){
            $plane_trip = PlaneTrip::where('id', $request['plane_trip_id'])->first();
            if ($plane_trip['available_seats'] < $request['number_of_people']) {
                return 2;
            }
        }
        if($request['plane_trip_away_id'] != null){
            $plane_trip_away = PlaneTrip::where('id', $request['plane_trip_away_id'])->first();
            if ($plane_trip_away['available_seats'] < $request['number_of_people']) {
                return 3;
            }
        }
        if($request['plane_trip_id'] != null){
            $plane_trip = PlaneTrip::where('id', $request['plane_trip_id'])->first();
            $plane_trip['available_seats'] -= $request['number_of_people'];
            $plane_trip->save();
        }
        if($request['plane_trip_away_id'] != null){
            $plane_trip_away = PlaneTrip::where('id', $request['plane_trip_away_id'])->first();
            $plane_trip_away['available_seats'] -= $request['number_of_people'];
            $plane_trip_away->save();
        }
      
      
        try {
            $booking = Booking::create([
                'user_id' => auth()->user()->id,
                'source_trip_id' => $request['source_trip_id'],
                'destination_trip_id' => $request['destination_trip_id'],
                'trip_name' => $request['trip_name'],
                'price' => $request['price'],
                'number_of_people' => $request['number_of_people'],
                'trip_capacity' => $request['trip_capacity'],
                'start_date' => $request['start_date'],
                'end_date' => $request['end_date'],
                'trip_note' => $request['trip_note'],
                'type' => 'dynamic',
            ]);
            if($request['place_ids'] !=null)
            foreach ($request['place_ids'] as $place) {
                $book_place = BookPlace::create([
                    'book_id' => $booking->id,
                    'place_id' => $place,
                    'current_price' => Place::where('id', $place)->first()->place_price,
                ]);
            }

            // go away
            if($request['plane_trip_id'] !=null)
            $book_plane = BookPlane::create([
                'book_id' => $booking->id,
                'plane_trip_id' => $request['plane_trip_id'],
            ]);

            // back away
            if($request['plane_trip_away_id'] !=null)
            $book_plane_away = BookPlane::create([
                'book_id' => $booking->id,
                'plane_trip_id' => $request['plane_trip_away_id'],
            ]);
            if($request['hotel_id'] != null)
            {   // rooms
                            if($request['count_room_C2']!=null)
                            {
                            $rooms = Room::available($request['start_date'], $request['end_date'])
                            ->where('hotel_id', $request['hotel_id'])
                            ->where('capacity', 2)
                            ->get();
                            for ($i = 0; $i < $request['count_room_C2']; $i++) {
                                BookingRoom::create([
                                    'book_id' => $booking->id,
                                    'room_id' => $rooms[$i]['id'],
                                    'current_price' => $rooms[$i]['price'],
                                    'start_date' => $request['start_date'],
                                    'end_date' => $request['end_date']
                                ]);
                            }
                            }
                            if($request['count_room_C4']!=null)
                            {
                            $rooms = Room::available($request['start_date'], $request['end_date'])
                            ->where('hotel_id', $request['hotel_id'])
                            ->where('capacity', 4)
                            ->get();
                            for ($i = 0; $i < $request['count_room_C4']; $i++) {
                                BookingRoom::create([
                                    'book_id' => $booking->id,
                                    'room_id' => $rooms[$i]['id'],
                                    'current_price' => $rooms[$i]['price'],
                                    'start_date' => $request['start_date'],
                                    'end_date' => $request['end_date']
                                ]);
                            }
                            }
                            if($request['count_room_C6']!=null)
                            {
                            $rooms = Room::available($request['start_date'], $request['end_date'])
                            ->where('hotel_id', $request['hotel_id'])
                            ->where('capacity', 6)
                            ->get();
                            for ($i = 0; $i < $request['count_room_C6']; $i++) {
                                BookingRoom::create([
                                    'book_id' => $booking->id,
                                    'room_id' => $rooms[$i]['id'],
                                    'current_price' => $rooms[$i]['price'],
                                    'start_date' => $request['start_date'],
                                    'end_date' => $request['end_date']
                                ]);
                            }
                            }
            }
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
    }
    $dynamic_trip=Booking::with(['places:id,name,place_price,text','places.images:id,image',
    'plane_trips:id,airport_source_id,airport_destination_id,current_price,available_seats,flight_date,landing_date',
    'plane_trips.airport_source:id,name',
    'plane_trips.airport_destination:id,name',
                                ])
                                ->AvailableRooms()
                                ->where('id',$booking->id)->get();
     return $dynamic_trip;
    }


}
