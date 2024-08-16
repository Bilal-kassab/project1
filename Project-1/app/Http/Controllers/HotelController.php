<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hotel\StoreHotelRequest;
use App\Http\Requests\Hotel\UpdateHotelRequest;
use App\Models\Area;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\Hotel_Image;
use App\Models\HotelImage;
use Exception;
use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator ;

class HotelController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Admin|Hotel admin', ['only'=> ['invisibleAdminHotel','store','update','update_Image_Hotel','addAirportImage','destroy']]);
        $this->middleware('role:Super Admin', ['only'=> ['index','destroySuperAdmin','changeVisible']]);

    }

    public function index()
    {

        return response()->json(['data'=>Hotel::with(
                                                    'images:id,hotel_id,image',
                                                    'area:id,name,country_id',
                                                    'country:id,name',
                                                    'user:id,name,position,email')
                                                    ->orderBy('stars','desc')->get()
        ],200);
    }

    public function get_hotel_in_area(Request $request,$id)
    {
        try{
            $area=Area::findOrFail($id);
        }catch(\Exception $e){
            return response()->json([
                'message'=>trans('global.notfound')
            ]);
        }
        if($request->user()->hasRole('User')){
            return response()->json([
                'data'=>Hotel::with(['images'])
                            ->where('area_id',$id)
                            ->where('visible',true)
                            ->select('id','name','stars','number_rooms','area_id','country_id','user_id')
                            ->orderBy('stars','desc')
                            ->get(),
            ],200);
        }
        else{
            return response()->json([
                'data'=>Hotel::with(['images','user:id,name,position,email,phone_number,image'])
                            ->where('area_id',$id)
                            ->select('id','name','stars','number_rooms','area_id','country_id','user_id','visible')
                            ->orderBy('stars','desc')
                            ->get(),
            ],200);
        }
    }

    public function get_hotel_in_country(Request $request,$id)
    {
        try{
            $country=Country::findOrFail($id);
        }catch(\Exception $e){
            return response()->json([
                'message'=>trans('global.notfound')
            ]);
        }

        if($request->user()->hasRole('User')){
            return response()->json([
                'data'=>Hotel::with(['images','area'])
                            ->where('country_id',$id)
                            ->where('visible',true)
                            ->select('id','name','stars','number_rooms','area_id','country_id')
                            ->orderBy('stars','desc')
                            ->get(),
            ],200);
        }
        else{
            return response()->json([
                'data'=>Hotel::with(['images','user:id,name,position,email,phone_number,image','area'])
                ->where('country_id',$id)
                ->select('id','name','stars','number_rooms','area_id','country_id','user_id','visible')
                ->orderBy('stars','desc')
                ->get(),
            ],200);
        }
    }


    public function store(StoreHotelRequest $request)
    {
        $area=Area::find($request->area_id);
        $hotel= Hotel::Create([
            'name'=>$request->name,
            'user_id'=> auth()->user()->id,
            'area_id'=> $request->area_id,
            'country_id'=>Area::find($request->area_id)['country_id'],
            // 'number_rooms'=> $request->number_rooms,
            'stars'=> $request->stars??3,
        ]);

        if($request->hasFile('images')){
            foreach ($request->file('images') as $imagefile){
                $images = new HotelImage;
                $images->hotel_id= $hotel->id;
                $images->save();
                $image_name=time().$images->id. '.' . $imagefile->getClientOriginalExtension();
                $imagefile->move('HotelImages/',$image_name);
                $images->image = "HotelImages/".$image_name;
                $images->save();
            }
        }
        return response()->json([
            'message'=>trans('global.add'),
            'data'=>Hotel::with(['images:id,hotel_id,image','area:id,name,country_id','country:id,name','user:id,name,position,phone_number,email'])
                    ->where('id', $hotel->id)->get()
            ],200);
    }

    public function show($id)
    {
            try{
                $hotel= Hotel::with(['images','country:id,name','area:id,country_id,name'])
                                ->select('id','name','number_rooms','stars','area_id','user_id','country_id')
                                ->findOrFail($id);
            }catch(\Exception $e){
                return response()->json([
                    'message'=> trans('global.notfound')
                ],404);
            }

            return response()->json([
                'data'=> $hotel
            ],200);
    }

    public function update(UpdateHotelRequest $request)
    {
        // if(auth()->user()->id !=Hotel::where('id',$id)->first()->user_id ){
        //  return response()->json([
        //      'message'=>trans('global.not-have-the-hotel')
        //  ]);
        // }
        try{
            $hotel=Hotel::where('user_id',auth()->id())->first();
            // $hotel =Hotel::findOrFail($id);

         }catch(\Exception $e){
            return response()->json([
                'message'=>trans('global.notfound')
            ],404);
         }
         $hotel->name = $request->name;
         $hotel->area_id=$request->area_id;
         $hotel->country_id=Area::find($request->area_id)['country_id'];
         $hotel->number_rooms += $request->number_rooms;
        //  $hotel->user_id=$request->user_id;
         // $hotel->stars = $request->stars;
        //  $hotel->visible=$request->visible;
         $hotel->save();


        return response()->json([
            'message'=>trans('global.update'),
            'data'=>Hotel::with(['images','country:id,name','area:id,name'])
                          ->where('id',$hotel['id'])
                          ->select('id','name','stars','number_rooms','visible','area_id','user_id','country_id')
                          ->get(),

        ],200);
    }

    public function add_Hotel_Image(Request $request):JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images'=> 'present|array|min:1',
            'images.*' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'hotel_id'=>'required|numeric|exists:hotels,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }
        $hotel=Hotel::where('id',$request->hotel_id)->first();
        if(auth()->id() != $hotel->user_id)
        {
            return response()->json([
                'message'=>trans('global.not-permission')
            ],403);
        }

        foreach ($request->file('images') as $imagefile){
            $images = new HotelImage;
            $images->hotel_id= $hotel->id;
            $images->save();
            $image_name=time().$images->id. '.' . $imagefile->getClientOriginalExtension();
            $imagefile->move('HotelImages/',$image_name);
            $images->image = "HotelImages/".$image_name;
            $images->save();
        }

        // return response()->json([
        //     'mesaage'=>trans('global.add')
        // ],200);
        return response()->json([
            'message'=>trans('global.add'),
            'data'=>Hotel::with(['images','country:id,name','area:id,name'])
                        ->where('id',$images->hotel_id)
                        ->select('id','name','stars','number_rooms','area_id','user_id','country_id')
                        ->get(),
        ],200);
    }

    public function update_Image_Hotel(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'image_id'=>'required|numeric|exists:hotel_images,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }
        $hotel=HotelImage::where('id',$request->image_id)->first();
        if(auth()->user()->id != Hotel::where('id',$hotel->hotel_id)->first()->user_id)
        {
            return response()->json([
                'message'=>trans('global.not-have-the-hotel')
            ]);
        }
        $hotel_image = HotelImage::findOrFail($request->image_id);

        }catch(\Exception $e){
            return response()->json([
                'message'=> trans('global.notfound')
            ],404);
        }
        if(File::exists($hotel_image->image))
        {
            File::delete($hotel_image->image);
        }
        $image = $request->file('image');
        $image_name=time() . '.' . $image->getClientOriginalExtension();
        $image->move('HotelImages/',$image_name);
        $hotel_image->image="HotelImages/".$image_name;
        $hotel_image->save();

        // $data=[
        //     'id'=>$hotel_image->id,
        //     'hotel_id'=>$hotel_image->hotel_id,
        //     'hotel'=>Hotel::where('id',$hotel_image->hotel_id)->pluck('name'),
        //     'image'=> $hotel_image->image,
        //     'updated_at'=>$hotel_image->updated_at
        // ];
        return response()->json([
            'message'=>trans('global.update'),
            'data'=>Hotel::with(['images','country:id,name','area:id,name'])
                        ->where('id',$hotel_image->hotel_id)
                        ->select('id','name','stars','number_rooms','area_id','user_id','country_id')
                        ->get(),
        ],200);
    }

    public function search_Hotel_by_Name(Request $request)
    {

        $validatedData = Validator::make($request->all(),[
            'name' => ['required','string'],
        ]);
        if( $validatedData->fails() ){
            return response()->json([
                'message'=> $validatedData->errors()->first(),
            ],422);
        }
                    if($request->user()->hasRole('User')){
                        return response()->json([
                            'data'=>Hotel::with(['images','country:id,name','area:id,country_id,name'])
                            ->select('id','name','number_rooms','stars','area_id','country_id')
                            ->where('name','like','%'.$request->name.'%')
                            ->where('visible',true)
                            ->orderBy('stars','desc')
                            ->get()
                        ],200);
                    }
                    else{
                        return response()->json([
                            'data'=>Hotel::with(['images','area:id,country_id,name','country:id,name','user:id,name,position,email,phone_number,image'])
                            ->select('id','name','number_rooms','stars','area_id','user_id','country_id','visible')
                            ->where('name','like','%'.$request->name.'%')
                            ->orderBy('stars','desc')
                            ->get()
                        ],200);
                    }
    }

    public function search_Hotel_by_Stars(Request $request)
    {

        $validatedData = Validator::make($request->all(),[
            'stars' => ['required','numeric','min:0','max:5'],
        ]);
        if( $validatedData->fails() ){
            return response()->json([
                'message'=> $validatedData->errors()->first(),
            ],422);
        }
                    if($request->user()->hasRole('User')){
                        return response()->json([
                            'data'=>Hotel::with(['images','country:id,name','area:id,country_id,name'])
                            ->select('id','name','number_rooms','stars','area_id','country_id')
                            ->where('stars','like','%'.$request->stars.'%')
                            ->where('visible',true)
                            ->get()
                        ],200);
                    }
                    else{
                        return response()->json([
                            'data'=>Hotel::with(['images','country:id,name','area:id,country_id,name','user:id,name,position,email,phone_number,image'])
                            ->select('id','name','number_rooms','stars','area_id','country_id','user_id','visible')
                            ->where('stars','like','%'.$request->stars.'%')
                            ->get()
                        ],200);
                    }
    }

    public function destroy()
    {
        // if(auth()->user()->id !=Hotel::where('id',$id)->first()->user_id ){
        //     return response()->json([
        //         'message'=>trans('global.not-have-the-hotel')
        //     ]);
        //    }
        try{
            $hotel=Hotel::where('user_id',auth()->id())->first();
            $hotel->delete();
            }catch(\Exception $exception){
                return response()->json([
                    'message'=>trans('global.notfound')
                ],404);
            }
            return response()->json([
                'message'=>trans('global.delete')
            ],200);
    }

    public function destroySuperAdmin(Request $request): JsonResponse
    {
        try{
            $validator = Validator::make($request->all(), [
                'hotel_id'=>'required|numeric|exists:hotels,id',
                // 'visible'=>'required|numeric|boolean',
            ]);
            if( $validator->fails() ){
                return response()->json([
                    'message'=> $validator->errors()->first(),
                ],422);
            }
            $hotel= Hotel::where('id',$request->hotel_id)->first();
            $hotel->delete();
        }catch(\Exception $e){
            return response()->json([
                'message'=> trans('global.notfound'),
            ],404);
        }
        return response()->json([
            'message'=> trans('global.delete')
        ]);
    }

    public function changeVisible(Request $request){
        $validator = Validator::make($request->all(), [
            'id'=>'required|numeric|exists:hotels,id',
            'visible'=>'required|numeric|boolean',
        ]);
        if( $validator->fails() ){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }
        // if(auth()->user()->id != $request->user()->id){
        //     return response()->json([
        //         'message'=>'you dont have this hotel'
        //     ],401);
        // }

        $hotel=Hotel::findOrFail($request->id);
        $hotel['visible']=$request->visible;
        $hotel->save();
        return response()->json([
            'data'=>$hotel
        ],200);

    }

    public function get_my_hotel(){
        try{
            return response()->json([
                    'data'=>Hotel::with('area','country','images')->where('user_id',auth()->user()->id)->get()
                ],200);
        }catch(Exception $e){
            return response()->json([
                'message'=>$e->getMessage()
            ],404);
        }
    }

    public function invisibleAdminHotel()
    {
        try{
            $hotel=Hotel::where('user_id',auth()->id())->first();
            if($hotel->visible)
            {
                $hotel->visible=false;
            }else{
                $hotel->visible=true;
            }
            $hotel->save();
        }catch(Exception $exception)
        {
            return response()->json([
                'message'=>$exception->getMessage()
            ]);
        }
        return response()->json([
            'message'=>trans('global.update')
        ],200);
    }
}
