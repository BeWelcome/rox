<?php

namespace Rox\Activity\Service;

use Rox\Activity\Model\Activity;

class ActivityService implements ActivityServiceInterface
{
    public function getAllActivities()
    {
        $activity = new Activity();

        return $activity->getAll();
    }
}
