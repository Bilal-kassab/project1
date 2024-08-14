<?php

namespace App\Http\Controllers;

use App\Http\Requests\Book\BookStaticTripRequest;
use App\Http\Requests\Book\CheckStaticTripRequest;
use App\Http\Requests\Book\DestroyRequest;
use App\Http\Requests\Book\EditStaticTripRequest;
use App\Http\Requests\Offer\OfferRequest;
use App\Http\Requests\Trip\SearchStaticBookRequest;
use App\Http\Requests\Trip\StoreStaticTripRequest;
use App\Http\Requests\Trip\UpdateStaticTripRequest;
use App\Models\Activity;
use App\Models\Bank;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\BookingStaticTrip;
use App\Models\Country;
use App\Models\Place;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Carbon\Carbon;
use DateTime;
use Exception;
use Google\Service\CloudSearch\Resource\Query;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaticBookController extends Controller
{


    private $bookrepository;

    public function __construct(BookRepositoryInterface $bookrepository)
    {
        $this->middleware('role:Admin|Super Admin|Trip manger', ['only'=> ['store_Admin','update_Admin','tripCancellation','offer']]);
        $this->middleware('role:Admin|Super Admin', ['only'=> ['getDetailsStaticTrip']]);
        $this->middleware('role:Admin|Super Admin|User', ['only'=> ['index']]);
        $this->middleware('role:Trip manger', ['only'=> ['getTripAdminTrips','getTripAdminTripDetails']]);
        $this->middleware('permission:unbanned', ['only' => ['checkStaticTrip','bookStaticTrip','editBook']]);
        $this->bookrepository = $bookrepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            $static_trips=$this->bookrepository->index($request);
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
        try{


            $data=[
                // 'source_trip_id'=>$request->source_trip_id,
                'destination_trip_id'=>$request->destination_trip_id,
                'hotel_id'=>$request->hotel_id,
                'trip_name'=>$request->trip_name,
                'ratio'=>$request->ratio,
                'telegram_link'=>$request->telegram_link,
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
                    'message' =>trans('trip.plane-trip-date')
                ], 400);
            }

            return response()->json([
                'data'=>$static_book
            ],200);
        }catch(Exception $exception){
                return response()->json([
                    'message' =>$exception->getMessage()
                ]);
        }

    }
    public function update_Admin(UpdateStaticTripRequest $request,$id)
    {
        try{
            $booking= Booking::findOrFail($id);
        $data=[
            // 'hotel_id'=>$request->hotel_id,
            'number_of_people'=>$request->add_new_people,
            'places'=>$request->places,
            'trip_note'=>$request->trip_note,
            // 'trip_capacity'=>$request->trip_capacity,
            'trip_name'=>$booking->trip_name,
            // 'price'=>$request->price,
            'start_date'=>$booking->start_date,
            'end_date'=>$booking->end_date,
            'plane_trip'=>$booking?->plane_trips[0]['id'],
            'plane_trip_away'=>$booking?->plane_trips[1]['id'],
        ];

            if(auth()->id() != $booking->user_id)
            {
                return response()->json([
                    'message'=>trans('global.not-permission'),
                ],200);
            }
            $edit=$this->bookrepository->editAdmin($data,$id);
            if ($edit === 8)
            {
                return response()->json([
                    'message' => trans('trip.haveBook')
                ], 400);
            }
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

    public function tripCancellation(DestroyRequest $request):JsonResponse
    {
        try {
            $val=$this->bookrepository->tripCancellation($request['id']);
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
        if($val==4)
        {
            return response()->json([
                'message'=>'check from check api',
            ],400);
        }
        // if($val['payment_type']=='wallet'){
        //     $bank=Bank::where('email',auth()->user()->email)->first();
        //     $bank['money']=$bank['money']-$val['book_price'];
        //     $bank['payments']+=$val['book_price'];
        //     $bank->save();
        // }
        return response()->json([
            'message'=>trans('trip.enjoy-trip')
        ],200);
        }catch(Exception $exception){
            return response()->json([
                'message'=>$exception->getMessage()
            ],400);
        }

    }

    public function stripePayment(BookStaticTripRequest $request)
    {
        try{

            $book_price=$request['total_price'];
            if($request['discount']){
                $book_price=$request['price_after_discount'];
            }

            $staticTrip=Booking::where([['id',$request['trip_id']],['type','static']])->first();
            // return $staticTrip;
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $response=$stripe->checkout->sessions->create([
        'line_items' => [
            [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                            'name' => $staticTrip['trip_name'],
                    ],
                    //trip price
                    'unit_amount' =>$book_price*100 ,
                ],
                'quantity' => 1,
            ]
        ],
        'mode' => 'payment',
        // 'success_url' => route('success').'?session_id={CHECKOUT_SESSION_ID}',
        // 'success_url' => self::success($request),
        'success_url' =>route('success',$request),
        'cancel_url' => route('cancel'),
        ]);
        if(isset($response->id)&& $response->id != ''){
            return redirect($response->url);
        }else{
            return redirect()->route('cancel');
        }
        }catch(Exception $ex){
            return response()->json([
                'message'=>$ex->getMessage()
            ],);
        }


    }

    public function success(Request $request){

        $val=$this->bookrepository->bookStaticTrip($request->all());
        // return $request;
            return response()->json([
                'message'=>'Enjoy Trip'
            ],200);
    }

    public function cancel()
    {
        return response()->json([
            'message'=>'failed plz try again'
        ],422);
    }

    public function showAllMyStaicTrips()
    {
        // $static_trip=Booking::with('user_rooms')->get();
        $date=Carbon::now()->format('Y-m-d');
        $finishedTrips=BookingStaticTrip::whereRelation('static_trip','start_date','<',$date)
                                        ->with('static_trip:id,trip_name,trip_capacity,start_date,end_date,stars,trip_note','rooms:id,capacity')
                                        ->select('id','user_id','static_trip_id','number_of_friend','book_price')
                                        ->where('user_id',auth()->id())
                                        ->get();
        $futureTrips=BookingStaticTrip::whereRelation('static_trip','start_date','>',$date)
                                        ->with('static_trip:id,trip_name,trip_capacity,start_date,end_date,stars,trip_note','rooms:id,capacity')
                                        ->select('id','user_id','static_trip_id','number_of_friend','book_price')
                                        ->where('user_id',auth()->id())
                                        ->get();


        return response()->json([
            'data'=>[
                'finished_trips'=>$finishedTrips,
                'future_trips'=>$futureTrips,
            ],
        ],200);
    }

    public function showPriceDetails($id)
    {
        try{
            $bookStaticTrip=BookingStaticTrip::findOrFail($id);
            if($bookStaticTrip['user_id']!= auth()->id()){
                return response()->json([
                    'message'=>trans('global.not-permission')
                ],403);
            }
            //get the trip
            $static_trip=Booking::where('type','static')->where('id',$bookStaticTrip['static_trip_id'])->first();
            //days
            $datetime1 = new DateTime($static_trip['start_date']);
            $datetime2 = new DateTime($static_trip['end_date']);
            $interval = $datetime1->diff($datetime2);
            $days = $interval->format('%a');
            //rooms_needed
            $rooms_needed=(int)($bookStaticTrip['number_of_friend']/$static_trip['trip_capacity']);
            if($bookStaticTrip['number_of_friend'] % $static_trip['trip_capacity'] >0) $rooms_needed++;
            //room price
            $room=BookingRoom::where([
                ['book_id',$static_trip['id']],
                ['user_id',auth()->id()]
                ])->first();
            //plane trip
            $plane_trip=$static_trip?->plane_trips;
            $goingPlaneTrip=$plane_trip[0]['current_price']??0;
            $returnPlaneTrip=$plane_trip[1]['current_price']??0;
            //place price
            $total_price=0.0;

            $total_price+=(($static_trip['price']-($room['current_price']*$days)));
            $placePrice=$total_price-$goingPlaneTrip-$returnPlaneTrip;
            $total_price*=$bookStaticTrip['number_of_friend'];
            $total_price+=$rooms_needed*$room['current_price']*$days;
            $data=[
                'trip_id'=>(int)$id,#
                'number_of_friend'=>(int)$bookStaticTrip['number_of_friend'],#
                'rooms_needed'=>$rooms_needed,#
                'days'=>(int)$days,#
                'room_price'=>(doubleval($room['current_price']))??null,#
                'ticket_price_for_going_trip'=>$goingPlaneTrip,#
                'ticket_price_for_return_trip'=>$returnPlaneTrip,#
                'ticket_price_for_places'=>$placePrice,#
                'total_price'=>$total_price,#
                // 'price_after_discount'=>$price_after_discount,
            ];
            return response()->json([
                'data'=>$data
            ],200);
        }catch(Exception $exception){

            return response()->json([
                'message'=>$exception->getMessage()
            ],404);
        }
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

    public function getDetailsStaticTrip($id):JsonResponse
    {
        try{
            $details=$this->bookrepository->getDetailsStaticTrip($id);
            return response()->json([
                'data'=>$details
            ],200);
        }catch(Exception $ex){
            return response()->json([
                'message'=>$ex->getMessage()
            ]);
        }

    }

    public function getTripAdminTrips():JsonResponse
    {
        try{
            $staticTrip=$this->bookrepository->getTripAdminTrips();
            return response()->json([
                'data'=>$staticTrip,
            ]);
        }catch(Exception $ex){
            return response()->json([
                'message'=>$ex->getMessage()
            ]);
        }
    }

    public function getTripAdminTripDetails($id):JsonResponse
    {
        try{
            $staticTrip=$this->bookrepository->getTripAdminTripDetails($id);
            return response()->json([
                'data'=>$staticTrip,
            ]);
        }catch(Exception $ex){
            return response()->json([
                'message'=>$ex->getMessage()
            ]);
        }
    }
    public function offer($id,OfferRequest $request):JsonResponse{
        try{
            $trip=booking::findOrFail($id);
            $booking=BookingStaticTrip::where('static_trip_id',$id)->exists();
            if(!$booking){
                $price=$trip['new_price']??$trip['price'];
                $trip['new_price']=$price-($request['ratio']*$price);
                $trip->save();
            }
            else{
                return response()->json([
                    'message'=>trans('trip.offer')
                ],400);
            }
            return response()->json([
                'data'=>$this->bookrepository->showStaticTrip($id)
            ],200);
        }
        catch(Exception $exception){
            return response()->json([
                'message'=>$exception->getMessage()
            ]);
        }
    }

    public function searchTrip(SearchStaticBookRequest $request)
    {
        try{
           $bookings=$this->bookrepository->searchTrip($request->all());

            return response()->json([
                'data'=>$bookings,
            ],200);
        }catch(Exception $ex){
            return response()->json([
                'message'=>$ex->getMessage(),
            ],500);
        }
    }
}
