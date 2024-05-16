<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirportImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'plane_id',
        'image'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function plane(): BelongsTo
    {
        return $this->belongsTo(Plane::class,'plane_id');
    }
}
