<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable=['name','stars','area_id','number_rooms','user_id','country_id'];
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class,'area_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(user::class,'user_id');
    }
    public function rooms():HasMany
    {
        return $this->hasMany(Room::class,'hotel_id');
    }
    public function images():HasMany
    {
        return $this->hasMany(HotelImage::class,'hotel_id');
    }
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class,'country_id');
    }
}
