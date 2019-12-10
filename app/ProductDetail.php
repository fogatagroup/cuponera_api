<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{

    /**
     * @var string
     */
    protected $table = 'product_detail';

    //public $timestamps = false;

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_update';
}
