<?php

namespace App\Http\Controllers;

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
    public function store(Request $request)
    {

        
        $validatedData = Validator::make($request->all(),[
            'name' => ['required', 'unique:countries', 'string'],
        ]);
        if( $validatedData->fails() ){
            return response()->json([
                'message'=> $validatedData->errors()->first(),
            ],422);
        }
        $country= Country::Create([
            'name'=>$request->name,
        ]);
        return response()->json([
            'message'=>'succesfully',
            'data'=>$country
            ],200);
    
        /*
        $country = new country();
        $country->name = $request->name;
        $country->save();
        */
   
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
                'message'=>'Not Found'
            ],404);
        }
       
      return response()->json([
        'data'=>$country
      ],200);
    }

   
    /**
     * Update the specified resource in storage.
     */
    public function update($id,Request $request)
    {
        try{
        $country=Country::findOrFail($id);
        }catch(\Exception $exception){
            return response()->json([
                'message'=>'Not Found'
            ],404);
        }
        $validatedData = validator::make($request->all(),[
            'name' => ['required', 'unique:countries', 'string']
        ]);
        if( $validatedData->fails() ){
            return response()->json([
                'message'=> $validatedData->errors()->first(),
            ],422);
        }
        $country->name=$request->name;
        $country->save();
        return response()->json([
            'message'=>'update succesfully',
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
                'message'=>'Not Found'
            ],404);
        }
        
        return response()->json([
            'message'=>'delete done!!'
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
        $country=Country::where('name','like','%'.$request->name.'%')->get();

        return response()->json([
            'data'=>$country
        ],200);
    }
}
