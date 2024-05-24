<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable=[
        'place_id',
        'user_id',
        'comment'
    ];

    protected $hidden=['updated_at'];
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

}
