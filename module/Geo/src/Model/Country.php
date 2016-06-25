<?php

namespace Rox\Geo\Model;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    /**
     * @var string
     */
    public $table = 'geonamescountries';

    /**
     * @var boolean
     */
    public $timestamps = false;
}
