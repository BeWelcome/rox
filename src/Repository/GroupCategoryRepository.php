<?php

namespace App\Repository;

use App\Entity\GroupCategory;
use App\Entity\GroupCategoryTranslation;
use Collator;
use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<GroupCategory>
 */
class GroupCategoryRepository extends EntityRepository
{
    /**
     * All categories, sorted by their name in the given language.
     *
     * @return list<GroupCategory>
     */
    public function findAllSortedByName(string $locale): array
    {
        /** @var list<GroupCategory> $categories */
        $categories = $this->createQueryBuilder('c')
            ->select('c', 't')
            ->leftJoin('c.translations', 't')
            ->getQuery()
            ->getResult();

        $collator = new Collator($locale);
        usort(
            $categories,
            static fn (GroupCategory $a, GroupCategory $b): int => (int) $collator->compare($a->getName($locale), $b->getName($locale)),
        );

        return $categories;
    }

    /**
     * @return array<int, int> the number of groups in each category, by category id (categories without groups left out)
     */
    public function countGroups(): array
    {
        $rows = $this->getEntityManager()->getConnection()->fetchAllNumeric(
            'SELECT group_category_id, COUNT(*) FROM group_category_group GROUP BY group_category_id'
        );
        $counts = [];
        foreach ($rows as [$categoryId, $count]) {
            $counts[(int) $categoryId] = (int) $count;
        }

        return $counts;
    }

    /**
     * The categories whose keywords or names (in any language) are words of the text, the most matches first.
     *
     * @param list<int> $without ids of categories left out (those already picked)
     *
     * @return list<GroupCategory>
     */
    public function findSuggested(string $text, array $without, int $limit): array
    {
        $text = mb_strtolower(html_entity_decode(strip_tags($text), \ENT_QUOTES | \ENT_HTML5));
        if ('' === trim($text)) {
            return [];
        }

        $scores = [];
        $categories = [];
        foreach ($this->findAllSortedByName('en') as $category) {
            if (\in_array($category->getId(), $without, true)) {
                continue;
            }
            $names = array_map(static fn (GroupCategoryTranslation $name): string => mb_strtolower((string) $name->getContent()), array_values($category->getNames()));
            $words = array_merge($category->getKeywords(), $names);
            $score = 0;
            foreach (array_unique($words) as $word) {
                $word = trim($word);
                if ('' !== $word && 1 === preg_match('/(?<![\p{L}\p{N}])' . preg_quote($word, '/') . '(?![\p{L}\p{N}])/u', $text)) {
                    ++$score;
                }
            }
            if ($score > 0) {
                $scores[$category->getId()] = $score;
                $categories[$category->getId()] = $category;
            }
        }
        arsort($scores);

        return array_values(array_map(
            static fn (int $id): GroupCategory => $categories[$id],
            \array_slice(array_keys($scores), 0, $limit),
        ));
    }
}
