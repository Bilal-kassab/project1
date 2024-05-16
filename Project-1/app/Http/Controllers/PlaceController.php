<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Category;
use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceCategory;
use App\Models\PlaceImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class PlaceController extends Controller
{
    public function index()
    {
        return response()->json([
            'data'=>Place::with('images:id,place_id,image','categories:id,name','area:id,name,country_id','area.country:id,name')
                           ->get()
        ]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name'=>'required|string|unique:places',
            'area_id'=>'required|numeric|exists:areas,id',
            'category_ids'=> 'present|array',
            'category_ids.*'=> 'required|numeric|exists:categories,id',
            'place_price'=> 'required|numeric|max:10000',
            'text'=> 'string|max:1000',
            'images'=> 'array',
            'images.*' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);

        if( $validator->fails() ){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }
        $place = Place::create([
            'name'=> $request->name,
            'text'=> $request->text,
            'place_price'=> $request->place_price,
            'area_id'=> $request->area_id,
        ]);

        foreach( $request->category_ids as $category_id ){
            PlaceCategory::create([
                'place_id'=> $place->id,
                'category_id'=> $category_id
            ]);
        }

        if($request->hasFile('images')){
            foreach ($request->file('images') as $imagefile){
                $images = new PlaceImage;
                $images->place_id= $place->id;
                $image_name=time() . '.' . $imagefile->getClientOriginalExtension();
                $imagefile->move('PlaceImages/',$image_name);
                $images->image = "PlaceImages/".$image_name;
                $images->save();
            }
        }

        return response()->json([
            // 'data'=> Place::with(['images' => function ($q) {
            //     $q->select('id', 'image');
            // }])
            // ->where('id', $place->id)
            // ->get()
            'data'=>Place::with(['images:id,place_id,image','categories:id,name','area:id,name,country_id','area.country:id,name'])
                    ->where('id', $place->id)->get()
        ],200);
    }

    public function updatePlace(Request $request,$id)
    {

        $validator = Validator::make($request->all(), [
            'name'=>'required|string|unique:places',
            'area_id'=>'required|numeric|exists:areas,id',
            'category_ids'=> 'present|array',
            'category_ids.*'=> 'required|numeric|exists:categories,id',
            'place_price'=> 'required|numeric|max:10000',
            'text'=> 'required|string|max:1000',
        ]);

        if( $validator->fails() ){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }

        try{
            $place = Place::findOrFail($id);

         }catch(\Exception $e){
            return response()->json([
                'message'=> 'Not found'
            ],404);
         }
         $place->name = $request->name;
         $place->area_id=$request->area_id;
         $place->place_price = $request->place_price;
         $place->text = $request->text;
         $place->save();

        $place_categories=PlaceCategory::where('place_id',$place->id)->get();
         foreach($place_categories as $place_category){
            $place_category->delete();
         }

         foreach($request->category_ids as $category_id ){

            PlaceCategory::create([
                'place_id'=> $place->id,
                'category_id'=> $category_id
            ]);
        }


        return response()->json([
            'message'=>'updated successfully',
            'data'=>Place::with(['images','categories:id,name'])
                          ->where('id',$id)
                          ->select('id','name','place_price','text','area_id')
                          ->get(),
        ],200);
    }

   /* public function updatePlaceImage(Request $request){
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'place_id'=>'required|numeric|exists:places,id',
            'image_id'=>'required|numeric|exists:place_images,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }

        try{
        $place_image = PlaceImage::findOrFail($request->image_id);
        $image = $request->file('image');
        $image_name=time() . '.' . $image->getClientOriginalExtension();
        $image->move('PlaceImages/',$image_name);

       // if(!$place_image->isEmpty()){
            if(File::exists($place_image->image))
            {
                File::delete($place_image->image);
            }
             $place_image->image="PlaceImages/".$image_name;
             $place_image->save();
        //}
        //else{

        //}
    }catch(\Exception $e){
        //    return response()->json([
        //        'message'=> 'Not found'
        //    ],404);
        PlaceImage::create([
            'image'=>"PlaceImages/".$image_name,
            'place_id'=>$request->place_id
        ]);
    }



        $data=[
            'id'=>$place_image->id,
            'place_id'=>$place_image->place_id,
            'place'=>Place::where('id',$place_image->place_id)->pluck('name'),
            'image'=> $place_image->image,
            'updated_at'=>$place_image->updated_at
        ];
        return response()->json([
            'message'=>'photo updated successfully',
            'data'=>$data,
        ],200);

    }
*/
    // عدل كل صور المعالم دفعة وحدة
    //updatePlace شوف تابع

    public function updateExistPlaceImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'image_id'=>'required|numeric|exists:place_images,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }

        try{
            $place_image = PlaceImage::findOrFail($request->image_id);
         }catch(\Exception $e){
            return response()->json([
                'message'=> 'Not found'
            ],404);
         }



        if(File::exists($place_image->image))
        {
            File::delete($place_image->image);
        }

        $image = $request->file('image');
        $image_name=time() . '.' . $image->getClientOriginalExtension();
        $image->move('PlaceImages/',$image_name);
        $place_image->image="PlaceImages/".$image_name;
        $place_image->save();

        $data=[
            'id'=>$place_image->id,
            'place_id'=>$place_image->place_id,
            'place'=>Place::where('id',$place_image->place_id)->pluck('name'),
            'image'=> $place_image->image,
            'updated_at'=>$place_image->updated_at
        ];
        return response()->json([
            'message'=>'photo updated successfully',
            'data'=>Place::with(['images','categories:id,name'])
                          ->where('id',$place_image->place_id)
                          ->select('id','name','place_price','text','area_id')
                          ->get(),
        ],200);

    }


    public function addPlaceImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images'=> 'present|array|min:1',
            'images.*' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'place_id'=>'required|numeric|exists:places,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }

        foreach ($request->file('images') as $imagefile){
            $images = new PlaceImage;
            $images->place_id= $request->place_id;
            $image_name=time() . '.' . $imagefile->getClientOriginalExtension();
            $imagefile->move('PlaceImages/',$image_name);
            $images->image = "PlaceImages/".$image_name;
            $images->save();
        }

        return response()->json([
            "message"=> "Image Added successfully",
            'data'=>Place::with(['images','categories:id,name'])
                          ->where('id',$request->place_id)
                          ->select('id','name','place_price','text','area_id')
                          ->get(),
        ]);

    }

    public function show($id)
    {

        try{
            $place= Place::with(['images','categories:id,name','area:id,country_id,name','area.country:id,name'])
                            ->select('id','name','place_price','text','area_id')
                            ->findOrFail($id);
         }catch(\Exception $e){
            return response()->json([
                'message'=> 'Not found'
            ],404);
         }

        return response()->json([
            'data'=> $place
        ],200);
    }

    public function placesDependingOnArea($id)
    {
        try{
                $places=Area::with(['country:id,name','places:id,name,place_price,text,area_id','places.categories:id,name'])
                            ->select('id','name','country_id')
                            ->findOrFail($id);
        }catch(\Exception $e){
            return response()->json([
                'message'=> 'Not Found'
            ],404);
        }

        return response()->json([
            'data'=> $places
        ],200);
    }

    public function placesDependingOnCountry($id)
    {

        try{
            $places=Country::with(['areas:id,name,country_id','areas.places:id,name,place_price,text,area_id','areas.places.categories:id,name'])
                            ->select('id','name')
                            ->findOrFail($id);
        }catch(\Exception $e){
            return response()->json([
                'message'=>'Not found',
            ],404);
        }
        return response()->json([
            'data'=> $places
        ],200);
    }

    public function placesDependingOnCategory($id)
    {
        try{//
            $places=Category::with(['places:id,name,place_price,text,area_id','places.images:id,image','places.area:id,name,country_id','places.area.country:id,name'])
                                   ->where('id','=', $id)
                                   ->select('id','name')
                                   ->get();
        }catch(\Exception $e){
            return response()->json([
                'message'=> $e->getMessage(),
            ]);
        }
        return response()->json([
            'data'=> $places
        ],200);
    }

    public function placesDependingOnPosition()
    {

        if(auth()->user()->position==null){
            return response()->json([
                'message'=> 'Update your position'
            ]);
        }
        try{
        $places=Country::with(['areas:id,name,country_id','areas.places:id,name,place_price,text,area_id','areas.places.images:id,image','areas.places.categories:id,name'])
                ->select('id','name')
                ->findOrFail(auth()->user()->position);
        }
        catch(\Exception $e){
            return response()->json([
                'message'=>'your position is NULL'
            ]);
        }
        return response()->json([
            'data'=> $places
        ],200);
    }

    public function search(Request $request){

        $validatedData = Validator::make($request->all(),[
            'name' => ['required','string'],
        ]);
        if( $validatedData->fails() ){
            return response()->json([
                'message'=> $validatedData->errors()->first(),
            ],422);
        }
        $place=Place::with(['images','categories:id,name','area:id,country_id,name','area.country:id,name'])
                     ->select('id','name','place_price','text','area_id')
                    ->where('name','like','%'.$request->name.'%')->get();

        return response()->json([
            'data'=>$place
        ],200);
    }
}
