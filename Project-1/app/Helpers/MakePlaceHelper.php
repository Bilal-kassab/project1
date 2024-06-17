<?php

namespace App\Helpers;

use App\Models\Place;
use App\Models\PlaceCategories;
use App\Models\PlaceCategory;
use App\Models\PlaceImage;

class MakePlaceHelper
{
    public static function makePlace($name,$place_price,$text,$area_id,$category_ids)
    {
        $place=Place::create([
            "name"=> $name,
            "place_price"=> $place_price,
            "area_id"=> $area_id,
            'text'=> $text,
        ]);

        PlaceImage::create([
            'place_id'=>$place->id,
            'image'=>'PlaceImages/1718441623.png'
        ]);

        foreach ($category_ids as $category_id) {
            PlaceCategory::create([
                'place_id'=> $place->id,
                'category_id'=> $category_id
            ]);
        }

        return $place;
    }
}
