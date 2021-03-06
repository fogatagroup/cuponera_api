<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{

    /**
     * @var string
     */
    protected $table = 'sales_detail';

    //public $timestamps = false;

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_update';
}
