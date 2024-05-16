<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Favorite extends Model
{
    use HasFactory;
    protected $fillable = [
        'place_id','user_id'
    ] ;

    public function place():BelongsTo
    {
        return $this->belongsTo(Place::class,'place_id');
    }
   
}
