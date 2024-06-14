<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\SearchCategoryRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Models\Category;
use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $category=Category::get();
        return response()->json([
            'data'=>$category
        ],200);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {

        $category= Category::Create([
            'name'=>$request->name,
        ]);
        return response()->json([
            'message'=>trans('global.add'),
            'data'=>$category
            ],200);

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try{
        $category=Category::findOrFail($id);
        }catch(\Exception $exception){
            return response()->json([
                'message'=>trans('global.notfound')
            ],404);
        }
        return response()->json([
          'data'=>$category
        ],200);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update($id,SearchCategoryRequest $request)
    {
        try{
        $country=Category::findOrFail($id);
        }catch(\Exception $exception){
            return response()->json([
                'message'=>trans('global.notfound')
            ],404);
        }
        $country->name=$request->name;
        $country->save();

        return response()->json([
            'message'=>trans('global.update'),
            'data'=>$country
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
        Category::findOrFail($id)->delete();
        }catch(\Exception $e){
            return response()->json([
                'message'=>trans('global.notfound')
            ],404);
        }
        return response()->json([
            'message'=>trans('global.delete')
        ],200);
    }

    public function search(SearchCategoryRequest $request)
    {
        $category=Category::where('name','like','%'.$request->name.'%')->get();
        return response()->json([
            'data'=>$category
        ],200);
    }
}
