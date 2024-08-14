<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class RateBooking extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id',
        'booking_id',
        'rate',
    ];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function trip():BelongsTo
    {
        return $this->belongsTo(Booking::class,'booking_id');
    }
}
