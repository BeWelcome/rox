<?php
/**
 * Created by PhpStorm.
 * User: saeed
 * Date: 12/22/16
 * Time: 12:36 AM.
 */

namespace AppBundle\Model;

use AppBundle\Entity\CommunityNews;
use AppBundle\Repository\CommunityNewsRepository;
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
        /** @var CommunityNewsRepository $repository */
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
        /** @var CommunityNewsRepository $repository */
        $repository = $this->em->getRepository(CommunityNews::class);

        return $repository->pageAll($page, $limit);
    }

    public function getLatest()
    {
        /** @var CommunityNewsRepository $repository */
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
            ->setCurrentPage($page)
        ;

        return $pagerfanta;
    }
}
