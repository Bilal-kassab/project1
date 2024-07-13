<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Place;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    private $favorite;

    public function __construct(FavoriteRepositoryInterface $favorite)
    {
        $this->favorite = $favorite;
    }
    public function index(){

        return response()->json([
            "data"=>Favorite::with('place:id,name')
                    ->where('user_id',auth()->user()->id)
                    ->select('id','place_id')
                    ->get(),

        ]);
    }
    public function setFavorite(Request $request){
        $validator = Validator::make($request->all(), [
            'place_id' => 'required|numeric|exists:places,id',
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }
        $favorite=Favorite::query()
                  ->where('user_id', auth()->user()->id)
                  ->where('place_id', $request->place_id)
                  ->first();
        if($favorite){
            return response()->json([
                'message'=> 'this place has already added'
            ],200);
        }
        $favorite = new Favorite();
        $favorite->user_id = auth()->user()->id;
        $favorite->place_id = $request->place_id;
        $favorite->save();
        return response()->json([
            'message'=> 'set favorite successfully',
            'place'=>Place::where('id',$favorite->place_id)->pluck('name'),
        ],200);
    }

    public function deleteFavorite(Request $request){
        $validator = Validator::make($request->all(), [
            'favorite_id' => 'required|numeric|exists:favorites,id',
        ],
        [
            'exists'=> 'Not found',
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }

        $favorite=Favorite::query()
                  ->where('user_id', auth()->user()->id)
                  ->where('id', $request->favorite_id)
                  ->first();
        if(!$favorite){
            return response()->json([
                'message'=> 'this place does not in favorite'
            ],200);
        }
        $favorite->delete();

        return response()->json([
            'message'=> 'removed from favorite successfully '
        ],200);

    }

    public function getSuggest()
    {
        // to get all favorite places
        $favoritePlaces=$this->favorite->favoritePlaces();
        // to get all  categories id from the previous places
        $categoryIds=$this->favorite->placesCategories($favoritePlaces);
        //to get all places id that have a categories from the previous categories
        $places = $this->favorite->placesDependingOnCategories($categoryIds);

        return $places;
    }

}
