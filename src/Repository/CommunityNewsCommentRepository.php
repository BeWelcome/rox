<?php

namespace App\Repository;

use App\Entity\CommunityNewsComment;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @extends EntityRepository<CommunityNewsComment>
 */
class CommunityNewsCommentRepository extends EntityRepository
{
    /**
     * The comments members reported and the checkers haven't handled yet: the most reported first, then the newest.
     *
     * @return Pagerfanta<CommunityNewsComment>
     */
    public function findReported(int $page = 1, int $items = 20): Pagerfanta
    {
        $query = $this->createQueryBuilder('c')
            ->addSelect('COUNT(r.id) AS HIDDEN reports')
            ->join('c.reports', 'r')
            ->where('c.deleted IS NULL')
            ->groupBy('c.id')
            ->orderBy('reports', 'DESC')
            ->addOrderBy('c.created', 'DESC');

        $pager = new Pagerfanta(new QueryAdapter($query, false, false));
        $pager->setMaxPerPage($items);
        $pager->setNormalizeOutOfRangePages(true);
        $pager->setCurrentPage(max(1, $page));

        return $pager;
    }

    /**
     * @param list<int> $newsIds
     *
     * @return array<int, int> the comments of each news (not the removed ones), by news id
     */
    public function countByNews(array $newsIds): array
    {
        if ([] === $newsIds) {
            return [];
        }
        $rows = $this->createQueryBuilder('c')
            ->select('IDENTITY(c.communityNews) AS news', 'COUNT(c.id) AS comments')
            ->where('c.communityNews IN (:news)')
            ->andWhere('c.deleted IS NULL')
            ->setParameter('news', $newsIds)
            ->groupBy('news')
            ->getQuery()
            ->getArrayResult();

        return array_combine(
            array_map(static fn (array $row): int => (int) $row['news'], $rows),
            array_map(static fn (array $row): int => (int) $row['comments'], $rows),
        );
    }
}
