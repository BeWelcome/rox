<?php

namespace App\Repository;

use App\Entity\Word;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
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
        $qb = $this
            ->createQueryBuilder('t')
            ->select('count(t.id)')
            ->where('(t.isArchived = 0 OR t.isArchived IS NULL)')
            ->andWhere('t.shortCode = :locale')
            ->setParameter(':locale', $locale)
        ;

        if ('en' !== $locale) {
            $qb
                ->andWhere('t.doNotTranslate = :doNotTranslate')
                ->setParameter(':doNotTranslate', 'no')
            ;
        }

        $q = $qb->getQuery();

        $count = $q->getSingleScalarResult();

        return $count;
    }

    public function getTranslationsForLocale(string $locale, string $domain)
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.shortCode = :locale')
            ->where('(t.isArchived = 0 OR t.isArchived IS NULL)')
            ->andWhere('t.doNotTranslate = :doNotTranslate')
            ->andWhere('t.shortCode = :locale')
            ->andWhere('t.domain = :domain')
            ->setParameter(':doNotTranslate', 'no')
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

    public function getTranslationDetails(): array
    {
        $translationDetails = [];
        // \todo: Check for existing locales in Filesystem (allows to enable languages only on certain installs)
        $locales = explode(
            ',',
            'ar,bg,ca,cs,da,de,el,en,eo,es,eu,fa,fi,fr,hi,hr,hu,id,it,ja,lt,lv,nb,'
            . 'nl,no,pl,pt,pt-BR,rm,ro,ru,sk,sl,sr,su,sw,tr,zh-Hans,zh-Hant'
        );
        foreach ($locales as $locale) {
            $count = $this->getTranslationIdCount($locale);
            $change = $this->getLatestChange($locale);
            if (null === $change) {
                $translator = null;
                $date = null;
            } else {
                $translator = $change['translator'];
                $date = $change['date'];
            }
            $translationDetails[$locale] = [
                'count' => $count,
                'translator' => $translator,
                'date' => $date,
            ];
            uasort($translationDetails, function ($a, $b) {
                if ($a['count'] === $b['count']) {
                    return 0;
                } elseif ($a['count'] > $b['count']) {
                    return -1;
                }

                return 1;
            });
        }

        return $translationDetails;
    }

    private function getLatestChange(string $locale): ?array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.shortCode = :locale')
            ->where('(t.isArchived = 0 OR t.isArchived IS NULL)')
            ->andWhere('t.doNotTranslate = :doNotTranslate')
            ->andWhere('t.shortCode = :locale')
            ->setParameter(':doNotTranslate', 'no')
            ->setParameter(':locale', $locale)
            ->orderBy('t.updated', 'DESC')
            ->setMaxResults(1)
        ;

        /** @var Word $details */
        $details = $qb
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $date = $details->getUpdated();
        try {
            $translator = $details->getAuthor()->getUsername();
        } catch (\Exception $e) {
            $translator = null;
        }

        return [
            'translator' => $translator,
            'date' => $date,
        ];
    }

    private function queryAll($locale, $code = ''): QueryBuilder
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
