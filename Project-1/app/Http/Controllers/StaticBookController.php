<?php

namespace App\Http\Controllers;

use App\Http\Requests\Book\BookStaticTripRequest;
use App\Http\Requests\Book\CheckStaticTripRequest;
use App\Http\Requests\Book\EditStaticTripRequest;
use App\Http\Requests\Trip\StoreStaticTripRequest;
use App\Http\Requests\Trip\UpdateStaticTripRequest;
use App\Models\Booking;
use App\Models\BookingStaticTrip;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;

class StaticBookController extends Controller
{


    private $bookrepository;

    public function __construct(BookRepositoryInterface $bookrepository)
    {
        $this->middleware('role:Admin|Super Admin', ['only'=> ['store_Admin','update_Admin','tripCancellation']]);
        $this->bookrepository = $bookrepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $static_trips=$this->bookrepository->index();
            return response()->json([
                'data'=>$static_trips
            ],200);
        }catch(Exception $e){
            return response()->json([
                'message'=>$e->getMessage(),
            ],404);

        }
    }

    public function store_Admin(StoreStaticTripRequest $request)
    {
        $data=[
            'source_trip_id'=>$request->source_trip_id,
            'destination_trip_id'=>$request->destination_trip_id,
            'hotel_id'=>$request->hotel_id,
            'trip_name'=>$request->trip_name,
            'ratio'=>$request->ratio,
            'number_of_people'=>$request->number_of_people,
            'trip_capacity'=>$request->trip_capacity,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date,
            'trip_note'=>$request->trip_note,
            'places'=>$request->places,
            'activities'=>$request->activities,##
            'plane_trip'=>$request->plane_trip,
            'plane_trip_away'=>$request->plane_trip_away,
        ];
        $static_book=$this->bookrepository->store_Admin($data);
        if($static_book == 1){
            return response()->json([
                'message'=>trans('trip.not-enough-room'),
            ],400);
        }
        if($static_book == 2){
            return response()->json([
                'message' =>trans('trip.not-enough-going-trip-plane')
            ], 400);
        }
        if($static_book == 3){
            return response()->json([
                'message' =>trans('trip.not-enough-return-trip-plane')
            ], 400);
        }
        if($static_book == 4){
            return response()->json([
                'message' => trans('trip.trip-faild')
            ], 400);
        }

        return response()->json([
            'data'=>$static_book
        ],200);
    }
    public function update_Admin(UpdateStaticTripRequest $request,$id)
    {
        $data=[
            // 'hotel_id'=>$request->hotel_id,
            'trip_name'=>$request->trip_name,
            'price'=>$request->price,
            'number_of_people'=>$request->add_new_people,
            // 'trip_capacity'=>$request->trip_capacity,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date,
            'trip_note'=>$request->trip_note,
            'places'=>$request->places,
            'plane_trip'=>$request->plane_trip,
            'plane_trip_away'=>$request->plane_trip_away,
        ];
        try {
            $booking= Booking::findOrFail($id);
            if(auth()->id() != $booking->user_id)
            {
                return response()->json([
                    'message'=>trans('global.not-permission'),
                ],200);
            }
            $edit=$this->bookrepository->editAdmin($data,$id);
            if($edit === 2){
                return response()->json([
                    'message' => trans('trip.not-enough-going-trip-plane')
                ], 400);
            }
            if($edit === 6){
                return response()->json([
                    'message' => trans('trip.not-enough-room')
                ], 400);
            }
            if($edit === 3){
                return response()->json([
                    'message' => trans('trip.not-enough-return-trip-plane')
                ], 400);
            }
            if($edit === 4)
            {
                return response()->json([
                    'message'=>trans('trip.start-trip')
                ],404);
            }
            if ($edit === 5)
            {
                return response()->json([
                    'message' => trans('trip.invaild-date')
                ], 400);
            }
            return response()->json([
                'message'=>trans('global.update'),
                'data'=>$edit,
              ],200);
        } catch (Exception $exception) {
            return response()->json([
                // 'message'=>'Update Fail',
                'message'=>$exception->getMessage(),
            ],422);
        }
    }

    public function tripCancellation($id):JsonResponse
    {
        try {
            $val=$this->bookrepository->tripCancellation($id);
            return response()->json([
                'message'=>$val
            ],200);
        } catch (Exception $exception) {
            return response()->json([
                'message'=>$exception->getMessage()
            ],422);
        }
    }

    public function showStaticTrip($id)
    {
        $trip=$this->bookrepository->showStaticTrip($id);
        if($trip===1)
        {
            return response()->json([
                'message'=>trans('global.notfound')
            ],404);
        }
        return response()->json(['data'=>$trip],200);

    }

    public function checkStaticTrip(CheckStaticTripRequest $request,$id):JsonResponse
    {
        try{
            $val=$this->bookrepository->checkStaticTrip($request->all(),$id);
            if($val==1){
                return response()->json([
                    'message'=>trans('trip.not-enough-member'),
                ],400);
            }
            if($val==2){
                return response()->json([
                    'message'=>trans('trip.not-enough-room'),
                ],400);
            }
            if($val==3){
                return response()->json([
                    'message'=>'Error!',
                ],400);
            }
            return response()->json([
                'data'=>$val
            ],200);
        }catch(Exception $exception){
            return response()->json([
                'message'=>$exception->getMessage(),
            ],400);
        }
    }

    public function bookStaticTrip(BookStaticTripRequest $request):JsonResponse
    {
        try{
            $val=$this->bookrepository->bookStaticTrip($request->all());
        if($val==1)
        {
            return response()->json([
                'message'=>trans('trip.not-have-the-money'),
            ],400);
        }
        return response()->json([
            'message'=>trans('trip.enjoy-trip')
        ],200);
        }catch(Exception $exception){
            return response()->json([
                'message'=>$exception->getMessage()
            ],400);
        }

    }

    public function showAllMyStaicTrips()
    {
        //$static_trip=Booking::with('user_rooms')->get();
        $static_trip=BookingStaticTrip::with('static_trip:id,trip_name,trip_capacity,start_date,end_date,stars,trip_note','rooms:id,capacity')
                                        ->select('id','user_id','static_trip_id','number_of_friend','book_price')
                                        ->where('user_id',auth()->id())
                                        ->get();

        return response()->json([
            'data'=>$static_trip,
        ],200);
    }

    public function editBook(EditStaticTripRequest $request,$id)
    {
        try{
            $data=[
                'number_of_friend'=>$request['new_number_of_friend'],
                'discount'=>$request['discount']
            ];
           $val=$this->bookrepository->editBook($data,$id);
           if($val==1){
                return response()->json([
                    'message'=>trans('trip.not-enough-member'),
                ],400);
            }
            if($val==2){
                return response()->json([
                    'message'=>trans('trip.not-enough-room'),
                ],400);
            }
            if($val==3){
                return response()->json([
                    'message'=>trans('trip.not-have-the-money'),
                ],400);
            }
            if($val==5){
                return response()->json([
                    'message'=>'Error int this trip',
                ],400);
            }
            if($val==10){
                return response()->json([
                    'message'=>trans('trip.invaild-date')
                ]);
            }

            return response()->json([
                'message'=>trans('global.update')
            ],200);
        }catch(Exception $exception){
            return response()->json([
                'message'=>$exception->getMessage()
            ],400);
        }
    }

    public function deleteBook($id):JsonResponse
    {
        $val=$this->bookrepository->deleteBook($id);
        if($val==1){
            return response()->json([
                'message'=>trans('trip.invaild-date')
            ],400);
        }

        if($val==3){
            return response()->json([
                'message'=>trans('global.notfound'),
            ],404);
        }
        return response()->json([
            'message'=>trans('trip.cancel-trip'),
        ],400);

    }
}
