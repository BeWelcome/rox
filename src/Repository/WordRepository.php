<?php

namespace App\Repository;

use App\Doctrine\TranslationAllowedType;
use App\Entity\Word;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class WordRepository extends EntityRepository
{
    public function paginateTranslations($locale, $code = '', $page = 1, $items = 20)
    {
        $queryBuilder = $this->queryAll($locale, $code);
        $adapter = new QueryAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    public function getTranslatableItemsCount($locale)
    {
        $qb = $this
            ->getTranslatableItemsForLocaleQuery($locale)
            ->select('count(t.id)')
        ;

        $q = $qb->getQuery();

        $count = $q->getSingleScalarResult();

        return $count;
    }

    public function getTranslationsForLocale(string $locale, string $domain)
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->where('t.shortCode = :locale')
            ->andWhere(
                $qb->expr()->orX(
                    't.isArchived = 0 OR t.isArchived IS NULL',
                    't.isArchived = 1 AND t.code LIKE \'broadcast%\''
                )
            )
            ->andWhere('t.domain = :domain')
            ->setParameter(':locale', $locale)
            ->setParameter(':domain', $domain);

        $translations = $qb
            ->getQuery()
            ->getResult();

        return $translations;
    }

    public function getTranslatableItemsForLocale(string $locale, string $domain)
    {
        $translatableItems =
            $this
                ->getTranslatableItemsForLocaleQuery($locale, $domain)
                ->getQuery()
                ->getResult()
        ;

        return $translatableItems;
    }

    public function getLanguagesForTranslatableItem(string $locale, string $domain)
    {
        $translatableItems =
            $this
                ->getTranslatableItemsForLocaleQuery($locale, $domain)
                ->getQuery()
                ->getResult()
        ;

        return $translatableItems;
    }

    public function getTranslationDetails(array $enabledLocales): array
    {
        $translationDetails = [];
        foreach ($enabledLocales as $locale) {
            $count = $this->getTranslatableItemsCount($locale);
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
            ->andWhere('t.translationAllowed = :translationAllowed')
            ->andWhere('t.shortCode = :locale')
            ->setParameter(':translationAllowed', TranslationAllowedType::TRANSLATION_ALLOWED)
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
            $translator = $details->getAuthor();
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

    private function getTranslatableItemsForLocaleQuery(string $locale, ?string $domain = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.shortCode = :locale')
            ->andWhere('(t.isArchived = 0 OR t.isArchived IS NULL)')
            ->setParameter(':locale', $locale)
        ;
        if (null !== $domain) {
            $qb
                ->andWhere('t.domain = :domain')
                ->setParameter(':domain', $domain)
            ;
        }
        $qb
            ->andWhere('t.translationAllowed = :translationAllowed')
            ->setParameter(':translationAllowed', TranslationAllowedType::TRANSLATION_ALLOWED)
        ;

        return $qb;
    }
}
