<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class PlaneTrip extends Model
{
    use HasFactory;

    protected $fillable = [
        'plane_id',
        'airport_source_id',
        'airport_destination_id',
        'country_source_id',
        'country_destination_id',
        'current_price',
        'available_seats',
        'flight_date',
        'landing_date',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'laravel_through_key',
        'pivot'
    ];

    public function scopeGetTripDetails(Builder $query): void
    {
        $query->with('plane:id,name','country_source:id,name','country_destination:id,name','airport_source:id,name','airport_destination:id,name');
    }
    public function scopeDetails(Builder $query): void
    {
        $query->with('plane:id,name','country_source:id,name','country_destination:id,name','airport_source:id,name','airport_destination:id,name');
    }

    public function scopeWhereDate(Builder $query,$data): void
    {
        $query->where('flight_date','>=',$data['flight_date'])->Where('landing_date','<=',$data['landing_date']);
    }
    public function plane():BelongsTo
    {
        return $this->belongsTo(Plane::class,'plane_id');
    }

    public function airport_source():BelongsTo
    {
        return $this->belongsTo(Airport::class,'airport_source_id');
    }

    public function airport_destination():BelongsTo
    {
        return $this->belongsTo(Airport::class,'airport_destination_id');
    }

    public function country_destination():BelongsTo
    {
        return $this->belongsTo(Country::class,'country_destination_id');
    }
    public function country_source():BelongsTo
    {
        return $this->belongsTo(Country::class,'country_source_id');
    }

    public function booking():BelongsToMany
    {
        return $this->belongsToMany(Booking::class,'book_planes','plane_trip_id','book_id');
    }

}
