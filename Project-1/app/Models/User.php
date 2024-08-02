<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'position',
        'is_approved',
        'point',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        //'pivot'
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected function getDefaultGuardName(): string
    {
        return 'user';
    }
    public function airport(): HasOne
    {
        return $this->hasOne(Airport::class,'user_id');
    }
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class,'user_id');
    }

    public function comments():BelongsToMany
    {
        return $this->belongsToMany(Comment::class,'comments','user_id','place_id');
    }
    public function static_trips():BelongsToMany
    {
        return $this->belongsToMany(BookingStaticTrip::class,'booking_static_trips','user_id','static_trip_id');
    }
    public function myStaticTrip():HasMany
    {
        return $this->HasMany(BookingStaticTrip::class,'user_id');
    }

    public function position()
    {
        return $this->belongsTo(Country::class,'position');
    }

}
