<?php

namespace App\Model;

use App\Entity\CommunityNews;
use App\Entity\CommunityNewsComment;
use App\Repository\CommunityNewsCommentRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\Collections\CollectionAdapter;
use Pagerfanta\Pagerfanta;

readonly class CommunityNewsModel
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getPaginator(int $page, int $limit): Pagerfanta
    {
        /** @var NotificationRepository $repository */
        $repository = $this->entityManager->getRepository(CommunityNews::class);

        return $repository->pagePublic($page, $limit);
    }

    public function getAdminPaginator(int $page, int $limit): Pagerfanta
    {
        /** @var NotificationRepository $repository */
        $repository = $this->entityManager->getRepository(CommunityNews::class);

        return $repository->pageAll($page, $limit);
    }

    public function getLatest(): mixed
    {
        /** @var NotificationRepository $repository */
        $repository = $this->entityManager->getRepository(CommunityNews::class);

        return $repository->getLatest();
    }

    public function getCommentsPaginator(CommunityNews $communityNews, int $page, int $limit): Pagerfanta
    {
        $adapter = new CollectionAdapter($communityNews->getComments());
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta
            ->setMaxPerPage($limit)
            ->setCurrentPage($page);

        return $pagerfanta;
    }

    public function getLatestCommunityNewsComments(int $page, int $limit): Pagerfanta
    {
        /** @var CommunityNewsCommentRepository $repository */
        $repository = $this->entityManager->getRepository(CommunityNewsComment::class);

        return $repository->findLatestCommunityNewsComments($page, $limit);
    }

    public function deleteAsSpamByChecker(array $commentIds): void
    {
        // delete all activities based on their ids
        /** @var CommunityNewsCommentRepository $communityNewsCommentRepository */
        $communityNewsCommentRepository = $this->entityManager->getRepository(CommunityNewsComment::class);
        $comments = $communityNewsCommentRepository->findBy(['id' => $commentIds]);
        foreach ($comments as $comment) {
            $this->entityManager->remove($comment);
        }
        $this->entityManager->flush();
    }
}
