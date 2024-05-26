<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use HasFactory;
    protected $fillable=['name','country_id'];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(country::class);
    }

    public function places(): HasMany
    {
        return $this->hasMany(Place::class,'area_id')->where('visible',true);
    }

    public function airports(): HasMany
    {
        return $this->hasMany(Airport::class,'area_id');
    }
}
