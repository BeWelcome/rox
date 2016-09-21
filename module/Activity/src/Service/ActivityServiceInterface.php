<?php

namespace Rox\Activity\Service;

use Illuminate\Database\Eloquent\Builder;

interface ActivityServiceInterface
{
    /**
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function getAllActivities();
}
