<?php

namespace AppBundle\Model;

use AppBundle\Entity\Activity;
use AppBundle\Repository\ActivityRepository;

class ActivityModel extends BaseModel
{
    public function getLatest($page, $limit)
    {
        /** @var ActivityRepository $repository */
        $repository = $this->em->getRepository(Activity::class);

        return $repository->findLatest($page, $limit);
    }
}
