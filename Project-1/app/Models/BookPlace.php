<?php

namespace App\Models;

use App\Http\Controllers\PlaceController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookPlace extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'book_id',
        'place_id',
        'current_price',
        'place_note',
    ] ;
    public function places():HasMany{
        return $this->hasMany(Place::class,'place_id');
    }

    public function books():HasMany{
        return $this->hasMany(Booking::class,'book_id');
    }
    
}
