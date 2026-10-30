<?php

namespace App\Repository;

use App\Entity\GroupCategory;
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
}
