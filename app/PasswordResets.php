<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordResets extends Model
{
    
    /**
     * @var string
     */
    protected $table = 'password_resets';

    public $timestamps = false;

}
