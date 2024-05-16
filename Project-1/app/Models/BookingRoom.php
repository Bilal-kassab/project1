<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'room_id',
        'current_price',
        'start_date',
        'end_date'
    ] ;
    public function books():HasMany
    {
        return $this->hasMany(Booking::class,'book_id');
    }
    

    public function rooms():HasMany
    {
        return $this->hasMany(Booking::class,'room_id');
    }
}
