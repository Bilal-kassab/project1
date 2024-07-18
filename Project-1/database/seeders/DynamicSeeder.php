<?php

namespace Database\Seeders;

use App\Models\ActivityBook;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\BookPlace;
use App\Models\BookPlane;
use App\Models\Place;
use App\Models\PlaneTrip;
use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DynamicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plane_trip=PlaneTrip::where('id',1)->first();
        $plane_trip_away=PlaneTrip::where('id',3)->first();
        $booking = Booking::create([
            'user_id' => 7,
            'source_trip_id' => 1,
            'destination_trip_id' =>2,
            'trip_name' => 'trip to France',
            'number_of_people' => 5,
            'trip_capacity' => null,
            "price"=>1350,
            'start_date' =>$plane_trip['flight_date'],
            'end_date' => $plane_trip_away['flight_date'],
            'trip_note' => 'HI My friend',
            'type' => 'dynamic',
        ]);

        ActivityBook::create([
            'booking_id' => $booking->id,
            'activity_id' => 1,
        ]);
        ActivityBook::create([
            'booking_id' => $booking->id,
            'activity_id' => 2,
        ]);
        $book_place = BookPlace::create([
            'book_id' => $booking->id,
            'place_id' => 1,
            'current_price' =>Place::where('id', 1)->first()->place_price,
        ]);

        $book_place = BookPlace::create([
            'book_id' => $booking->id,
            'place_id' => 2,
            'current_price' => Place::where('id', 2)->first()->place_price,
        ]);


        $book_plane = BookPlane::create([
            'book_id' => $booking->id,
            'plane_trip_id' => $plane_trip->id,
        ]);
        $book_plane_away = BookPlane::create([
            'book_id' => $booking->id,
            'plane_trip_id' => $plane_trip_away->id,
        ]);

        $rooms = Room::available($plane_trip['flight_date'], $plane_trip_away['flight_date'])
                                ->where('hotel_id', 1)
                                ->where('capacity', 2)
                                ->get();
                for ($i = 0; $i < 2; $i++) {
                    $book_room=BookingRoom::create([
                        'book_id' => $booking->id,
                        'room_id' => $rooms[$i]['id'],
                        'current_price' => $rooms[$i]['price'],
                        'start_date' => $booking['start_date'],
                        'end_date' => $booking['end_date']
                    ]);
                }
        $rooms = Room::available($plane_trip['flight_date'], $plane_trip_away['flight_date'])
                                ->where('hotel_id', 1)
                                ->where('capacity', 4)
                                ->get();
                for ($i = 0; $i < 2; $i++) {
                    $book_room=BookingRoom::create([
                        'book_id' => $booking->id,
                        'room_id' => $rooms[$i]['id'],
                        'current_price' => $rooms[$i]['price'],
                        'start_date' => $booking['start_date'],
                        'end_date' => $booking['end_date']
                    ]);
                }
        $rooms = Room::available($plane_trip['flight_date'], $plane_trip_away['flight_date'])
                                ->where('hotel_id', 1)
                                ->where('capacity', 6)
                                ->get();
                for ($i = 0; $i < 2; $i++) {
                    $book_room=BookingRoom::create([
                        'book_id' => $booking->id,
                        'room_id' => $rooms[$i]['id'],
                        'current_price' => $rooms[$i]['price'],
                        'start_date' => $booking['start_date'],
                        'end_date' => $booking['end_date']
                    ]);
                }

    }
}
