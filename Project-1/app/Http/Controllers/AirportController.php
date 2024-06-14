<?php

namespace App\Http\Controllers;

use App\Http\Requests\Airport\SearchAirportRequest;
use App\Http\Requests\Airport\StoreAirportRequest;
use App\Http\Requests\Airport\UpdateAirportRequest;
use App\Models\Airport;
use App\Models\AirportImage;
use App\Models\Area;
use App\Models\Country;
use App\Models\Plane;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Database\Eloquent\Builder;
class AirportController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:Admin|Airport admin', ['only'=> ['store','update','updateExistAirportImage','addAirportImage','getMyAirport']]);
        $this->middleware('role:Super Admin|Admin|Airport admin', ['only'=> ['getAirportDetails']]);
        $this->middleware('role:Super Admin', ['only'=> ['allAirport']]);
    }

    public function getMyAirport()
    {
        return response()->json([
            'data'=> Airport::airportWithAdmin()
                                ->where('user_id',auth()->id())
                                ->get(),
         ],200);
    }

    public function store(StoreAirportRequest $request):JsonResponse
    {

        $airport= Airport::create([
            'name'=> $request->name,
            'user_id'=> auth()->id(),
            'country_id'=>Area::find($request->area_id)['country_id'],
            'area_id'=> $request->area_id,
        ]);

        if($request->hasFile('images')){
            foreach ($request->file('images') as $imagefile){
                $images = new AirportImage;
                $images->airport_id= $airport->id;
                $image_name=time() . '.' . $imagefile->getClientOriginalExtension();
                $imagefile->move('AirportImage/',$image_name);
                $images->image = "AirportImage/".$image_name;
                $images->save();
            }
        }


        return response()->json([
            'message'=> trans('global.add'),
            'data'=>Airport::with('country:id,name','area:id,name','user:id,name,email,image,position')
                             ->select('id','name','user_id','area_id','country_id')
                             ->where('id',$airport->id)
                             ->get(),
        ],200);
    }

    public function update(UpdateAirportRequest $request, $id): JsonResponse
    {
        try{
            $airport= Airport::findOrFail($id);
            if(auth()->id() != $airport->user_id)
            {
                return response()->json([
                    'message'=>trans('global.not-permission')
                ],403);
            }
        }catch(\Exception $e){
            return response()->json([
                'message'=> trans('global.notfound')
            ],404);
        }
          $airport->name = $request->name??$airport['name'];
          //$airport->user_id = $request->user_id;
          $airport->country_id=Area::find($request->area_id??$airport['area_id'])['country_id'];
          $airport->area_id = $request->area_id??$airport['area_id'];
          $airport->save();
          return response()->json([
            'message'=> trans('global.update'),
            'data'=>Airport::with('country:id,name','area:id,name','user:id,name,email,image,position')
                            ->select('id','name','user_id','area_id','country_id')
                            ->where('id',$airport->id)
                            ->first(),
          ],200);
    }

    public function updateExistAirportImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'image_id'=>'required|numeric|exists:airport_images,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }

        try{
            $airport_image = AirportImage::findOrFail($request->image_id);
            $airport= Airport::findOrFail($airport_image->airport_id);
            if(auth()->id() != $airport->user_id)
            {
                return response()->json([
                    'message'=>trans('global.not-permission')
                ],200);
            }
         }catch(\Exception $e){
            return response()->json([
                'message'=> trans('global.notfound')
            ],404);
         }

         if(File::exists($airport_image->image))
         {
             File::delete($airport_image->image);
         }

         $image = $request->file('image');
         $image_name=time() . '.' . $image->getClientOriginalExtension();
         $image->move('AirportImage/',$image_name);
         $airport_image->image="AirportImage/".$image_name;
         $airport_image->save();

         return response()->json([
            'message'=> trans('global.update'),
            'data'=>Airport::with('images:id,airport_id,image','country:id,name','area:id,name','user:id,name,email,image,position')
                             ->select('id','name','user_id','area_id','country_id')
                             ->where('id',$airport_image->airport_id)
                             ->get(),
          ],200);

    }

    public function addAirportImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images'=> 'present|array|min:1',
            'images.*' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'airport_id'=>'required|numeric|exists:airports,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }
        $airport= Airport::findOrFail($request->airport_id);
        if(auth()->id() != $airport->user_id)
        {
            return response()->json([
                'message'=>trans('global.not-permission')
            ],200);
        }
        foreach ($request->file('images') as $imagefile){
            $images = new AirportImage;
            $images->airport_id= $request->airport_id;
            $image_name=time() . '.' . $imagefile->getClientOriginalExtension();
            $imagefile->move('AirportImage/',$image_name);
            $images->image = "AirportImage/".$image_name;
            $images->save();
        }

        return response()->json([
            'data'=> Airport::with('images:id,airport_id,image','country:id,name','area:id,name','user:id,name,email,image,position')
                            ->select('id','name','user_id','area_id','country_id')
                            ->where('id',$request->airport_id)
                            ->get(),
        ],200);


    }

    public function destroy($id): JsonResponse
    {
        try{
            $airport= Airport::findOrFail($id);
        }catch(\Exception $e){
            return response()->json([
                'message'=> trans('global.notfound'),
            ],404);
        }
        $airport->delete();
        return response()->json([
            'message'=> trans('global.delete')
        ]);
    }

    public function show(Request $request,$id): JsonResponse
    {
        try{
            $airport= Airport::visible()->findOrFail($id);
        }catch(\Exception $e){
            return response()->json([
                'message'=> trans('global.notfound'),
            ],404);
        }
        if($request->user()->hasRole('User')) {
            return response()->json([
            'data'=> Airport::airportWithoutAdmin()
                                ->visible()
                                ->where('id',$airport->id)
                                ->get(),
            ],200);
        }
         return response()->json([
            'data'=> Airport::airportWithAdmin()->visible()
                                ->where('id',$airport->id)
                                ->get(),
         ],200);

    }

    public function search(SearchAirportRequest $request): JsonResponse
    {

       if($request->user()->hasRole('User')) {
            return response()->json([
            'data'=> Airport::airportWithoutAdmin()
                            ->visible()
                            ->where('name','like','%'.$request->name.'%')->get()
            ],200);
        }

         return response()->json([
            'data'=>  Airport::airportWithAdmin()
                                ->visible()
                                ->where('name','like','%'.$request->name.'%')->get(),
         ],200);

    }

    public function getAllCountryAirports(Request $request,$id):JsonResponse
    {
        try{
            $country=Country::select('id','name')->findOrFail($id);

        }catch(\Exception $e){
            return response()->json([
                'message'=> trans('global.notfound'),
            ],404);
        }
        if($request->user()->hasRole('User')) {
            return response()->json([
            'data'=> Airport::airportWithoutAdmin()->visible()
                                ->where('country_id',$country->id)->get()
            ],200);
        }

         return response()->json([
            'data'=> Airport::airportWithAdmin()->visible()
                            ->where('country_id',$country->id)->get()
            // 'data'=> Country::whereRelation('airports','visible',true)->whereHas('areas.airports')->with(['areas:id,country_id,name','areas.airports' => function (Builder $query) {
            //                 $query->select('id','name','country_id','area_id','user_id','visible');
            //             },'airports.user:id,name,email,image,position']) ->select('id','name')
            //                 ->where('id',$country->id)->get()
         ],200);
    }

    public function getAllAreaAirports(Request $request,$id):JsonResponse
    {
        try{
            $area=Area::findOrFail($id);
        }catch(\Exception $e){
            return response()->json([
                'message'=> trans('global.notfound')
            ],404);
        }

        if($request->user()->hasRole('User')) {
            return response()->json([
                'data'=>Area::whereRelation('airports','visible',true)->whereHas('airports')->with(['country:id,name','airports' => function (Builder $query) {
                    $query->select('id','name','country_id','area_id','user_id','visible');
                }]) ->select('id','name','country_id')
                    ->where('id',$area->id)->get()
            ],200);
        }
       // 'airports.user:id,name,email,image,position'
         return response()->json([
            'data'=> Area::whereRelation('airports','visible',true)->whereHas('airports')->with(['country:id,name','airports' => function (Builder $query) {
                            $query->select('id','name','country_id','area_id','user_id','visible');
                        },'airports.user:id,name,email,image,position']) ->select('id','name','country_id')
                            ->where('id',$area->id)->get()
         ],200);
    }

    public function getAirportDetails($id):JsonResponse
    {
        try{
            $airport=Airport::airportWithoutAdmin()->visible()->select('id','name','area_id','country_id','visible')->findOrFail($id);
            $planes=Plane::where('airport_id',$airport->id)->get()->count();
        }catch(\Exception $e){
            return response()->json([
                'message'=> trans('global.notfound')
            ],404);
        }

        $data=[
            'airport'=>$airport,
            'plane_count'=>$planes
        ];
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function allAirport()
    {
        return response()->json([
            'data'=> Airport::airportWithAdmin()
                                ->get(),
         ],200);
    }



}
