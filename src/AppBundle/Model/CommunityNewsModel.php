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

class CommunityNewsModel extends BaseModel
{
    /**
     * @param mixed $page
     * @param mixed $limit
     *
     * @param bool $publicOnly
     * @return Pagerfanta
     */
    public function getLatestPaginator($page, $limit)
    {
        /** @var CommunityNewsRepository $repository */
        $repository = $this->em->getRepository(CommunityNews::class);

        return $repository->findLatest($page, $limit, true);
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
