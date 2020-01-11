<?php

namespace App\Model;

use App\Entity\Activity;
use App\Repository\ActivityRepository;
use App\Utilities\ManagerTrait;

class ActivityModel
{
    use ManagerTrait;

    public function getLatest($page, $limit)
    {
        /** @var ActivityRepository $repository */
        $repository = $this->getManager()->getRepository(Activity::class);

        return $repository->findLatest($page, $limit);
    }

    public function getLatestBannedAdmins($page, $limit)
    {
        /** @var ActivityRepository $repository */
        $repository = $this->getManager()->getRepository(Activity::class);

        return $repository->findLatestBannedAdmins($page, $limit);
    }

    public function deleteAsSpamByChecker($activityIds)
    {
        // delete all activities based on there ids
        $em = $this->getManager();
        /** @var ActivityRepository $activityRepository */
        $activityRepository = $em->getRepository(Activity::class);
        $activities = $activityRepository->findBy([ 'id' => $activityIds]);
        foreach ($activities as $activity) {
            $em->remove($activity);
        }
        $em->flush();
    }
}
