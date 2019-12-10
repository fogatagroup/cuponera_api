<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const STATUSES = [
        'created' => 'created',
        'reversed' => 'reversed',
        'completed' => 'completed',
        'cancelled' => 'cancelled'
    ];
}
