<?php

namespace App\Http\Controllers;

use App\Http\Requests\Country\SearchCountryRequest;
use App\Http\Requests\Country\StoreCountryRequest;
use App\Http\Requests\Country\UpdateCountryRequest;
use App\Models\Country;
use Carbon\Exceptions\Exception;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\Input;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $country=Country::get();
        return response()->json([
            'data'=>$country
        ],200);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCountryRequest $request)
    {

        $country= Country::Create([
            'name'=>$request->name,
        ]);
        return response()->json([
            'message'=>trans('county.add-country'),
            'data'=>$country
            ],200);

    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try{
      $country=Country::findOrFail($id);
        }catch(\Exception $exception){
            return response()->json([
                'message'=>trans('global.notfound')
            ],404);
        }

      return response()->json([
        'data'=>$country
      ],200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update($id,UpdateCountryRequest $request)
    {
        try{
        $country=Country::findOrFail($id);
        }catch(\Exception $exception){
            return response()->json([
                'message'=>trans('global.notfound')
            ],404);
        }
        $country->name=$request->name;
        $country->save();
        return response()->json([
            'message'=>trans('county.update-country'),
            'data'=>$country
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
            Country::findOrFail($id)->delete();
        }catch(\Exception $exception){
            return response()->json([
                'message'=>trans('global.notfound')
            ],404);
        }

        return response()->json([
            'message'=>trans('county.delete-country')
        ],200);
    }

    public function search(SearchCountryRequest $request){

        $country=Country::where('name','like','%'.$request->name.'%')->get();

        return response()->json([
            'data'=>$country
        ],200);
    }
}
