<?php

namespace App\Model;

use App\Entity\Activity;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;

class ActivityModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getLatest($page, $limit): Pagerfanta
    {
        /** @var ActivityRepository $repository */
        $repository = $this->entityManager->getRepository(Activity::class);

        return $repository->findLatest($page, $limit);
    }

    public function getProblematicActivities($page, $limit): Pagerfanta
    {
        /** @var ActivityRepository $repository */
        $repository = $this->entityManager->getRepository(Activity::class);

        return $repository->findProblematicActivities($page, $limit);
    }

    public function deleteAsSpamByChecker($activityIds): void
    {
        // delete all activities based on there ids
        /** @var ActivityRepository $activityRepository */
        $activityRepository = $this->entityManager->getRepository(Activity::class);
        $activities = $activityRepository->findBy(['id' => $activityIds]);
        foreach ($activities as $activity) {
            $this->entityManager->remove($activity);
        }
        $this->entityManager->flush();
    }
}
