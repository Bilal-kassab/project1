<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Lang;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'title', 'body'];

    public function user():BelongsTo
    {
       return $this->belongsTo(User::class,'user_id');
    }
}
