<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaceCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'place_id'
    ] ;

    public function place()
    {
        return $this->belongsTo(Place::class,'place_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }

}
