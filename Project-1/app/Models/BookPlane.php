<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        return $this->belongsToMany(PlaneTrip::class,'plane_trip_id');
    }
}