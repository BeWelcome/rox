<?php

namespace App\Repository;

use App\Doctrine\TranslationAllowedType;
use App\Entity\Word;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Exception;

class WordRepository extends EntityRepository
{
    // The lists of the translation tool. Each row: code, domain, shortCode, sentence, created (and majorUpdate for
    // updateList()); one row per code, so WordListAdapter counts the distinct codes of the root alias.

    /**
     * Every code, with its text in the locale or else in English. Optionally only codes or texts containing $term.
     */
    public function allList(string $locale, string $term = ''): QueryBuilder
    {
        $qb = $this->createQueryBuilder('w')
            ->select('DISTINCT w.code AS code')
            ->addSelect('COALESCE(IDENTITY(lang.language), IDENTITY(dflt.language)) AS shortCode')
            ->addSelect('COALESCE(lang.domain, dflt.domain) AS domain')
            ->addSelect('COALESCE(lang.sentence, dflt.sentence) AS sentence')
            ->addSelect('COALESCE(lang.created, dflt.created) AS created')
            ->leftJoin(
                Word::class,
                'dflt',
                Join::WITH,
                "dflt.code = w.code AND IDENTITY(dflt.language) = 'en' AND (dflt.isArchived IS NULL OR dflt.isArchived = false)"
                . ' AND dflt.translationAllowed = :allowed'
            )
            ->leftJoin(
                Word::class,
                'lang',
                Join::WITH,
                'lang.code = w.code AND IDENTITY(lang.language) = :locale AND (lang.isArchived IS NULL OR lang.isArchived = false)'
            )
            ->setParameter('allowed', TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setParameter('locale', $locale)
            ->orderBy('created', 'DESC');
        if ('' !== $term) {
            $qb->where('w.code LIKE :term OR w.sentence LIKE :term')->setParameter('term', '%' . $term . '%');
        }

        return $qb;
    }

    /**
     * English texts to translate that have no text in the locale yet. Optionally only codes containing $term.
     */
    public function missingList(string $locale, string $term = ''): QueryBuilder
    {
        $translated = $this->createQueryBuilder('translated')
            ->select('translated.code')
            ->where('IDENTITY(translated.language) = :locale');
        $qb = $this->englishRows('w')
            ->andWhere('(w.isArchived IS NULL OR w.isArchived = false)')
            ->andWhere('w.translationAllowed = :allowed')
            ->andWhere('w.code NOT IN (' . $translated->getDQL() . ')')
            ->setParameter('allowed', TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setParameter('locale', $locale)
            ->orderBy('w.created', 'DESC');
        if ('' !== $term) {
            $qb->andWhere('w.code LIKE :term')->setParameter('term', '%' . $term . '%');
        }

        return $qb;
    }

    /**
     * Texts in the locale whose English text changed since; majorUpdate is 1 for a major change.
     */
    public function updateList(string $locale): QueryBuilder
    {
        return $this->createQueryBuilder('w')
            ->select('w.code AS code, w.domain AS domain, IDENTITY(w.language) AS shortCode, w.sentence AS sentence')
            ->addSelect('w.created AS created')
            ->addSelect('CASE WHEN en.majorUpdate > w.updated THEN 1 ELSE 0 END AS majorUpdate')
            ->innerJoin(Word::class, 'en', Join::WITH, "en.code = w.code AND IDENTITY(en.language) = 'en'")
            ->where('IDENTITY(w.language) = :locale')
            ->andWhere('(en.isArchived IS NULL OR en.isArchived = false)')
            ->andWhere('en.translationAllowed = :allowed')
            ->andWhere('en.majorUpdate > w.updated OR en.updated > w.updated')
            ->setParameter('locale', $locale)
            ->setParameter('allowed', TranslationAllowedType::TRANSLATION_ALLOWED)
            ->orderBy('majorUpdate', 'DESC')
            ->addOrderBy('en.updated', 'DESC');
    }

    /**
     * Archived English texts, last updated first.
     */
    public function archivedList(): QueryBuilder
    {
        return $this->englishRows('w')
            ->andWhere('w.isArchived = true')
            ->orderBy('w.updated', 'DESC');
    }

    /**
     * English texts that must not be translated, last updated first.
     */
    public function doNotTranslateList(): QueryBuilder
    {
        return $this->englishRows('w')
            ->andWhere('w.translationAllowed = :notAllowed')
            ->setParameter('notAllowed', TranslationAllowedType::TRANSLATION_NOT_ALLOWED)
            ->orderBy('w.updated', 'DESC');
    }

    public function getTranslatableItemsCount($locale)
    {
        $qb = $this
            ->getTranslatableItemsForLocaleQuery($locale)
            ->select('COUNT(t.code)')
        ;

        $q = $qb->getQuery();

        $count = $q->getSingleScalarResult();

        return $count;
    }

    public function getTranslationsForLocale(string $locale, string $domain)
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->where('t.language = :locale')
            ->andWhere(
                $qb->expr()->orX(
                    't.isArchived = 0 OR t.isArchived IS NULL',
                    't.isArchived = 1 AND t.code LIKE \'broadcast%\''
                )
            )
            ->andWhere('t.domain = :domain')
            ->setParameter('locale', $locale)
            ->setParameter('domain', $domain);

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
            uasort($translationDetails, static function ($a, $b) {
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

    private function englishRows(string $alias): QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->select("{$alias}.code AS code, {$alias}.domain AS domain, IDENTITY({$alias}.language) AS shortCode")
            ->addSelect("{$alias}.sentence AS sentence, {$alias}.created AS created")
            ->where("IDENTITY({$alias}.language) = 'en'");
    }

    private function getLatestChange(string $locale): ?array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.language = :locale')
            ->andWhere('(t.isArchived = 0 OR t.isArchived IS NULL)')
            ->andWhere('t.translationAllowed = :translationAllowed')
            ->setParameter('translationAllowed', TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setParameter('locale', $locale)
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
        } catch (Exception) {
            $translator = null;
        }

        return [
            'translator' => $translator,
            'date' => $date,
        ];
    }

    private function getTranslatableItemsForLocaleQuery(string $locale, ?string $domain = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.language = :locale')
            ->andWhere('(t.isArchived = 0 OR t.isArchived IS NULL)')
            ->setParameter('locale', $locale)
        ;
        if (null !== $domain) {
            $qb
                ->andWhere('t.domain = :domain')
                ->setParameter('domain', $domain)
            ;
        }
        $qb
            ->andWhere('t.translationAllowed = :translationAllowed')
            ->setParameter('translationAllowed', TranslationAllowedType::TRANSLATION_ALLOWED)
        ;

        return $qb;
    }
}
