<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\BookPlace;
use App\Models\BookPlane;
use App\Models\Place;
use App\Models\PlaneTrip;
use App\Models\Room;
use Exception;

class BookRepository implements BookRepositoryInterface
{
    public function store_Admin($request)
    {
        // to check if there are an enough rooms in this hotel
        $room_count = $request['number_of_people'] / $request['trip_capacity'];
        if ($request['number_of_people'] % $request['trip_capacity'] > 0) $room_count++;
        $rooms = Room::available($request['start_date'], $request['end_date'])
            ->where('hotel_id', $request['hotel_id'])
            ->where('capacity', $request['trip_capacity'])
            ->count();
        if ($rooms < $room_count) {
           return 1;
        }

        $plane_trip = PlaneTrip::where('id', $request['plane_trip'])->first();
        if ($plane_trip['available_seats'] >= $request['number_of_people']) {
            $plane_trip['available_seats'] -= $request['number_of_people'];
            $plane_trip->save();
        } else {
            return 2;
        }

        $plane_trip_away = PlaneTrip::where('id', $request['plane_trip_away'])->first();
        if ($plane_trip_away['available_seats'] >= $request['number_of_people']) {
            $plane_trip_away['available_seats'] -= $request['number_of_people'];
            $plane_trip_away->save();
        } else {
            return 3;
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
                'type' => 'static',
            ]);
            foreach ($request['places'] as $place) {
                $book_place = BookPlace::create([
                    'book_id' => $booking->id,
                    'place_id' => $place,
                    'current_price' => Place::where('id', $place)->first()->place_price,
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
            // rooms
            $rooms = Room::available($request['start_date'], $request['end_date'])
                ->where('hotel_id', $request['hotel_id'])
                ->where('capacity', $request['trip_capacity'])
                ->get();
            for ($i = 0; $i < $room_count; $i++) {
                BookingRoom::create([
                    'book_id' => $booking->id,
                    'room_id' => $rooms[$i]['id'],
                    'current_price' => $rooms[$i]['price'],
                    'start_date' => $request['start_date'],
                    'end_date' => $request['end_date']
                ]);
            }
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
        }

         $static_book=Booking::with(['places:id,name,place_price,text','places.images:id,image',
                                        'plane_trips:id,airport_source_id,airport_destination_id,current_price,available_seats,flight_date,landing_date',
                                        'plane_trips.airport_source:id,name',
                                        'plane_trips.airport_destination:id,name',
                                    ])
                                    ->AvailableRooms()
                                    ->where('id',$booking->id)->get();
        return $static_book;
    }

    public function editAdmin($request,$id)
    {
        try{
            $booking= Booking::findOrFail($id);
            if(auth()->id() != $booking->user_id)
            {
                return response()->json([
                    'message'=>'You do not have the permission'
                ],200);
            }
        // to check if there are an enough rooms in this hotel
        $room_count = $request['number_of_people'] / $booking['trip_capacity'];
        if ($request['number_of_people'] % $booking['trip_capacity'] > 0) $room_count++;
        $rooms = Room::available($request['start_date'], $request['end_date'])
                    ->where('hotel_id', $request['hotel_id'])
                    ->where('capacity', $booking['trip_capacity'])
                    ->count();
        if ($rooms < $room_count) {
           return 1;
        }

        ##### delete old booking room
          $bookRoom=BookingRoom::where('book_id',$booking['id'])->delete();


        $plane_trip = PlaneTrip::where('id', $request['plane_trip'])->first();
        if ($plane_trip['available_seats'] >= $request['number_of_people']) {
            $plane_trip['available_seats'] -= $request['number_of_people'];
            $plane_trip->save();
        } else {
            return 2;
        }

        $plane_trip_away = PlaneTrip::where('id', $request['plane_trip_away'])->first();
        if ($plane_trip_away['available_seats'] >= $request['number_of_people']) {
            $plane_trip_away['available_seats'] -= $request['number_of_people'];
            $plane_trip_away->save();
        } else {
            return 3;
        }
        ##### update the seats in this trip
        $bookplane=$booking->plane_trips;
        $bookplane[0]['available_seats']+=$booking['number_of_people'];
        $bookplane[1]['available_seats']+=$booking['number_of_people'];
        $bookplane[0]->save();
        $bookplane[1]->save();
        BookPlane::where('book_id',$booking['id'])->delete();


        $booking->trip_name = $request['trip_name']?? $booking['trip_name'];
        $booking->price = $request['price']?? $booking['price'];
        $booking->number_of_people = $request['number_of_people']?? $booking['number_of_people'];
        $booking->start_date = $request['start_date']?? $booking['start_date'];
        $booking->end_date = $request['end_date']?? $booking['end_date'];
        $booking->trip_note = $request['trip_note']?? $booking['trip_note'];
        $booking->save();


        $book_place=BookPlace::where('book_id',$booking['id'])->delete();
        foreach ($request['places'] as $place) {
            $book_place = BookPlace::create([
                'book_id' => $booking->id,
                'place_id' => $place,
                'current_price' => Place::where('id', $place)->first()->place_price,
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

     // // rooms
        $rooms = Room::available($request['start_date'], $request['end_date'])
            ->where('hotel_id', $request['hotel_id'])
            ->where('capacity', $booking['trip_capacity'])
            ->get();
        for ($i = 0; $i < $room_count; $i++) {
            BookingRoom::create([
                'book_id' => $booking->id,
                'room_id' => $rooms[$i]['id'],
                'current_price' => $rooms[$i]['price'],
                'start_date' => $request['start_date'],
                'end_date' => $request['end_date']
            ]);
        }
    }catch(\Exception $e){
        return 4;
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
            $static_trip=Booking::where('type','static')
                                 ->with(['places:id,name,place_price,text','places.images:id,image',
                                 'plane_trips:id,airport_source_id,airport_destination_id,current_price,available_seats,flight_date,landing_date',
                                 'plane_trips.airport_source:id,name',
                                 'plane_trips.airport_destination:id,name',
                             ])
                             ->AvailableRooms()
                             ->where('id',$id)
                             ->get();
        }catch(Exception $e)
        {
            return 1;
        }

        return $static_trip;
    }
}