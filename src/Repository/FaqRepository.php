<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class FaqRepository extends EntityRepository
{
    /**
     * Returns a Pagerfanta object encapsulating the matching paginated log messages.
     *
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function findLatest($page, $limit)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatest()));
        $paginator->setMaxPerPage($limit);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return Query
     */
    private function queryLatest()
    {
        $qb = $this->createQueryBuilder('f');
        $qb
            ->join('f.category', 'c')
            ->orderBy('c.sortorder', 'ASC')
            ->addOrderBy('f.sortorder', 'ASC');

        return $qb->getQuery();
    }
}
