<?php

namespace Rox\Models;


use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    public $table = 'activities';
    
    public $timestamps = false;

    /**
     * Get the member record associated with the log entry.
     */
    public function creator()
    {
        return $this->hasOne('Rox\Member\Model\Member', 'creator', 'id');
    }

    /**
     * Get the attendees of the activity
     */
    public function attendees()
    {
        return $this->hasManyThrough('Rox\Member\Model\Member', 'Rox\Models\ActivityAttendee', 'activityId', 'id');
    }

    public function location()
    {
        return $this->hasOne('Rox\Models\Location', 'geonameid', 'locationId');
    }
}