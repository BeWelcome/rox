<?php

namespace Rox\Geo\Model;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    /**
     * @var boolean
     */
    public $timestamps = false;

    /**
     * @var string
     */
    public $table = 'geonames';

    /**
     * @var array
     */
    protected $relationships = [
        'country',
    ];

    public function country()
    {
        return $this->hasOne(Country::class, 'country', 'countryCode');
    }

    public function getAttribute($key)
    {
        // The Eloquent implementation of getAttribute will first return the
        // attribute of $key before checking if it has a relationship.
        // We want the opposite of this because we want to define the 'country'
        // key as a relationship to the geoname entity, even though the location
        // table defines a 'country' column.
        if (in_array($key, $this->relationships, true)) {
            return $this->getRelationValue($key);
        }

        return parent::getAttribute($key);
    }

    public function getCountryCodeAttribute()
    {
        return $this->attributes['country'];
    }

    public function __isset($key)
    {
        return parent::__isset($key) || in_array($key, $this->relationships, true);
    }
}
