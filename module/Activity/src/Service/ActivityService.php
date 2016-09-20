<?php

namespace Rox\Activity\Service;

use Rox\Activity\Model\Activity;

class ActivityService implements ActivityServiceInterface
{
    public function getAllActivity()
    {
        $activity = new Activity();

        return $activity->getAll();
    }
}
