<?php

namespace App\Repository;

use App\Entity\CommunityNews;
use App\Entity\CommunityNewsCommentReport;
use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommunityNewsCommentReport>
 */
class CommunityNewsCommentReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommunityNewsCommentReport::class);
    }

    /**
     * @return array<int, true> the comments of the news the member reported, by id
     */
    public function findReportedBy(Member $member, CommunityNews $communityNews): array
    {
        $ids = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.comment)')
            ->join('r.comment', 'c')
            ->where('r.reporter = :member')
            ->andWhere('c.communityNews = :news')
            ->setParameter('member', $member)
            ->setParameter('news', $communityNews)
            ->getQuery()
            ->getSingleColumnResult();

        return array_fill_keys(array_map('intval', $ids), true);
    }

    /**
     * How many comments have reports and are still there: the checkers' open reports.
     */
    public function countReportedComments(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(DISTINCT c.id)')
            ->join('r.comment', 'c')
            ->where('c.deleted IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
