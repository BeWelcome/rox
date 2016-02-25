<?php

namespace Rox\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    public $timestamps = false;

    public $table = 'geonames';

    public function Country()
    {
        return $this->hasOne('Rox\Models\Country', 'country', 'country');
    }

}