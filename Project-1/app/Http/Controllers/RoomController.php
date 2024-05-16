<?php

namespace App\Http\Controllers;

use App\Models\BookingRoom;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class RoomController extends Controller
{

    public function index($id)
    {
        try{
            $capacity_2_price=0;
            $capacity_4_price=0;
            $capacity_6_price=0;
            $capacity_2_count=Room::query()->where([
                 ['capacity', '=', 2],
                 ['status', '=', 0],
                 ['hotel_id',$id],
             ])->count();
             if($capacity_2_count!=0){
            $capacity_2_price=Room::query()->where([
                ['capacity', '=', 2],
                ['status', '=', 0],
                ['hotel_id',$id],
            ])->get('price')[0]['price'];
             }
            $capacity_4_count=Room::query()->where([
                ['capacity', '=', 4],
                ['status', '=', 0],
                ['hotel_id',$id],
            ])->count();
                if($capacity_4_count!=0){
                    $capacity_4_price=Room::query()->where([
                        ['capacity', '=', 4],
                        ['status', '=', 0],
                        ['hotel_id',$id],
                    ])->get('price')[0]['price'];

                }

        $capacity_6_count=Room::query()->where([
            ['capacity', '=', 6],
            ['status', '=', 0],
            ['hotel_id',$id],
        ])->count();
        if($capacity_6_count!=0){
                    $capacity_6_price=Room::query()->where([
                    ['capacity', '=', 6],
                    ['status', '=', 0],
                    ['hotel_id',$id],
                ])->get('price')[0]['price'];
        }

       $data=[
        'capacity_2'=>[
            'count'=>$capacity_2_count,
            'price'=>$capacity_2_price
        ],
        'capacity_4'=>[
            'count'=>$capacity_4_count,
            'price'=>$capacity_4_price
        ],
        'capacity_6'=>[
            'count'=>$capacity_6_count,
            'price'=>$capacity_6_price
        ],
       ];

        }catch(Exception $e){
            return response()->json([
                'message'=>$e->getMessage(),
            ]);
        }
        return response()->json([
            'data'=>$data,
        ],200);

    }
    public function get_My_Rooms()
    {

        try{

        }catch(Exception $e){
            return response()->json([
                'message'=>$e->getMessage()
            ],404);
        }

        return response()->json([
                'data'=>Hotel::with('rooms')->where('user_id',auth()->user()->id)->get()
            ],200);
    }
    public function store(Request $request)
    {
        try{
        $validatedData =Validator::make($request->all(),[
            'hotel_id'=>'required|numeric|exists:hotels,id',
            'capacity'=>'required|numeric',
            'price'=>'required|numeric',
            'count'=>'required|min:1|numeric',
       ]);
       if( $validatedData->fails() ){
           return response()->json([
               'message'=> $validatedData->errors()->first(),
           ],422);
       }

       $my_hotel=Hotel::where('id',$request->hotel_id)->first();
       if(auth()->user()->id != $my_hotel->user_id){
        return response()->json([
            'message'=>'you dont have this hotel'
        ]);
       }

    $co=$request->count;
      while($co){
            $data[]=[
            'hotel_id'=>$request->hotel_id,
            'capacity'=>$request->capacity,
            'price'=>$request->price
            ];

            $co = $co-1;
        }
        Room::insert($data);
    }catch(Exception $e){
        return response()->json([
            'message'=>$e->getMessage(),
        ],404);
    }
        return response()->json([
            'message'=>'added done',
        ],200);
    }
    public function show($id)
    {

        try{
            $room= Room::with(['hotel:id,name,area_id,user_id','hotel.user:id,name,email'])
            ->select('id','capacity','price','hotel_id')->findOrFail($id);
         }catch(Exception $e){
            return response()->json([
                'message'=> 'Not found'
            ],404);
         }

        return response()->json([
            'data'=> $room
        ],200);

    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_id'=>'required|numeric|exists:hotels,id',
            'capacity'=>[
                'required',Rule::exists('rooms')->where(function ($query) {
                       return $query->where('hotel_id', request()->get('hotel_id'));
                    }),],

            'price'=>'required|numeric'
        ]);

        if( $validator->fails() ){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }

        $my_hotel=Hotel::where('id',$request->hotel_id)->first();
        if(auth()->user()->id != $my_hotel->user_id){
         return response()->json([
             'message'=>'you dont have this hotel'
         ]);
        }

        try{
            $room=Room::where('hotel_id',$request->hotel_id)
                        ->where('capacity',$request->capacity)
                        ->get();

         }catch(Exception $e){
            return response()->json([
                'message'=> $e->getMessage(),
            ],404);
         }
         foreach($room as $r){
            $r->price=$request->price;
            $r->save();
         }
        return response()->json([
            'message'=>'updated successfully',
        ],200);

    }
    public function destroy($id)
    {
        if(auth()->user()->id != Hotel::where('id',Room::where('id',$id)->first()->hotel_id)->first()->user_id ){
         return response()->json([
             'message'=>'you dont have this room'
         ]);
        }
        try{
            Room::findOrFail($id)->delete();
            }catch(Exception $exception){
                return response()->json([
                    'message'=>'Not Found'
                ],404);
            }
            return response()->json([
                'message'=>'delete done!!'
            ],200);
    }
    public function change_status_room(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=>'required|numeric|exists:rooms,id',
            'status'=>'required|numeric|boolean',
        ]);
        if( $validator->fails() ){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }
        if(auth()->user()->id != Hotel::where('id',Room::where('id',$request->id)->first()->hotel_id)->first()->user_id ){
            return response()->json([
                'message'=>'you dont have this hotel'
            ],401);
        }
        $room=Room::findOrFail($request->id);
        $room['status']=$request->status;
        $room->save();
        return response()->json([
            'data'=>$room
        ],200);

    }

    public function booking_room(Request $request)
    {
   
            $date=Carbon::now()->format('Y-m-d');
            $validatedData =Validator::make($request->all(),[
                'book_id'=>'required|numeric|exists:bookings,id',
                'room_id'=>'required|numeric|exists:rooms,id',
                //'current_price'=>'required|numeric|min:0',
                'start_date'=>"required|date|after_or_equal:$date",
                'end_date'=>'required|date|after_or_equal:end_date',
           ]);
           if( $validatedData->fails() ){
               return response()->json([
                   'message'=> $validatedData->errors()->first(),
               ],422);
           }
           try{
        $book=BookingRoom::create([
            'book_id'=>$request->book_id,
            'room_id'=>$request->room_id,
            'current_price'=>Room::where('id',$request->room_id)->first()->price,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date,
        ]);
    }catch(Exception $e){
        return response()->json([
            'message'=>$e->getMessage(),
        ],404);
    }
    return response()->json([
        'data'=>$book
    ],200);

    }
}


