<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class BookingStaticTrip extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id',
        'static_trip_id',
        'number_of_friend',
        'book_price',
    ];

    public function static_trip():BelongsTo
    {
        return $this->belongsTo(Booking::class,'static_trip_id');
    }
    public function user():BelongsTo
    {
        return $this->belongsTo(USer::class,'user_id');
    }

    public function rooms():BelongsToMany
    {
        return $this->belongsToMany(Room::class,'static_trip_rooms','booking_static_trip_id','room_id');
    }


    public function scopeRoom($query)
    {
        return $query->whereRelation('static_trip.rooms','user_id',auth()->id())->with('static_trip.rooms');
    }

}
