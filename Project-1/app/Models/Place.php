<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Place extends Model
{
    use HasFactory;
    protected $fillable = [
    'name',
    'place_price',
    'text',
    'area_id',
    'category_id',
    'visible',
    'lat',
    'long'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot',
    ];

    public function categories(): BelongsToMany
    {
        return $this->BelongsToMany(Category::class,'place_categories');
    }

    public function scopeVisible(Builder $query): void
    {
        $query->where('visible', '=', true);
    }
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class,'area_id');
    }

    public function images():HasMany
    {
        return $this->hasMany(PlaceImage::class,'place_id');
    }

    public function users():BelongsToMany
    {
        return $this->belongsToMany(User::class,'favorites','place_id','user_id');
    }
    public function userComments():BelongsToMany
    {
        return $this->belongsToMany(User::class,'comments','place_id','user_id');
    }
    public function comments():HasMany
    {
        return $this->hasMany(Comment::class,'place_id');
    }

    public function favorites():HasMany
    {
        return $this->hasMany(Favorite::class,'place_id');
    }
    public function bookings():BelongsToMany
    {
        return $this->belongsToMany(Booking::class,'book_places','place_id','book_id');
    }


}
