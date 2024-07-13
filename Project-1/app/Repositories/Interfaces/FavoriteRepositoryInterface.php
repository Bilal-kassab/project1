<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

Interface FavoriteRepositoryInterface
{
    public function favoritePlaces();
    public function placesCategories($places);
    public function placesDependingOnCategories($categoryIds);
}
