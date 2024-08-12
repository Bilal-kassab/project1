<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\Favorite;
use App\Models\Place;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;

class FavoriteRepository implements FavoriteRepositoryInterface
{
    public function favoritePlaces()
    {
       return Favorite::with('place.categories')
                    ->where('user_id',auth()->id())
                    ->select('id','place_id')
                    ->get();
    }

    public function placesCategories($places)
    {
        $categories=[];

        foreach($places as  $place){
            if($place['place']['categories'])
            {
                foreach($place['place']['categories'] as $category){
                    if(!in_array($category['id'],$categories))
                    {
                        array_push($categories,$category['id']);
                    }
                }
            }
        }
        return $categories;
    }

    public function placesDependingOnCategories($categoryIds){

        $places = Place::whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('categories.id', $categoryIds);
        })->get();

        $placeIds=[];

        foreach($places as  $place){
            if(!in_array($place['id'],$placeIds))
            {
                array_push($placeIds,$place['id']);
            }
        }

        $trips = Booking::whereHas('places', function ($query) use ($placeIds) {
            $query->whereIn('places.id', $placeIds);
        })->where('type','static')->get();

        return $trips;

    }


}
