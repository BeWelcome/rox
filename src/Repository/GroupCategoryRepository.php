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
}
