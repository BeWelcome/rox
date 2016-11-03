<?php

namespace Rox\Trip\Model;

use Rox\Core\Model\AbstractModel;
use Rox\Geo\Model\Location;

class SubTrip extends AbstractModel
{
    /**
     * No guarded properties
     */
    protected $guarded = [];

    /**
     * No timestamps necessary for subtrips
     */
    public $timestamps = false;

    /**
     * Get the trip for this subtrip.
     */
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'geonameId');
    }
}