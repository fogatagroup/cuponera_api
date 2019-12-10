<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{

    /**
     * @var string
     */
    protected $table = 'user_type';

    //public $timestamps = false;

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_update';
}
