<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'number_of_people',
        'start_date',
        'trip_capacity',
        'end_date',
        'stars',
        'trip_note',
        'type'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
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

    public function scopeAvailableRooms($query)
    {
        return $query->withCount(['rooms' => function ($query) {
                $query->where('user_id',null);
        } ]);
    }

    public function plane_trips():BelongsToMany
    {
        return $this->belongsToMany(PlaneTrip::class,'book_planes','book_id','plane_trip_id');
    }
    public function places():BelongsToMany
    {
        return $this->belongsToMany(Place::class,'book_places','book_id','place_id');
    }

    public function activities():BelongsToMany
    {
       return $this->belongsToMany(Activity::class,'activity_books','activity_id');
    }

}

