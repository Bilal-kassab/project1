<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaticTripRoom extends Model
{
    use HasFactory;
    protected $fillable=[
        'booking_static_trip_id',
        'room_id',
    ];


}
