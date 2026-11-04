<?php

namespace App\Repository;

use App\Entity\CommunityNews;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * The community news: the members' list (PaginationRepository) and the admin's list.
 */
class CommunityNewsRepository extends PaginationRepository
{
    /**
     * The news of the admin's list, the newest first: all, or only the published (true) or hidden (false) ones.
     *
     * @return Pagerfanta<CommunityNews>
     */
    public function pageByVisibility(?bool $public, int $page, int $items): Pagerfanta
    {
        $query = $this->createQueryBuilder('n')
            ->orderBy('n.createdAt', 'DESC')
            ->addOrderBy('n.id', 'DESC');
        if (null !== $public) {
            $query->where('n.public = :public')->setParameter('public', $public);
        }
        $pager = new Pagerfanta(new QueryAdapter($query));
        $pager->setMaxPerPage($items);
        $pager->setNormalizeOutOfRangePages(true);
        $pager->setCurrentPage(max(1, $page));

        return $pager;
    }

    /**
     * @return array{all: int, published: int, hidden: int}
     */
    public function countByVisibility(): array
    {
        $rows = $this->createQueryBuilder('n')
            ->select('n.public AS public', 'COUNT(n.id) AS news')
            ->groupBy('n.public')
            ->getQuery()
            ->getArrayResult();
        $counts = ['all' => 0, 'published' => 0, 'hidden' => 0];
        foreach ($rows as $row) {
            $counts[$row['public'] ? 'published' : 'hidden'] += (int) $row['news'];
            $counts['all'] += (int) $row['news'];
        }

        return $counts;
    }
}
