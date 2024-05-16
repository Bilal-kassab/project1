<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\Hotel_Image;
use App\Models\HotelImage;
use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator ;

class HotelController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Admin|Hotel admin', ['only'=> ['store','update','update_Image_Hotel','addAirportImage']]);
        $this->middleware('role:Super Admin|', ['only'=> ['index,destroy,changeVisible']]);
       
    }
    public function index()
    {

        return response()->json(['data'=>Hotel::with(
            'images:id,hotel_id,image',
            'area:id,name,country_id',
            'country:id,name',
            'user:id,name,position,email')->get()
        ],200);
    }

    public function get_hotel_in_area(Request $request,$id)
    {
        try{
            $area=Area::findOrFail($id);
        }catch(\Exception $e){
            return response()->json([
                'message'=>'Not Found'
            ]);
        }
        if($request->user()->hasRole('User')){
            return response()->json([
                'data'=>Hotel::with(['images'])
                ->where('area_id',$id)
                ->select('id','name','stars','number_rooms','area_id','country_id','user_id')
                ->get(),
            ],200);
        }
        else{
            return response()->json([
                'data'=>Hotel::with(['images','user'])
                ->where('area_id',$id)
                ->select('id','name','stars','number_rooms','area_id','country_id','user_id')
                ->get(),
            ],200);
        }
        // return response()->json([
        //     'data'=>Hotel::with(['images'])
        //                   ->where('area_id',$id)
        //                   ->select('id','name','stars','rooms','area_id','country_id')
        //                   ->get(),
        // ]);


    }

    public function get_hotel_in_country(Request $request,$id)
    {
        try{
            $country=Country::findOrFail($id);
        }catch(\Exception $e){
            return response()->json([
                'message'=>'Not Found'
            ]);
        }

        if($request->user()->hasRole('User')){
            return response()->json([
                'data'=>Hotel::with(['images'])
                ->where('country_id',$id)
                ->select('id','name','stars','number_rooms','area_id','country_id')
                ->get(),
            ],200);
        }
        else{
            return response()->json([
                'data'=>Hotel::with(['images','user'])
                ->where('country_id',$id)
                ->select('id','name','stars','number_rooms','area_id','country_id','user_id')
                ->get(),
            ],200);
        }
    }


    public function store(Request $request)
    {
        $validatedData =Validator::make($request->all(),[
             'name'=>'required|string|unique:hotels',
            // 'user_id'=>'required|numeric|exists:users,id',
             'area_id'=>'required|numeric|exists:areas,id',
             'number_rooms'=>'required|numeric|max:1000|min:10',
             'stars'=>'required|numeric|min:0|max:5',
             'images'=> 'array',
             'images.*' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);
        if( $validatedData->fails() ){
            return response()->json([
                'message'=> $validatedData->errors()->first(),
            ],422);
        }
        
        $area=Area::find($request->area_id);
        $hotel= Hotel::Create([
            'name'=>$request->name,
            'user_id'=> auth()->user()->id,
            'area_id'=> $request->area_id,
            'country_id'=>Area::find($request->area_id)['country_id'],
            'number_rooms'=> $request->number_rooms,
            'stars'=> $request->stars,
        ]);

        if($request->hasFile('images')){
            foreach ($request->file('images') as $imagefile){
                $images = new HotelImage;
                $images->hotel_id= $hotel->id;
                $image_name=time() . '.' . $imagefile->getClientOriginalExtension();
                $imagefile->move('HotelImages/',$image_name);
                $images->image = "HotelImages/".$image_name;
                $images->save();
            }
        }
        return response()->json([
            'message'=>'succesfully',
            'data'=>Hotel::with(['images:id,hotel_id,image','area:id,name,country_id','country:id,name','user:id,name,position,email'])
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
                    'message'=> 'Not found'
                ],404);
             }

            return response()->json([
                'data'=> $hotel
            ],200);
     }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'=>'required|string|unique:hotels',
            'user_id'=>'required|numeric|exists:users,id',
            'area_id'=>'required|numeric|exists:areas,id',
            'number_rooms'=>'required|numeric|max:1000|min:10',
           // 'stars'=>'required|numeric|min:0|max:5',
            // 'visible'=>'required|numeric|boolean',
        ]);

        if( $validator->fails() ){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }

        if(auth()->user()->id !=Hotel::where('id',$id)->first()->user_id ){
         return response()->json([
             'message'=>'you dont have this hotel'
         ]);
        }
        try{
            $hotel =Hotel::findOrFail($id);

         }catch(\Exception $e){
            return response()->json([
                'message'=> 'Not found'
            ],404);
         }
         $hotel->name = $request->name;
         $hotel->area_id=$request->area_id;
         $hotel->country_id=Area::find($request->area_id)['country_id'];
         $hotel->number_rooms = $request->number_rooms;
         $hotel->user_id=$request->user_id;
         // $hotel->stars = $request->stars;
        //  $hotel->visible=$request->visible;
         $hotel->save();


        return response()->json([
            'message'=>'updated successfully',
            'data'=>Hotel::with(['images','country:id,name','area:id,name'])
                          ->where('id',$id)
                          ->select('id','name','stars','number_rooms','visible','area_id','user_id','country_id')
                          ->get(),

        ],200);
    }

    public function update_Image_Hotel(Request $request)
    {
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
                'message'=>'you dont have this hotel'
            ]);
           }

        try{
            $hotel_image = HotelImage::findOrFail($request->image_id);
         }catch(\Exception $e){
            return response()->json([
                'message'=> 'Not found'
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

        $data=[
            'id'=>$hotel_image->id,
            'hotel_id'=>$hotel_image->hotel_id,
            'hotel'=>Hotel::where('id',$hotel_image->hotel_id)->pluck('name'),
            'image'=> $hotel_image->image,
            'updated_at'=>$hotel_image->updated_at
        ];
        return response()->json([
            'message'=>'photo updated successfully',
            'data'=>Hotel::with(['images'])
                          ->where('id',$hotel_image->hotel_id)
                          ->select('id','name','stars','rooms','area_id','user_id')
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
                            ->get()
                        ],200);
                    }
                    else{
                        return response()->json([
                            'data'=>Hotel::with(['images','area:id,country_id,name','country:id,name','user'])
                            ->select('id','name','number_rooms','stars','area_id','user_id','country_id')
                            ->where('name','like','%'.$request->name.'%')
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
                            ->get()
                        ],200);
                    }
                    else{
                        return response()->json([
                            'data'=>Hotel::with(['images','country:id,name','area:id,country_id,name','user'])
                            ->select('id','name','number_rooms','stars','area_id','country_id','user_id')
                            ->where('stars','like','%'.$request->stars.'%')
                            ->get()
                        ],200);
                    }
    }

    public function destroy($id)
    {
        if(auth()->user()->id !=Hotel::where('id',$id)->first()->user_id ){
            return response()->json([
                'message'=>'you dont have this hotel'
            ]);
           }
        try{
            Hotel::findOrFail($id)->delete();
            }catch(\Exception $exception){
                return response()->json([
                    'message'=>'Not Found'
                ],404);
            }
            return response()->json([
                'message'=>'delete done!!'
            ],200);
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

    // public function hotel_book(Request $request){
    //     $validatedData =Validator::make($request->all(),[
    //         'count'=>'required|decimal',]);
    //         if($request->count> $hotel['rooms']){
    //             return response()->json([
    //                 'message'=>'this count is up of room_count'
    //             ]);
    //         }
    //         else{
    //             $request->count
    //         }


    // }


}
