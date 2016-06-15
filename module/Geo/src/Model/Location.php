<?php

namespace Rox\Geo\Model;

use Rox\Core\Model\AbstractModel;

class Location extends AbstractModel
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
    protected $ormRelationships = [
        'country',
    ];

    public function country()
    {
        return $this->hasOne(Country::class, 'country', 'countryCode');
    }

    public function getCountryCodeAttribute()
    {
        return $this->attributes['country'];
    }
}
