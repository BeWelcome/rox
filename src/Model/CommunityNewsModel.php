<?php

/**
 * Created by PhpStorm.
 * User: saeed
 * Date: 12/22/16
 * Time: 12:36 AM.
 */

namespace App\Model;

use App\Entity\CommunityNews;
use App\Entity\CommunityNewsComment;
use App\Repository\CommunityNewsCommentRepository;
use App\Repository\NotificationRepository;
use App\Utilities\ManagerTrait;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @method getLatestAdminPaginator($page, $limit)
 */
class CommunityNewsModel
{
    use ManagerTrait;

    /**
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function getPaginator($page, $limit)
    {
        /** @var NotificationRepository $repository */
        $repository = $this->getManager()->getRepository(CommunityNews::class);

        return $repository->pagePublic($page, $limit);
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function getAdminPaginator($page, $limit)
    {
        /** @var NotificationRepository $repository */
        $repository = $this->getManager()->getRepository(CommunityNews::class);

        return $repository->pageAll($page, $limit);
    }

    public function getLatest()
    {
        /** @var NotificationRepository $repository */
        $repository = $this->getManager()->getRepository(CommunityNews::class);

        return $repository->getLatest();
    }

    public function getCommentsPaginator(CommunityNews $communityNews, $page, $limit)
    {
        $adapter = new DoctrineCollectionAdapter($communityNews->getComments());
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta
            ->setMaxPerPage($limit)
            ->setCurrentPage($page);

        return $pagerfanta;
    }

    public function getLatestCommunityNewsComments($page, $limit)
    {
        /** @var CommunityNewsCommentRepository $repository */
        $repository = $this->getManager()->getRepository(CommunityNewsComment::class);

        return $repository->findLatestCommunityNewsComments($page, $limit);
    }

    public function deleteAsSpamByChecker($commentIds)
    {
        // delete all activities based on there ids
        $em = $this->getManager();
        /** @var CommunityNewsCommentRepository $repository */
        $communityNewsCommentRepository = $em->getRepository(CommunityNewsComment::class);
        $comments = $communityNewsCommentRepository->findBy(['id' => $commentIds]);
        foreach ($comments as $comment) {
            $em->remove($comment);
        }
        $em->flush();
    }
}
