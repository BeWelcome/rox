<?php

namespace AppBundle\Repository;

use AppBundle\Doctrine\CommentAdminActionType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class CommentRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    public function queryAll()
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.created', 'desc');

        return $qb;
    }

    /**
     * @param $quality
     * @return QueryBuilder
     */
    public function queryAllByQuality($quality)
    {
        $qb = $this->queryAll()
            ->where('c.quality = :quality')
            ->setParameter('quality', $quality);

        return $qb;
    }

    /**
     * @param $action
     * @return QueryBuilder
     */
    public function queryAllByAdminAction($action)
    {
        $qb = $this->queryAll()
            ->where('c.adminAction = :action')
            ->setParameter('action', $action);

        return $qb;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function pageAll($page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryAll()));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param $quality
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function pageAllByQuality($quality, $page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryAllByQuality($quality)));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param $action
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function pageAllByAdminAction($action, $page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryAllByAdminAction($action)));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return mixed|null
     */
    public function getReportedCommentsCount()
    {
        $q = $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.adminAction = :status')
            ->setParameter('status', CommentAdminActionType::ADMIN_CHECK)
            ->getQuery();

        $results = null;
        try {
            $results = $q->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
        }

        return $results;
    }

}
