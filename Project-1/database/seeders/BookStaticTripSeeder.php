<?php

namespace Database\Seeders;

use App\Models\BookingRoom;
use App\Models\BookingStaticTrip;
use App\Models\StaticTripRoom;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookStaticTripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //1
        $book_static=BookingStaticTrip::create([
            'user_id'=>7,
            'static_trip_id'=>1,
            'number_of_friend'=>2,
            'book_price'=>460
        ]);
        $rooms=BookingRoom::where('book_id',1)->get();
        $rooms[0]->user_id=7;
        $rooms[0]->save();
        StaticTripRoom::create([
            'booking_static_trip_id'=>$book_static['id'],
            'room_id'=>21,
        ]);
        //2
        $book_static=BookingStaticTrip::create([
            'user_id'=>7,
            'static_trip_id'=>1,
            'number_of_friend'=>2,
            'book_price'=>460
        ]);

        $rooms[1]->user_id=7;
        StaticTripRoom::create([
            'booking_static_trip_id'=>$book_static['id'],
            'room_id'=>22,
        ]);
        $rooms[1]->save();
    }
}
