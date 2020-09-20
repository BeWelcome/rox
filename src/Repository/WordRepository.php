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
        $qb = $this->createQueryBuilder('t');
        $q = $qb
            ->where('t.shortCode = :locale')
            ->where('(t.isArchived = 0 OR t.isArchived IS NULL)')
            ->andWhere('t.doNotTranslate = :doNotTranslate')
            ->andWhere('t.shortCode = :locale')
            ->setParameter(':locale', $locale)
        ;
        if ('en' === $locale) {
            $q->setParameter(':doNotTranslate', 'yes');
        } else {
            $q->setParameter(':doNotTranslate', 'no');
        }
        $q = $q->getQuery();

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
