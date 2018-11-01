<?php

namespace App\Model;

use App\Entity\Activity;
use App\Repository\ActivityRepository;

class ActivityModel extends BaseModel
{
    public function getLatest($page, $limit)
    {
        /** @var ActivityRepository $repository */
        $repository = $this->em->getRepository(Activity::class);

        return $repository->findLatest($page, $limit);
    }
}
