<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'hotel_id',
        'image',
        ];
        protected $hidden = [
            'created_at',
            'updated_at',
        ];
        public function hotel():BelongsTo
        {
            return $this->belongsTo(Hotel::class,'hotel_id');
        }
}
