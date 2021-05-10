<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * CommunityNewsCommentRepository.
 */
class CommunityNewsCommentRepository extends EntityRepository
{
    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * Only lists activities which do have only banned admins.
     *
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function findLatestCommunityNewsComments($page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new QueryAdapter($this->queryLatestCommunityNewsComments(), false));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return Query
     */
    public function queryLatestCommunityNewsComments()
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.created', 'desc')
            ->getQuery();
    }
}
