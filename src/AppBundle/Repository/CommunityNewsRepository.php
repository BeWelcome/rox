<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class CommunityNewsRepository extends EntityRepository
{
    /**
     * @param boolean $publicOnly
     * @return Query
     */
    public function queryLatest($publicOnly)
    {
        $query = $this->createQueryBuilder('cn')
            ->orderBy('cn.createdAt', 'desc');
        if ($publicOnly) {
            $query
                ->where('cn.public = :public')
                ->setParameter(':public', false);
        }
        return $query
            ->setMaxResults(1)
            ->getQuery();
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
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatest($publicOnly), false));
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
