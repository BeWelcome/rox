<?php
namespace Rox\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public $timestamps = false;

    public $table = 'geonamescountries';
}