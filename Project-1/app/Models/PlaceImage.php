<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceImage extends Model
{
    use HasFactory;
    protected $fillable = [
    'place_id',
    'image',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function place():BelongsTo
    {
        return $this->belongsTo(Place::class,'place_id');
    }

}
