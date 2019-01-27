<?php
/**
 * Created by PhpStorm.
 * User: saeed
 * Date: 12/22/16
 * Time: 12:36 AM.
 */

namespace App\Model;

use App\Entity\CommunityNews;
use App\Repository\NotificationRepository;
use Doctrine\ORM\NonUniqueResultException;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @method getLatestAdminPaginator($page, $limit)
 */
class CommunityNewsModel extends BaseModel
{
    /**
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function getPaginator($page, $limit)
    {
        /** @var NotificationRepository $repository */
        $repository = $this->em->getRepository(CommunityNews::class);

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
        $repository = $this->em->getRepository(CommunityNews::class);

        return $repository->pageAll($page, $limit);
    }

    public function getLatest()
    {
        /** @var NotificationRepository $repository */
        $repository = $this->em->getRepository(CommunityNews::class);

        try {
            return $repository->getLatest();
        } catch (NonUniqueResultException $e) {
        }
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
}
