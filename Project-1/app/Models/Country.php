<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Country extends Model
{
    use HasFactory;
    protected $fillable=['name'];
    public function areas(): HasMany
    {
        return $this->hasMany(area::class);
    }

    public function airports()
    {
        return $this->hasMany(Airport::class,'country_id');
    }

    public function trips(): HasManyThrough
    {
        return $this->hasManyThrough(Plane::class, Airport::class);
    }
    public function source_bookings(): HasMany
    {
        return $this->hasMany(Booking::class,'source_trip_id');
    }

    public function destination_bookings(): HasMany
    {
        return $this->hasMany(Booking::class,'destination_trip_id');
    }
}
