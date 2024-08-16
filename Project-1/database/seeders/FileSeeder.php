<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Airport;
use App\Models\Area;
use App\Models\Bank;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\BookingStaticTrip;
use App\Models\BookPlace;
use App\Models\BookPlane;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Country;
use App\Models\Favorite;
use App\Models\Hotel;
use App\Models\HotelImage;
use App\Models\Place;
use App\Models\PlaceCategory;
use App\Models\PlaceImage;
use App\Models\Plane;
use App\Models\PlaneTrip;
use App\Models\Room;
use App\Models\StaticTripRoom;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // #JSOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOON
        $records_countries=Storage::json('/public/countries.json');
        foreach($records_countries as $record){
            Country::create([
                'name'=>$record['name']
            ]);
        }
        $records_area=Storage::json('/public/areas.json');
        foreach($records_area as $record){
            Area::create([
                'name'=>$record['name'],
                'country_id'=>$record['country_id']
            ]);
        }
        $records_area=Storage::json('/public/places.json');
        foreach($records_area as $record){
            Place::create([
                'name'=>$record['name'],
                'place_price'=>$record['place_price'],
                'area_id'=>$record['area_id'],
                'lat'=>$record['lat'],
                'long'=>$record['long'],
            ]);
        }
        $records_area=Storage::json('/public/Activity.json');
        foreach($records_area as $record){
            Activity::create([
                'name'=>$record['name'],
            ]);
        }
        $records_area=Storage::json('/public/category.json');
        foreach($records_area as $record){
            Category::create([
                'name'=>$record['name'],
            ]);
        }
        $records_area=Storage::json('/public/place_categories.json');
        foreach($records_area as $record){
            PlaceCategory::create([
                'category_id'=>$record['category_id'],
                'place_id'=>$record['place_id'],
            ]);
        }
        $records=Storage::json('/public/SA.json');
        foreach($records as $record){
            $user=User::create([
            'name'=>$record['name'],
            'email'=>$record['email'],
            'phone_number'=>$record['phone_number'],
            'password'=>bcrypt(str($record['password'])),
            'is_approved'=>$record['is_approved'],
            'position'=>$record['position'],
            ]);
            $user->assignRole('Super Admin');
            $user->givePermissionTo('unbanned');;
        }
        $records=Storage::json('/public/users.json');
        foreach($records as $record){
            $user=User::create([
            'name'=>$record['name'],
            'email'=>$record['email'],
            'phone_number'=>$record['phone_number'],
            'email_verified_at'=>$record['email_verified_at'],
            'password'=>bcrypt(str($record['password'])),
            'image'=>$record['image'],
            'point'=>$record['point'],
            'is_approved'=>$record['is_approved'],
            'position'=>$record['position'],
            ]);
            $user->assignRole('User');
            $user->givePermissionTo('unbanned');;
        }
        $records=Storage::json('/public/hotel_admins.json');
        foreach($records as $record){
            $user=User::create([
            'name'=>$record['name'],
            'email'=>$record['email'],
            'phone_number'=>$record['phone_number'],
            'password'=>bcrypt(str($record['password'])),
            'is_approved'=>$record['is_approved'],
            'position'=>$record['position'],
            ]);
            $user->assignRole('Hotel admin');
            $user->givePermissionTo('unbanned');;
        }
        $records=Storage::json('/public/Airport_admins.json');
        foreach($records as $record){
            $user=User::create([
            'name'=>$record['name'],
            'email'=>$record['email'],
            'phone_number'=>$record['phone_number'],
            'password'=>bcrypt(str($record['password'])),
            'is_approved'=>$record['is_approved'],
            'position'=>$record['position'],
            ]);
            $user->assignRole('Airport admin');
            $user->givePermissionTo('unbanned');;
        }
        $records=Storage::json('/public/Trip_admins.json');
        foreach($records as $record){
            $user=User::create([
            'name'=>$record['name'],
            'email'=>$record['email'],
            'phone_number'=>$record['phone_number'],
            'password'=>bcrypt(str($record['password'])),
            'is_approved'=>$record['is_approved'],
            'position'=>$record['position'],
            ]);
            $user->assignRole('Trip manger');
            $user->givePermissionTo('unbanned');;
        }
        $records=Storage::json('/public/User2.json');
        foreach($records as $record){
            $user=User::create([
            'name'=>$record['name'],
            'email'=>$record['email'],
            'phone_number'=>$record['phone_number'],
            'email_verified_at'=>$record['email_verified_at'],
            'password'=>bcrypt(str($record['password'])),
            'image'=>$record['image'],
            'point'=>$record['point'],
            'is_approved'=>$record['is_approved'],
            'position'=>$record['position'],
            ]);
            $user->assignRole('User');
            $user->givePermissionTo('unbanned');;

        }
        $records_area=Storage::json('/public/Banks.json');
        foreach($records_area as $record){
            Bank::create([
                'email'=>$record['email'],
                'money'=>$record['money'],
                'payments'=>$record['payments'],
            ]);
        }
        $records_area=Storage::json('/public/place_image.json');
        foreach($records_area as $record){
            PlaceImage::create([
                'place_id'=>$record['place_id'],
                'image'=>$record['image'],
            ]);
        }
        $records_area=Storage::json('/public/hotels.json');
        foreach($records_area as $record){
            Hotel::create([
                'name'=>$record['name'],
                'user_id'=>$record['user_id'],
                'country_id'=>$record['country_id'],
                'area_id'=>$record['area_id'],
                'number_rooms'=>$record['number_rooms'],
                'stars'=>$record['stars'],
                'visible'=>$record['visible'],
            ]);
        }
        $records_area=Storage::json('/public/Hotel_image.json');
        foreach($records_area as $record){
            HotelImage::create([
                'hotel_id'=>$record['hotel_id'],
                'image'=>$record['image'],
            ]);
        }
        $records_area=Storage::json('/public/Rooms.json');
        for($i =1 ; $i<50 ; $i++){
        foreach($records_area as $record){
                Room::create([
                    'hotel_id'=>$i,
                    'capacity'=>$record['capacity'],
                    'status'=>$record['status'],
                    'price'=>$record['price'],
                ]);
            }
        }
        $records_area=Storage::json('/public/Airport.json');
        foreach($records_area as $record){
            Airport::create([
                'name'=>$record['name'],
                'user_id'=>$record['user_id'],
                'country_id'=>$record['country_id'],
                'area_id'=>$record['area_id'],
                'visible'=>$record['visible'],
            ]);
        }
        $records_area=Storage::json('/public/Planes.json');
        foreach($records_area as $record){
            Plane::create([
                'name'=>$record['name'],
                'number_of_seats'=>$record['number_of_seats'],
                'airport_id'=>$record['airport_id'],
                'ticket_price'=>$record['ticket_price'],
                'visible'=>$record['visible'],
            ]);
        }
        $records_area=Storage::json('/public/PlaneTrips.json');
        foreach($records_area as $record){
            PlaneTrip::create([
                'plane_id'=>$record['plane_id'],
                'airport_source_id'=>$record['airport_source_id'],
                'airport_destination_id'=>$record['airport_destination_id'],
                'country_source_id'=>$record['country_source_id'],
                'country_destination_id'=>$record['country_destination_id'],
                'current_price'=>$record['current_price'],
                'available_seats'=>$record['available_seats'],
                'flight_duration'=>$record['flight_duration'],
                'flight_date'=>$record['flight_date'],
                'landing_date'=>$record['landing_date'],
            ]);
        }
        $records=Storage::json('/public/Static_trip.json');
        foreach($records as $record){
            Booking::create([
                'user_id'=>$record['user_id'],
                'source_trip_id'=>$record['source_trip_id'],
                'destination_trip_id'=>$record['destination_trip_id'],
                'trip_name'=>$record['trip_name'],
                'price'=>$record['price'],
                'number_of_people'=>$record['number_of_people'],
                'trip_capacity'=>$record['trip_capacity'],
                'start_date'=>$record['start_date'],
                'end_date'=>$record['end_date'],
                'trip_note'=>$record['trip_note'],
                'type'=>$record['type'],
            ]);
        }
        $records=Storage::json('/public/Dynamic_booking.json');
        foreach($records as $record){
            Booking::create([
                'user_id'=>$record['user_id'],
                'source_trip_id'=>$record['source_trip_id'],
                'destination_trip_id'=>$record['destination_trip_id'],
                'trip_name'=>$record['trip_name'],
                'price'=>$record['price'],
                'number_of_people'=>$record['number_of_people'],
                'start_date'=>$record['start_date'],
                'end_date'=>$record['end_date'],
                'trip_note'=>$record['trip_note'],
                'type'=>$record['type'],
            ]);
        }
        $records=Storage::json('/public/Hotel_booking.json');
        foreach($records as $record){
            Booking::create([
                'user_id'=>$record['user_id'],
                'source_trip_id'=>$record['source_trip_id'],
                'destination_trip_id'=>$record['destination_trip_id'],
                'trip_name'=>$record['trip_name'],
                'price'=>$record['price'],
                'number_of_people'=>$record['number_of_people'],
                'start_date'=>$record['start_date'],
                'end_date'=>$record['end_date'],
                'trip_note'=>$record['trip_note'],
                'type'=>$record['type'],
            ]);
        }
        $records=Storage::json('/public/Plane_booking.json');
        foreach($records as $record){
            Booking::create([
                'user_id'=>$record['user_id'],
                'source_trip_id'=>$record['source_trip_id'],
                'destination_trip_id'=>$record['destination_trip_id'],
                'trip_name'=>$record['trip_name'],
                'price'=>$record['price'],
                'number_of_people'=>$record['number_of_people'],
                'start_date'=>$record['start_date'],
                'end_date'=>$record['end_date'],
                'trip_note'=>$record['trip_note'],
                'type'=>$record['type'],
            ]);
        }
        $records=Storage::json('/public/Book_plane.json');
        foreach($records as $record){
            BookPlane::create([
                'book_id'=>$record['book_id'],
                'plane_trip_id'=>$record['plane_trip_id'],
            ]);
        }
        $records=Storage::json('/public/Book_room.json');
        foreach($records as $record){
            BookingRoom::create([
                'book_id'=>$record['book_id'],
                'user_id'=>$record['user_id'],
                'room_id'=>$record['room_id'],
                'current_price'=>$record['current_price'],
                'start_date'=>$record['start_date'],
                'end_date'=>$record['end_date'],
            ]);
        }
        $records=Storage::json('/public/Booking_static_trip.json');
        foreach($records as $record){
            BookingStaticTrip::create([
                'static_trip_id'=>$record['static_trip_id'],
                'user_id'=>$record['user_id'],
                'number_of_friend'=>$record['number_of_friend'],
                'book_price'=>$record['book_price'],
                'evaluation'=>$record['evaluation'],
            ]);
        }
        $records=Storage::json('/public/Book_place.json');
        foreach($records as $record){
            BookPlace::create([
                'book_id'=>$record['book_id'],
                'place_id'=>$record['place_id'],
                'current_price'=>$record['current_price'],
                'place_note'=>$record['place_note'],
            ]);
        }
        $records=Storage::json('/public/static_trip_rooms.json');
        foreach($records as $record){
            StaticTripRoom::create([
                'booking_static_trip_id'=>$record['booking_static_trip_id'],
                'room_id'=>$record['room_id'],
            ]);
        }
        $records=Storage::json('/public/comment.json');
        foreach($records as $record){
            Comment::create([
                'place_id'=>$record['place_id'],
                'user_id'=>$record['user_id'],
                'comment'=>$record['comment'],
            ]);
        }
        $records=Storage::json('/public/favorite.json');
        foreach($records as $record){
            Favorite::create([
                'place_id'=>$record['place_id'],
                'user_id'=>$record['user_id']
            ]);
        }
    }
}
