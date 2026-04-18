<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    protected $fillable = [
        'name',
    ];

    /** @return HasMany<Race, $this> */
    public function races(): HasMany
    {
        return $this->hasMany(Race::class);
    }
}
