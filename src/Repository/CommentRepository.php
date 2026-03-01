<?php

namespace App\Repository;

use App\Doctrine\CommentAdminActionType;
use App\Doctrine\CommentQualityType;
use App\Doctrine\MemberStatusType;
use App\Entity\Comment;
use App\Entity\Member;
use App\Utilities\CommentSorter;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class CommentRepository extends EntityRepository
{
    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function pageAll($page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new QueryAdapter($this->queryAll()));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return QueryBuilder
     */
    public function queryAll()
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.created', 'desc');

        return $qb;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function pageAllForMember(Member $member, $page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new QueryAdapter($this->queryAllForMember($member)));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return QueryBuilder
     */
    public function queryAllForMember(Member $member)
    {
        $qb = $this->queryAll()
            ->where('c.toMember = :member')
            ->setParameter('member', $member);

        return $qb;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function pageAllFromMember(Member $member, $page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new QueryAdapter($this->queryAllFromMember($member)));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return QueryBuilder
     */
    public function queryAllFromMember(Member $member)
    {
        $qb = $this->queryAll()
            ->where('c.fromMember = :member')
            ->setParameter('member', $member);

        return $qb;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function pageAllByQuality($quality, $page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new QueryAdapter($this->queryAllByQuality($quality)));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return QueryBuilder
     */
    public function queryAllByQuality($quality)
    {
        $qb = $this->queryAll()
            ->where('c.quality = :quality')
            ->setParameter('quality', $quality);

        return $qb;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function pageAllByAdminAction($action, $page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(new QueryAdapter($this->queryAllByAdminAction($action)));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return QueryBuilder
     */
    public function queryAllByAdminAction($action)
    {
        $qb = $this->queryAll()
            ->where('c.adminAction = :action')
            ->setParameter('action', $action);

        return $qb;
    }

    public function getReportedCommentsCount(): int
    {
        $q = $this->createQueryBuilder('c')
            ->select('count(c.fromMember)')
            ->where('c.adminAction = :status')
            ->setParameter('status', CommentAdminActionType::ADMIN_CHECK)
            ->getQuery();

        $results = (int) $q->getSingleScalarResult();

        return $results;
    }

    public function getCommentsCountMemberByQuality(Member $member): array
    {
        $qb = $this->createQueryBuilder('c');
        $results = $qb
            ->innerJoin(Member::class, 'm', 'WITH', $qb->expr()->andX(
                $qb->expr()->eq('m.id', 'c.fromMember'),
                $qb->expr()->in('m.status', MemberStatusType::MEMBER_COMMENTS_ARRAY)
            ))
            ->where('c.toMember = :member')
            ->andWhere($qb->expr()->eq('c.displayInPublic', 1))
            ->setParameter('member', $member)
            ->select('c.quality, count(c.fromMember) AS count')
            ->groupBy('c.quality')
            ->getQuery()
            ->getScalarResult();
        ;

        $counts = [
            CommentQualityType::POSITIVE => 0,
            CommentQualityType::NEUTRAL => 0,
            CommentQualityType::NEGATIVE => 0,
            'total' => 0,
        ];

        $total = 0;
        foreach($results as $result) {
            $counts[$result['quality']] = $result['count'];
            $total += $result['count'];
        }
        $counts['total'] = $total;

        return $counts;
    }

    public function getAllCommentsMember(Member $member, string $type): array
    {
        $commentsByMember = [];
        $commentsForMember = $this->getAllCommentsForMember($member, $type);
        if ('all' === $type || ('all' !== $type && !empty($commentsForMember))) {
            $commentsByMember = $this->getAllCommentsByMember($member);
        }

        return $this->getCommentsAsArray($commentsForMember, $commentsByMember);
    }

    public function getLatestCommentsMember(Member $member, int $count = 5): array
    {
        $commentsForMember = $this->getLatestCommentsForMember($member, $count);
        $commentsByMember = $this->getLatestCommentsByMember($member, $count);

        return $this->getCommentsAsArray($commentsForMember, $commentsByMember);
    }

    public function getVisibleCommentsForMemberCount(Member $member): int
    {
        return $this->getCommentsForMemberQueryBuilder($member)
            ->select('count(c.fromMember)')
            ->andWhere('c.displayInPublic = 1')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getVisibleCommentsForMember(Member $member): array
    {
        return $this->getCommentsForMemberQueryBuilder($member)
            ->andWhere('c.displayInPublic = 1')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getAllCommentsForMember(Member $member, string $type): array
    {
        return $this->getCommentsForMemberQueryBuilder($member, $type)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getAllCommentsByMember(Member $member): array
    {
        return $this->getCommentsByMemberQueryBuilder($member)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getLatestCommentsForMember(Member $member, int $count): array
    {
        return $this->getCommentsForMemberQueryBuilder($member)
            ->orderBy('c.created', 'DESC')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getLatestCommentsByMember(Member $member, int $count): array
    {
        return $this->getCommentsByMemberQueryBuilder($member)
            ->orderBy('c.created', 'DESC')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getCommentsFromMember(Member $member): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.fromMember = :member')
            ->setParameter('member', $member)
            ->orderBy('c.created', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    private function getCommentsByMemberQueryBuilder(Member $member): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->innerJoin(Member::class, 'm', 'WITH', $qb->expr()->andX(
                $qb->expr()->eq('m.id', 'c.toMember'),
                $qb->expr()->in('m.status', MemberStatusType::MEMBER_COMMENTS_ARRAY)
            ))
            ->where('c.fromMember = :member')
            ->setParameter('member', $member)
        ;

        return $qb;
    }

    private function getCommentsForMemberQueryBuilder(Member $member, string $type = 'all'): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->innerJoin(Member::class, 'm', 'WITH', $qb->expr()->andX(
                $qb->expr()->eq('m.id', 'c.fromMember'),
                $qb->expr()->in('m.status', MemberStatusType::MEMBER_COMMENTS_ARRAY)
            ))
            ->where('c.toMember = :member')
            ->setParameter('member', $member)
        ;

        if ('all' !== $type) {
            $qb
                ->andWhere('c.quality = :type')
                ->setParameter('type', $type);
        }

        return $qb;
    }

    private function getCommentsAsArray(array $commentsForMember, array $commentsByMember): array
    {
        $comments = [];

        /** @var Comment $value */
        foreach ($commentsForMember as $value) {
            $key = $value->getFromMember()->getUsername();
            $comments[$key] = [
                'from' => $value,
            ];
        }
        foreach ($commentsByMember as $value) {
            $key = $value->getToMember()->getUsername();
            if (isset($comments[$key])) {
                $comments[$key] = array_merge($comments[$key], [
                    'to' => $value,
                ]);
            } else {
                $comments[$key] = [
                    'to' => $value,
                ];
            }
        }

        if (!empty($comments)) {
            $comments = new CommentSorter()->sortComments($comments);
        }

        return $comments;
    }
}
