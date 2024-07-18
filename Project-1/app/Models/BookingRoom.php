<?php

namespace App\Models;

use Google\Service\HangoutsChat\Resource\Rooms;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function roomss():BelongsTo
    {
        return $this->belongsTo(Room::class,'room_id');
    }
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
