<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfferProdDetail extends Model
{

    /**
     * @var string
     */
    protected $table = 'offerprod_detail';

    //public $timestamps = false;

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_update';
}
