<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class WordRepository extends EntityRepository
{
    public function paginateTranslations($locale, $code = '', $page = 1, $items = 20)
    {
        $queryBuilder = $this->queryAll($locale, $code);
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    public function getTranslationIdCount($locale)
    {
        $qb = $this->createQueryBuilder('t');
        $q = $qb
            ->select('count(t.id)')
            ->where('(t.isArchived = 0 OR t.isArchived IS NULL)')
            ->andWhere('t.doNotTranslate = :doNotTranslate')
            ->andWhere('t.shortCode = :locale')
            ->setParameter(':doNotTranslate', 'no')
            ->setParameter(':locale', $locale)
            ->getQuery()
        ;
        $count = $q->getSingleScalarResult();

        return $count;
    }

    public function getTranslationsForLocale(string $locale, string $domain)
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.shortCode = :locale')
            ->where('(t.isArchived = 0 OR t.isArchived IS NULL)')
            ->andWhere('t.shortCode = :locale')
            ->andWhere('t.domain = :domain')
            ->setParameter(':locale', $locale)
            ->setParameter(':domain', $domain)
        ;
        if ('en' !== $locale) {
            $qb
                ->andWhere('t.doNotTranslate = :doNotTranslate')
                ->setParameter(':doNotTranslate', 'no');
        }
        $q = $qb->getQuery();

        $translations = $q->getResult();

        return $translations;
    }
    private function queryAll($locale, $code = '')
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.shortCode = :locale')
            ->setParameter(':locale', $locale)
            ->orderBy('t.created', 'DESC')
            ->addOrderBy('t.code', 'ASC');
        if (!empty($code)) {
            $qb
                ->andWhere('t.code LIKE :code')
                ->setParameter(':code', '%' . $code . '%');
        }

        return $qb;
    }
}
