<?php

namespace App\Repository;

use App\Doctrine\TranslationAllowedType;
use App\Entity\Member;
use App\Entity\Word;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;

class WordRepository extends EntityRepository
{
    /** The views of the translation tool's list (toolList()) */
    public const array TOOL_VIEWS = ['missing', 'update', 'all', 'donottranslate', 'archived'];

    /**
     * A view of the translation tool's list: the English texts with their translation in the locale, if any.
     * Optionally only codes containing $term. Every row has the same columns, see the select below; one row per code,
     * so WordListAdapter counts the codes.
     * - missing: to translate, no translation yet, the newest first;
     * - update: to translate, the English text changed since the translation (a major update first);
     * - all: to translate, the last changed first;
     * - donottranslate: texts that stay English;
     * - archived: texts no longer used.
     */
    public function toolList(string $view, string $locale, string $term = ''): QueryBuilder
    {
        $qb = $this->createQueryBuilder('en')
            ->select('en.code AS code, en.domain AS domain, en.sentence AS english, en.description AS description')
            ->addSelect('en.format AS format, en.previousSentence AS previousSentence, en.sentenceChanged AS sentenceChanged')
            ->addSelect('en.created AS created, en.updated AS updated, en.majorUpdate AS majorUpdate')
            ->addSelect('en.isArchived AS isArchived, en.translationAllowed AS translationAllowed')
            ->addSelect('tr.sentence AS translation, tr.created AS translationCreated, tr.updated AS translationUpdated')
            ->leftJoin(Word::class, 'tr', Join::WITH, 'tr.code = en.code AND IDENTITY(tr.language) = :locale')
            ->where("IDENTITY(en.language) = 'en'")
            ->setParameter('locale', $locale);

        $current = '(en.isArchived IS NULL OR en.isArchived = false)';
        $toTranslate = $current . ' AND en.translationAllowed = :allowed';
        $outdated = '(en.majorUpdate > tr.updated OR en.updated > tr.updated)';
        match ($view) {
            'missing' => $qb->andWhere($toTranslate)->andWhere('tr.code IS NULL')
                ->orderBy('en.created', 'DESC'),
            'update' => $qb->andWhere($toTranslate)->andWhere('tr.code IS NOT NULL')->andWhere($outdated)
                ->addSelect('CASE WHEN en.majorUpdate > tr.updated THEN 1 ELSE 0 END AS HIDDEN major')
                ->orderBy('major', 'DESC')->addOrderBy('en.updated', 'DESC'),
            'all' => $qb->andWhere($toTranslate)->orderBy('en.updated', 'DESC'),
            'donottranslate' => $qb->andWhere($current)->andWhere('en.translationAllowed = :notAllowed')
                ->setParameter('notAllowed', TranslationAllowedType::TRANSLATION_NOT_ALLOWED)
                ->orderBy('en.updated', 'DESC'),
            'archived' => $qb->andWhere('en.isArchived = true')->orderBy('en.updated', 'DESC'),
            default => throw new InvalidArgumentException('Unknown view ' . $view),
        };
        if (\in_array($view, ['missing', 'update', 'all'], true)) {
            $qb->setParameter('allowed', TranslationAllowedType::TRANSLATION_ALLOWED);
        }
        // texts of the same date by code, so that every page has the same order
        $qb->addOrderBy('en.code', 'ASC');
        if ('' !== $term) {
            $qb->andWhere('en.code LIKE :term')->setParameter('term', '%' . addcslashes($term, '%_\\') . '%');
        }

        return $qb;
    }

    /**
     * How many texts each view of the translation tool's list has.
     *
     * @return array<string, int> by view (TOOL_VIEWS)
     */
    public function toolCounts(string $locale, string $term = ''): array
    {
        $counts = [];
        foreach (self::TOOL_VIEWS as $view) {
            $counts[$view] = (int) $this->toolList($view, $locale, $term)
                ->select('COUNT(en.code)')
                ->resetDQLPart('orderBy')
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $counts;
    }

    /**
     * For every language: how many of the texts to translate it has, and how many of them changed in English since.
     *
     * @return array<string, array{translated: int, outdated: int}> by locale
     */
    public function toolLanguageCounts(): array
    {
        $rows = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            "SELECT tr.ShortCode AS locale, COUNT(*) AS translated,
                    SUM(CASE WHEN en.majorupdate > tr.updated OR en.updated > tr.updated THEN 1 ELSE 0 END) AS outdated
             FROM word en
             JOIN word tr ON tr.code = en.code AND tr.ShortCode <> 'en'
             WHERE en.ShortCode = 'en' AND (en.isarchived IS NULL OR en.isarchived = 0) AND en.donottranslate = ?
             GROUP BY tr.ShortCode",
            [TranslationAllowedType::TRANSLATION_ALLOWED]
        );
        $counts = [];
        foreach ($rows as $row) {
            $counts[(string) $row['locale']] = ['translated' => (int) $row['translated'], 'outdated' => (int) $row['outdated']];
        }

        return $counts;
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
            $translationDetails[$locale] = [
                'count' => $count,
                'system' => $change['system'] ?? false,
                'translator' => $change['translator'] ?? null,
                'date' => $change['date'] ?? null,
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

        /** @var Word|null $details */
        $details = $qb
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null === $details) {
            return null;
        }

        // Author 0 is the system, which has no member row. The author is a reference that throws once it's used
        // when its member doesn't exist (any more); reading its id doesn't load it.
        $author = $details->getAuthor();
        $authorId = $author?->getId();
        $system = 0 === $authorId;
        $authorExists = null !== $authorId && !$system
            && 0 < $this->getEntityManager()->getRepository(Member::class)->count(['id' => $authorId]);
        $translator = $authorExists ? $author : null;

        return [
            'system' => $system,
            'translator' => $translator,
            'date' => $details->getUpdated(),
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
