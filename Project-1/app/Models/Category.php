<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    protected $fillable=['name'];

    // public function places(): hasMany
    // {
    //     return $this->hasMany(PlaceCategory::class,'place_id');
    // }

    public function places(): BelongsToMany
    {
        return $this->BelongsToMany(Place::class,'place_categories');
    }
    

}
