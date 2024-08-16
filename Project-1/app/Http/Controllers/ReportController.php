<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Country;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function topPlaces(Request $request)
    {
            $year = $request->year;
            $month = $request->month;

            $topPlaces = Place::withCount(['bookings' => function($query) use ($year, $month) {
                                $query->when($year, function($q) use ($year) {
                                    return $q->whereYear('start_date', $year);
                                })
                                ->when($month, function($q) use ($month) {
                                    return $q->whereMonth('start_date', $month);
                                });
                            }])
                            ->whereHas('bookings', function($query) use ($year, $month) {
                                $query->when($year, function($q) use ($year) {
                                    return $q->whereYear('start_date', $year);
                                })
                                ->when($month, function($q) use ($month) {
                                    return $q->whereMonth('start_date', $month);
                                });
                            })
                            ->orderBy('bookings_count', 'desc')
                            ->take(10)
                            ->get();
        return response()->json([
            'data'=>$topPlaces
        ],200);
    }

    public function getTheProfits(Request $request)
    {
        // $request->validate([
        //     'year'=>'required',
        // ]);
        $year=$request->year??2024;
        $arr=[];
        for($i=1;$i<=12;$i++){
            $price=0;
            $bookings=Booking::whereyear('start_date',$year)->whereMonth('start_date',"0$i")->where('type','static')
                            ->with('bookings')
                            ->get();
            $bookings2=Booking::whereyear('start_date',$year)->whereMonth('start_date',"0$i")->where('type','!=','static')
                            ->with('bookings')
                            ->get();

            foreach($bookings as $booking){
                $price+=$booking?->totalBookPrice()??0;
            }
            foreach($bookings2 as $booking2){
                $price+=$booking2->price;
            }
            $arr[]=[
                'month'=>$i,
                'price'=>$price
            ];
        }
        return response()->json([
            'data'=>$arr
        ],200);
    }

    public function getUsersWithTheMostBookings()
    {
        $mostBookingUsers = User::withCount('bookings')->withCount('myStaticTrip')
                                    ->whereHas('roles', function($query) {
                                        $query->where('name', 'User');
                                    })
                                    ->with('position')
                                    ->whereHas('bookings')->orWhereHas('myStaticTrip')
                                    ->take(10)
                                    ->orderByRaw('(bookings_count + my_static_trip_count) DESC')
                                    ->get();
        $data=[];
        foreach($mostBookingUsers as $mostBookingUser){
            $data[]=[
                'id'=>$mostBookingUser->id,
                'name'=>$mostBookingUser->name,
                'email'=>$mostBookingUser->email,
                'phone_number'=>$mostBookingUser->phone_number,
                'image'=>$mostBookingUser->image,
                'point'=>$mostBookingUser->point,
                'position'=>$mostBookingUser->position,
                'number_of_trips'=>$mostBookingUser->bookings_count+$mostBookingUser->my_static_trip_count,
            ];
        }
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function getCountriesWithTheMostRegistered(Request $request)
    {
        $year=$request->year??2024;
        $topRegisteredCountry=Country::withCount(['users' => function($query) use ($year) {
                                $query->when($year, function($q) use ($year) {
                                    return $q->whereYear('created_at', $year);
                                });
                            }])
                            ->whereHas('users',function($query) use ($year) {
                                $query->when($year, function($q) use ($year) {
                                    return $q->whereYear('created_at', $year);
                                });
                            })
                            ->orderBy('users_count', 'desc')
                            ->take(10)
                            ->get();
        return response()->json([
            'data'=>$topRegisteredCountry
        ],200);
    }
    public function getTheMostVisitedCountries (Request $request)
    {
        $year=$request->year??2024;
        $topVisitedCountries=Country::withCount(['destination_bookings'=> function($query) use ($year) {
                                $query->when($year, function($q) use ($year) {
                                    return $q->whereYear('created_at', $year);
                                });
                            }])
                            ->whereHas('destination_bookings', function($query) use ($year) {
                                $query->when($year, function($q) use ($year) {
                                    return $q->whereYear('created_at', $year);
                                });
                            })
                            ->orderBy('destination_bookings_count', 'desc')
                            ->take(10)
                            ->get();
        return response()->json([
            'data'=>$topVisitedCountries
        ],200);
    }
    public function getTheNumberOfTripsAndTheirProfit(Request $request)
    {
        // $request->validate([
        //     'year'=>'required',
        // ]);
        $year=$request->year??'2024';
        $month=$request->month;
                  $bookings=Booking::query()->whereyear('start_date',$year)
                        ->when($month,function($q) use ($month){
                            return $q->whereMonth('start_date',"0$month");
                        })->count();
        return response()->json([
            'data'=>$bookings
        ],200);
    }

    public function getTheTopVisitedPlaces()
    {
            $topPlaces = Place::withCount(['bookings'])
                            ->whereHas('bookings')
                            ->orderBy('bookings_count', 'desc')
                            ->take(10)
                            ->with(['comments','comments.user:id,name,image','images','categories:id,name','area:id,name,country_id','area.country:id,name'])
                            ->get();
        return response()->json([
            'data'=>$topPlaces
        ],200);
    }

}
