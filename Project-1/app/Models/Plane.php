<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plane extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'number_of_seats',
        'status',
        'ticket_price',
        'airport_id',
    ] ;
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    public function airport(): BelongsTo
    {
        return $this->belongsTo(Airport::class,'airport_id');
    }

    public function tripss():HasMany
    {
        return $this->hasMany(PlaneTrip::class,'plane_id');
    }
   

}
