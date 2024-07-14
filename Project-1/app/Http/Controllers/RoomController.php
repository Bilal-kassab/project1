<?php

namespace App\Http\Controllers;

use App\Http\Requests\Room\IndexRoomRequest;
use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Models\BookingRoom;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Database\Eloquent\Builder;

class RoomController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:Admin|Hotel admin', ['only'=> ['store','update','get_My_Rooms','change_status_room','destroy']]);
    }
    public function index(IndexRoomRequest $request,$id)
    {

    $start=$request->start_date;
    $end=$request->end_date;
        try{
            $capacity_2_price=0;
            $capacity_4_price=0;
            $capacity_6_price=0;
            $capacity_2_count=Room::query()->available($start,$end)->where([
                ['capacity', '=', 2],
                ['status', '=', 0],
                ['hotel_id',$id],
                ])->count();
            $capacity_2_price=Room::where([
                ['capacity', '=', 2],
                ['hotel_id',$id],
            ])->get('price')[0]['price'];

            $capacity_4_count=Room::query()->available($start,$end)->where([
                ['capacity', '=', 4],
                ['status', '=', 0],
                ['hotel_id',$id],
            ])->count();
            $capacity_4_price=Room::where([
                ['capacity', '=', 4],
                ['hotel_id',$id],
            ])->get('price')[0]['price'];

            $capacity_6_count=Room::query()->available($start,$end)->where([
                ['capacity', '=', 6],
                ['status', '=', 0],
                ['hotel_id',$id],
            ])->count();
            $capacity_6_price=Room::where([
                ['capacity', '=', 6],
                ['hotel_id',$id],
            ])->get('price')[0]['price'];

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
        return response()->json([
            'data'=>$data,
        ],200);

        }catch(Exception $e){
            return response()->json([
                'message'=>$e->getMessage(),
            ]);
        }

    }

    // public function index(Request $request,$id){
    //     $date=Carbon::now()->format('Y-m-d');
    //     $validatedData =Validator::make($request->all(),[
    //         //'hotel_id'=>'required|numeric|exists:hotels,id',
    //         //'capacity'=>'required|numeric',
    //         'start_date'=>"required|date|after_or_equal:$date",
    //         'end_date'=>'required|date|after_or_equal:start_date',
    //    ]);
    //    if( $validatedData->fails() ){
    //        return response()->json([
    //            'message'=> $validatedData->errors()->first(),
    //        ],422);
    //    }
    //    $start=$request->start_date;
    //    $end=$request->end_date;

    //      return response()->json([
    //         'data'=>Room::available($start,$end)->where('hotel_id',$id)->get(),
    //         // 'data'=>Room::whereDoesntHave('bookingss')->where('hotel_id',$id)->get(),
    //      ]);
    // }
    public function get_My_Rooms(){
        try{
            return response()->json([
                    'data'=>Hotel::with('rooms')->where('user_id',auth()->user()->id)->get()
                ],200);
        }catch(Exception $e){
            return response()->json([
                'message'=>$e->getMessage()
            ],404);
        }
    }
    public function store(StoreRoomRequest $request)
    {
        try{

       $my_hotel=Hotel::where('user_id',auth()->id())->first();

       if(auth()->user()->id != $my_hotel->user_id){
        return response()->json([
            'message'=>trans('global.not-have-the-hotel')
        ]);
       }

    $co=$request->count;
      while($co){
            $data[]=[
            'hotel_id'=>$my_hotel->id,
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
            'message'=>trans('global.add'),
        ],200);
    }
    public function show($id)
    {

        try{
            $room= Room::with(['hotel:id,name,area_id,user_id','hotel.user:id,name,email'])
            ->select('id','capacity','price','hotel_id')->findOrFail($id);
         }catch(Exception $e){
            return response()->json([
                'message'=> trans('global.notfound')
            ],404);
         }

        return response()->json([
            'data'=> $room
        ],200);

    }

    public function update(UpdateRoomRequest $request)
    {
        // if(auth()->user()->id != $my_hotel->user_id){
        //  return response()->json([
        //      'message'=>trans('global.not-have-the-hotel')
        //  ]);
        // }
        try{
            $my_hotel=Hotel::where('user_id',auth()->user()->id)->first();
            $room=Room::where('hotel_id',$my_hotel->id)
                        ->where('capacity',$request->capacity)
                        ->get();
            foreach($room as $r){
                $r->price=$request->price;
                $r->save();
                }
            return response()->json([
                'message'=>trans('global.update'),
            ],200);
         }catch(Exception $e){
            return response()->json([
                'message'=> $e->getMessage(),
            ],404);
         }
    }
    public function destroy($id)
    {
        if(auth()->user()->id != Hotel::where('id',Room::where('id',$id)->first()->hotel_id)->first()->user_id ){
         return response()->json([
             'message'=>trans('global.not-have-the-hotel')
         ]);
        }
        try{
            Room::findOrFail($id)->delete();
            }catch(Exception $exception){
                return response()->json([
                    'message'=>trans('global.notfound')
                ],404);
            }
            return response()->json([
                'message'=>trans('global.delete')
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


}


