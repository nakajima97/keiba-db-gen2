<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Race extends Model
{
    protected $fillable = [
        'venue_id',
        'race_date',
        'race_number',
    ];
}
