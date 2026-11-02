<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\CommentReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommentReport>
 */
class CommentReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentReport::class);
    }

    /**
     * @return list<CommentReport> the reports of the comment, the newest first
     */
    public function findForComment(Comment $comment): array
    {
        return $this->findBy(['comment' => $comment], ['created' => 'DESC', 'id' => 'DESC']);
    }

    /**
     * @param iterable<Comment> $comments
     *
     * @return array<int, true> the ids of those comments that have a report
     */
    public function reportedAmong(iterable $comments): array
    {
        $ids = [];
        foreach ($comments as $comment) {
            $ids[] = $comment->getId();
        }
        if ([] === $ids) {
            return [];
        }

        $reported = $this->createQueryBuilder('r')
            ->select('DISTINCT IDENTITY(r.comment) AS id')
            ->where('r.comment IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getSingleColumnResult();

        return array_fill_keys(array_map('intval', $reported), true);
    }
}
