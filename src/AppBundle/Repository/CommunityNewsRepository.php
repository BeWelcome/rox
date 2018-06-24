<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class CommunityNewsRepository extends EntityRepository
{
    /**
     * @param boolean $publicOnly
     * @return QueryBuilder
     */
    public function queryLatest($publicOnly)
    {
        $qb = $this->createQueryBuilder('cn')
            ->orderBy('cn.createdAt', 'desc');
        if ($publicOnly)
        {
            $qb->where('cn.public = true');
        }
        return $qb;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param int $page
     * @param int $items
     *
     * @param bool $publicOnly
     * @return Pagerfanta
     */
    public function findLatest($page = 1, $items = 10, $publicOnly = true)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatest($publicOnly)));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * Gets the latest community news (only visible to the public) if any.
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLatest()
    {
        return $this->createQueryBuilder('cn')
            ->where('cn.public = :public')
            ->setParameter(':public', true)
            ->orderBy('cn.createdAt', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
