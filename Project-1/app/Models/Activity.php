<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Activity extends Model
{
    use HasFactory;

    protected $fillable=[
        'name'
    ];
    protected $hidden=[
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function bookings():BelongsToMany
    {
       return $this->belongsToMany(Booking::class,'activity_books','book_id');
    }


}
