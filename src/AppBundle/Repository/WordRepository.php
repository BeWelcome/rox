<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class WordRepository extends EntityRepository
{
    public function paginateTranslations($locale, $page = 1, $items = 20)
    {
        $queryBuilder = $this->queryAll($locale);
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    private function queryAll($locale)
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.shortCode = :locale')
            ->setParameter(':locale', $locale)
            ->orderBy('t.created', 'DESC')
            ->addOrderBy('t.code', 'ASC');

        return $qb;
    }
}
