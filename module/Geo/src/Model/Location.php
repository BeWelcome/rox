<?php

namespace Rox\Geo\Model;

use Rox\Core\Model\AbstractModel;

class Location extends AbstractModel
{
    /**
     * @var string
     */
    public $table = 'geonames';

    /**
     * @var boolean
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $primaryKey = 'geonameid';

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
