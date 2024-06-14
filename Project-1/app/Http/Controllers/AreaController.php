<?php

namespace App\Http\Controllers;

use App\Http\Requests\Area\GetAreasForCountryRequest;
use App\Http\Requests\Area\SearchAreaRequest;
use App\Http\Requests\Area\StoreAreaRequest;
use App\Http\Requests\Area\UpdateAreaRequest;
use App\Models\Area;
use App\Models\Country;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $area=Area::get();
        return response()->json([
            'data'=>$area
        ],200);

    }

    public function getAreasForCountry(GetAreasForCountryRequest $request)
    {
        $areas=Country::query()
                        ->with('areas:id,name,country_id')
                        ->where('id',$request->country_id)
                        ->select('id','name')
                        ->get();

        return response()->json([
            'data'=>$areas
        ],200);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAreaRequest $request)
    {
        $area= Area::Create([
            'name'=>$request->name,
            'country_id'=>$request->country_id
        ]);
        return response()->json([
            'message'=>trans('global.add'),
            'data'=>Area::with('country:id,name')
                        ->where('id',$area->id)
                        ->select('id','name','country_id')
                        ->first()
            ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try{
        $area=Area::findOrFail($id);}
        catch(\Exception $exception){
            return response()->json([
                'message'=>trans('global.notfound')
            ],404);
        }
        return response()->json([
          'data'=>Area::with(['country'])->where('id',$area->id)->get()
        ],200);

    }


    /**
     * Update the specified resource in storage.
     */
    public function update($id,UpdateAreaRequest $request)
    {
        try{
        $area=Area::findOrFail($id);
        }catch(\Exception $exception){
            return response()->json([
                'message'=>trans('global.notfound')
            ],404);
        }
        $area->name=$request->name;
        $area->country_id=$request->country_id;
        $area->save();
        return response()->json([
            'message'=>trans('global.update'),
            'data'=>$area
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
        Area::findOrFail($id)->delete();
        }catch(\Exception $exception){
            return response()->json([
                'message'=>trans('global.notfound')
            ],404);
        }
        return response()->json([
            'message'=>trans('global.delete')
        ],200);
    }

    public function search(SearchAreaRequest $request)
    {
        $area=Area::where('name','like','%'.$request->name.'%')->get();

        return response()->json([
            'data'=>$area
        ],200);
    }
}
