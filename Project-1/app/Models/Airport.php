<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Airport extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'area_id',
        'country_id',
    ] ;
    protected $hidden = [
        'created_at',
        'updated_at',
        'laravel_through_key'
    ];


    public function scopeVisible(Builder $query): void
    {
        $query->where('visible', '=', true);
    }

    public function scopeAirportWithAdmin(Builder $query): void
    {
            $query->with('country:id,name','area:id,name','user:id,name,email,image,position')
                    ->select('id','name','user_id','area_id','country_id');
    }

    public function scopeAirportWithoutAdmin(Builder $query): void
    {
            $query->with('country:id,name','area:id,name')
                    ->select('id','name','user_id','area_id','country_id');
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class,'area_id');
    }
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class,'country_id');
    }


    public function planes(): HasMany
    {
        return $this->hasMany(Plane::class,'airport_id');
    }

    public function planesDestination (): HasMany
    {
        return $this->hasMany(Plane::class,'airport_destination_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(AirportImage::class,'airport_id');
    }

    public function trips(): HasManyThrough
    {
        return $this->hasManyThrough(PlaneTrip::class, Plane::class);
    }
}
