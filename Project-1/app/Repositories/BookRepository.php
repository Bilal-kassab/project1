<?php

namespace App\Repositories;

use App\Models\Activity;
use App\Models\ActivityBook;
use App\Models\Bank;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\BookingStaticTrip;
use App\Models\BookPlace;
use App\Models\BookPlane;
use App\Models\Country;
use App\Models\Place;
use App\Models\PlaneTrip;
use App\Models\Room;
use App\Models\StaticTripRoom;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Exception;

class BookRepository implements BookRepositoryInterface
{
    public function store_Admin($request)
    {
     try {

        $trip_price=0;
        $plane_trip = PlaneTrip::where('id', $request['plane_trip'])->first();
        $plane_trip_away = PlaneTrip::where('id', $request['plane_trip_away'])->first();

        // if($plane_trip['flight_date'] > $plane_trip_away['flight_date'])
        // {
        //     return 4;
        // }
        if ($plane_trip['available_seats'] < $request['number_of_people']) {
             return 2;
        }


        if ($plane_trip_away['available_seats'] < $request['number_of_people']) {
            return 3;
        }

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


        $plane_trip['available_seats'] -= $request['number_of_people'];
        $plane_trip->save();
        $plane_trip_away['available_seats'] -= $request['number_of_people'];
        $plane_trip_away->save();
            $booking = Booking::create([
                'user_id' => auth()->user()->id,
                'source_trip_id' => auth()->user()->position,
                'destination_trip_id' => $request['destination_trip_id'],
                'trip_name' => $request['trip_name'],
                //'price' => $request['price'],
                'number_of_people' => $request['number_of_people'],
                'trip_capacity' => $request['trip_capacity'],
                'start_date' => $request['start_date'],// to submit the flight date same as start date trip
                'end_date' => $request['end_date'],// to submit the flight date same as end date trip
                // 'start_date' => $plane_trip['flight_date'],// to submit the flight date same as start date trip
                // 'end_date' => $plane_trip_away['flight_date'],// to submit the flight date same as end date trip
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
                $interval = $datetime2->diff($datetime1);
                $days = $interval->format('%a');
                $trip_price+=($book_room['current_price']*$days);
                $trip_price-=($trip_price*$request['ratio']);// if there is an ratio from the price
                $booking['price']=$trip_price;
                $booking->save();
            // }
            $static_book=Booking::where('id',$booking->id)->first();

           return $this->showStaticTrip($static_book['id']);

        } catch (Exception $exception) {
             throw new Exception($exception->getMessage());
        }

    }

    public function editAdmin($request,$id)
    {
        try
        {
            $booking= Booking::findOrFail($id);
            $date=Carbon::now()->format('Y-m-d');
            $trip_date = Carbon::createFromFormat('Y-m-d', $booking['start_date']);
            if($date>=$trip_date){
                return 4;
            }
              // the old period
            $datetime1 = new DateTime($booking['start_date']);
            $datetime2 = new DateTime($booking['end_date']);
            $interval = $datetime1->diff($datetime2);
            $old_period = $interval->format('%a');
            // the new period
            $datetime3 = new DateTime($request['start_date']);
            $datetime4 = new DateTime($request['end_date']);
            $interval = $datetime3->diff($datetime4);
            $new_period = $interval->format('%a');
            //check if the new period a similar the ancient period
            if($old_period != $new_period){
                // return 'You should choose a period similar to the ancient period';
                return 5;
            }
            // $trip_price=0;
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
                return 6;
            }
            // show if there are available_seats to book in going trip
            $plane_trip = PlaneTrip::where('id', $request['plane_trip'])->first();
            if ($plane_trip['available_seats'] < $request['number_of_people']+$numberOfOldSeat) {
                 return 2;
            }
            // show if there are available_seats to book in return trip
            $plane_trip_away = PlaneTrip::where('id', $request['plane_trip_away'])->first();
            if ($plane_trip_away['available_seats'] < $request['number_of_people']+$numberOfOldSeat)###
            {
                return 3;
            }

            $bookRoom=BookingRoom::where('book_id',$booking['id'])->first();
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
            for ($i = 0; $i < $room_count+$bookRoomCount; $i++) {
                $book_room=BookingRoom::create([
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
            $booking->save();

        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
          return $this->showStaticTrip($booking->id);
    }

    public function tripCancellation($id)
    {
        try {
            $trip=Booking::findOrFail($id);
            $goingTrip=$trip->plane_trips[0]??null;
            $returnTrip=$trip->plane_trips[1]??null;

            $date=Carbon::now()->format('Y-m-d');
            $trip_date = Carbon::createFromFormat('Y-m-d', $trip['start_date']);
            if($date>=$trip_date){
                return trans('trip.start-trip');
            }
            $staticBooks=BookingStaticTrip::where('static_trip_id',$trip['id'])->get();
            // return the money to all users that booked in this trip
            $seats=0; ## For calculating the total number of seats on this trip, including those taken by passengers.
            foreach($staticBooks as $staticBook)
            {
                $seats+=$staticBook['number_of_friend'];
                $user=User::where('id',$staticBook['user_id'])->first();
                $userAccount=Bank::where('email',$user['email'])->first();
                $userAccount['money']+=$staticBook['book_price'];
                $userAccount['payments']-=$staticBook['book_price'];
                $userAccount->save();
            }
            // Return the seates to the plane trips again
            if($goingTrip){
                $goingTrip['available_seats']+=$trip['number_of_people']+$seats;
                $goingTrip->save();
            }
            if($returnTrip){
                $returnTrip['available_seats']+=$trip['number_of_people']+$seats;
                $returnTrip->save();
            }
            $trip->delete();
            return trans('trip.cancel-successfully');
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
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
                'new_price'=>$book['new_price'],
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
                'going_plane'=>[
                    'id'=>$book->plane_trips[0]->plane->id?? null,
                    'name'=>$book->plane_trips[0]->plane->name?? null,
                ]??null,
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
                'return_plane'=>[
                    'id'=>$book->plane_trips[1]->plane->id?? null,
                    'name'=>$book->plane_trips[1]->plane->name?? null,
                ]??null,
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
                            // ->select('id','trip_name','price','new_price','number_of_people','trip_capacity','start_date','end_date','stars','trip_note')
                            ->get();
        return $static_book;

    }

    public function checkStaticTrip($request,$id)
    {
        try{
            $static_trip=Booking::where('type','static')->findOrFail($id);
            //days
            $datetime1 = new DateTime($static_trip['start_date']);
            $datetime2 = new DateTime($static_trip['end_date']);
            $interval = $datetime1->diff($datetime2);
            $days = $interval->format('%a');
            $discount=1;
            if($static_trip['new_price']){
                $discount=$static_trip['new_price']/$static_trip['price'];
            }

            $rooms_needed=(int)($request['number_of_friend']/$static_trip['trip_capacity']);
            if($request['number_of_friend'] % $static_trip['trip_capacity'] >0) $rooms_needed++;
            $available_rooms=BookingRoom::where('book_id',$id)->where('user_id',null)->count();
            $room=BookingRoom::where('book_id',$id)->first();
            $plane_trip=$static_trip?->plane_trips;
            if($static_trip['number_of_people'] < $request['number_of_friend']){
                return 1;
            }
            if($available_rooms < $rooms_needed){
                return 2;
            }
            $goingPlaneTrip=$plane_trip[0]['current_price']*$request['number_of_friend']??0;
            $returnPlaneTrip=$plane_trip[1]['current_price']*$request['number_of_friend']??0;
            $total_price=0.0;
            $total_price+=(($static_trip['price']-($room['current_price']*$days))*$request['number_of_friend']);
            $placePrice=$total_price-$goingPlaneTrip-$returnPlaneTrip;
            $total_price+=$rooms_needed*$room['current_price']*$days;
            $price_after_discount=null;
            if(auth()->user()->point >= 50)#################
            {
                $price_after_discount=$total_price-($total_price*0.5);
            }

            $data=[
                'trip_id'=>(int)$id,
                'number_of_friend'=>(int)$request['number_of_friend'],
                'rooms_needed'=>$rooms_needed,
                'days'=>(int)$days,
                'room_price'=>(doubleval($room['current_price'])*$discount)??null,
                'ticket_price_for_going_trip'=>$goingPlaneTrip*$discount,
                'ticket_price_for_return_trip'=>$returnPlaneTrip*$discount,
                'ticket_price_for_places'=>$placePrice*$discount,
                'total_price'=>$total_price*$discount,
                'price_after_discount'=>$price_after_discount,
            ];
            return $data;
        }catch(Exception $exception){
            throw new Exception($exception->getMessage());
        }

    }

    public function bookStaticTrip($request)
    {
        try{
            $bank=Bank::where('email',auth()->user()->email)->first();
        if($bank['money']<$request['total_price'] && $bank['money']<$request['price_after_discount'])
        {
            return 1;
        }
        $user=User::where('id',auth()->id())->first();
        $book_price=$request['total_price'];###########
        if($request['discount']){
            $book_price=$request['price_after_discount'];
            $user['point']-=50;
        }
        $user['point']+=5;
        $user->save();
        $book_static=BookingStaticTrip::create([
            'user_id'=>auth()->id(),
            'static_trip_id'=>$request['trip_id'],
            'number_of_friend'=>$request['number_of_friend'],
            'book_price'=>$book_price
        ]);
        $bank['money']=$bank['money']-$book_price;
        $bank['payments']+=$book_price;
        $bank->save();
        $static_trip=Booking::where('type','static')->findOrFail($request['trip_id']);
        $static_trip['number_of_people']=$static_trip['number_of_people']-$request['number_of_friend'];
        $static_trip->save();

         $rooms=BookingRoom::where('book_id',$request['trip_id'])->where('user_id',null)->get();
        for($i=0;$i<$request['rooms_needed'];$i++)
        {
            StaticTripRoom::create([
                'booking_static_trip_id'=>$book_static['id'],
                'room_id'=>$rooms[$i]['room_id'],
            ]);
            $rooms[$i]->user_id=auth()->id();
            $rooms[$i]->save();
        }
        return 2;

        }catch(Exception $exception){
            throw new Exception($exception->getMessage());
        }
    }

    public function editBook($request,$id)
    {
        try{
            // $data['number_of_friend']=$request['new_number_of_friend'];
            $static_book=BookingStaticTrip::findOrFail($id);
            $book=Booking::findOrFail($static_book['static_trip_id']);
            $date=Carbon::now()->format('Y-m-d');
            $trip_date = Carbon::createFromFormat('Y-m-d', $book['start_date']);
            if($date>=$trip_date){
                return 10;
            }
            $val=$this->checkStaticTrip($request,$static_book['static_trip_id']);
            if($val==1 || $val==2){
                return $val;
            }
            $bank=Bank::where('email',auth()->user()->email)->first();
            if($bank['money']<$val['total_price'] && $bank['money']<$val['price_after_discount'])
            {
                return 3;
            }
            $book_price=$val['total_price'];###########
            if($request['discount']){
                $book_price=$val['price_after_discount'];
                $user=User::where('id',auth()->id())->first();
                $user['point']-=50;
                $user->save();
            }
            $static_book['number_of_friend']+=$request['number_of_friend'];
            $static_book['book_price']+=$book_price;
            $static_book->save();

            $bank['money']=$bank['money']-$book_price;
            $bank['payments']+=$book_price;
            $bank->save();

            $trip=Booking::where('type','static')->findOrFail($static_book['static_trip_id']);
            $trip['number_of_people']-=$request['number_of_friend'];
            $trip->save();

            $rooms=BookingRoom::where('book_id',$static_book['static_trip_id'])->where('user_id',null)->get();
            for($i=0;$i<$val['rooms_needed'];$i++)
            {
                StaticTripRoom::create([
                    'booking_static_trip_id'=>$static_book['id'],
                    'room_id'=>$rooms[$i]['room_id'],
                ]);
                $rooms[$i]->user_id=auth()->id();
                $rooms[$i]->save();
            }

            return 4;
        }catch(Exception $exception){
            throw new Exception($exception->getMessage());
        }
    }

    public function deleteBook($id)
    {
        try {
            $static_book=BookingStaticTrip::findOrFail($id);
            $book=Booking::findOrFail($static_book['static_trip_id']);
            $date=Carbon::now()->format('Y-m-d');
            $trip_date = Carbon::createFromFormat('Y-m-d', $book['start_date']);
            if($date>=$trip_date){
                return 1;
            }
            $book['number_of_people']+=$static_book['number_of_friend'];
            $static_book_rooms=$static_book->rooms;
            foreach($static_book_rooms as $room){
                $myRoom=BookingRoom::where([
                    ['room_id',$room['id']],
                    ['book_id',$static_book['static_trip_id']]
                    ])->first();
                $myRoom->user_id=null;
                $myRoom->save();
            }
             $book->save();
             $static_book->delete();
             $bank=Bank::where('email',auth()->user()->email)->first();
             $bank['money']+=$static_book['book_price'];
             $bank['payments']-=$static_book['book_price'];
             $bank->save();
             $user=User::where('id',auth()->id())->first();
             $user['point']-=5;
             $user->save();
            return 2;
        } catch (Exception $th) {
           return 3;
        }
    }

    public function getDetailsStaticTrip($id)
    {
        try{

            $staticTrip=Booking::where('id',$id)->with('user')->first();
            $details=BookingStaticTrip::where('static_trip_id',$staticTrip['id'])
                                        ->with('user:id,name,phone_number,image')
                                        ->select('id','user_id','static_trip_id','number_of_friend')
                                        ->get();
            return [
              'trip_admin'=>$staticTrip['user'],
              'details'=>$details,
            ];

        }catch(Exception $ex){
            throw new Exception($ex);
        }

    }

    public function getTripAdminTrips()
    {
        try{
            $staticTrip=Booking::where('type','static')
                             ->where('user_id',auth()->id())
                             ->AvailableRooms()
                            //  ->select('id','trip_name','price','new_price','number_of_people','trip_capacity','start_date','end_date','stars','trip_note')
                             ->get();
            return $staticTrip;
        }catch(Exception $exception){
            throw new Exception($exception->getMessage());
        }
    }

    public function getTripAdminTripDetails($id)
    {
        try{
            $staticTrip=Booking::where('id',$id)
                                ->where('user_id',auth()->id())
                                ->first();
            $details=[];
            if($staticTrip)
            {
                $details=BookingStaticTrip::where('static_trip_id',$staticTrip['id'])
                                            ->with('user:id,name,phone_number,image')
                                            ->select('id','user_id','static_trip_id','number_of_friend')
                                            ->get();
            }
            return $details;
        }catch(Exception $ex){
            throw new Exception($ex);
        }
    }

    public function searchTrip($request)
    {
        try{
            $bookings='';
            //  Activity
            if($request['type']=='activity'){
                $activityIds=Activity::where('name','like','%'.$request['search_variable'].'%')->pluck('id')->toArray();
                $bookings = Booking::whereHas('activities', function ($query) use ($activityIds) {
                    $query->whereIn('activities.id', $activityIds);
                })->get();
            }
            // Country
            if($request['type']=='country'){
                $countryIds=Country::where('name','like','%'.$request['search_variable'].'%')->pluck('id')->toArray();
                $bookings = Booking::whereIn('destination_trip_id',$countryIds)->get();
            }
            // Place
            if($request['type']=='place'){
                $placeIds=Place::where('name','like','%'.$request['search_variable'].'%')->pluck('id')->toArray();
                $bookings = Booking::whereHas('places', function ($query) use ($placeIds) {
                    $query->whereIn('places.id', $placeIds);
                })->get();
            }

            // Date
            if($request['type']=='date'){
                $bookings = Booking::where('start_date','>=',$request['first_date'])
                                    ->where('end_date','<=',$request['second_date'])
                                    ->get();
            }
            return $bookings;

        }catch(Exception $ex){
            throw new Exception($ex);
        }
    }
}
