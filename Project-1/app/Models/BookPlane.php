<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookPlane extends Model
{
    use HasFactory;
    protected $fillable=[
        'plane_trip_id',
        'book_id',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function plane_trip():BelongsToMany
    {
        return $this->belongsToMany(PlaneTrip::class,'plane_trip_id','book_id');
    }

    public function planetrip():BelongsTo
    {
        return $this->belongsTo(PlaneTrip::class,'plane_trip_id');
    }
    public function books():BelongsTo
    {
        return $this->belongsTo(Booking::class,'book_id');
    }
}
