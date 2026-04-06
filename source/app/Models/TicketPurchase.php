<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketPurchase extends Model
{
    protected $fillable = [
        'user_id',
        'race_id',
        'ticket_type_id',
        'buy_type_id',
        'selections',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'selections' => 'array',
        ];
    }
}
