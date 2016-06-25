<?php

namespace Rox\Geo\Model;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    public $table = 'geonamescountries';
}
