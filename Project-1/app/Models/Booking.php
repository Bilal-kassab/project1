<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'source_trip_id',
        'destination_trip_id',
        'trip_name',
        'price',
        'new_price',
        'number_of_people',
        'start_date',
        'trip_capacity',
        'end_date',
        'stars',
        'trip_note',
        'type',
    ];
    protected $hidden = [
        // 'created_at',
        // 'updated_at',
        'pivot',
    ];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function source_trip():BelongsTo
    {
        return $this->belongsTo(Country::class,'source_trip_id');
    }
    public function destination_trip():BelongsTo
    {
        return $this->belongsTo(Country::class,'destination_trip_id');
    }
    public function rooms():BelongsToMany
    {
        return $this->belongsToMany(Room::class,'booking_rooms','book_id','room_id');
    }
    public function user_rooms():BelongsToMany
    {
        return $this->belongsToMany(Room::class,'booking_rooms','book_id','room_id')->where('user_id',auth()->id());
    }
    public function scopeAvailableRooms($query)
    {
        return $query->withCount(['rooms' => function ($query) {
                $query->where('user_id',null);
        } ]);
    }
    public function scopeUserRooms($query,$capacity,$uesrID)
    {
        return $query->withCount(['rooms' => function ($query) use($capacity,$uesrID) {
                $query->where('user_id',$uesrID)
                      ->where('capacity',$capacity);
        } ]);
    }

    public function plane_trips():BelongsToMany
    {
        return $this->belongsToMany(PlaneTrip::class,'book_planes','book_id','plane_trip_id');
    }
    public function places():BelongsToMany
    {
        return $this->belongsToMany(Place::class,'book_places','book_id','place_id')->with(['images','area:id,name']);
    }

    public function activities():BelongsToMany
    {
       return $this->belongsToMany(Activity::class,'activity_books','booking_id','activity_id');
    }

    // public function static_trips():BelongsToMany
    // {
    //     return $this->belongsToMany(BookingStaticTrip::class,'booking_static_trips','static_trip_id','user_id');
    // }
    public function bookings():HasMany
    {
        return $this->HasMany(BookingStaticTrip::class,'static_trip_id');
    }
    public function totalBookPrice()
    {
        return $this->bookings()->sum('book_price');
    }

    // public function static_trip_rooms():BelongsToMany
    // {
    //     return $this->rooms()->whereRelation('rooms');
    // }
}

