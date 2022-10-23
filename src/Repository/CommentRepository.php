<?php

namespace App\Repository;

use App\Doctrine\CommentAdminActionType;
use App\Doctrine\MemberStatusType;
use App\Entity\Comment;
use App\Entity\Member;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
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
     * @param $quality
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
     * @param $quality
     *
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
     * @param $action
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
     * @param $action
     *
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

    public function getCommentsMember(Member $member): array
    {
        $comments = [];
        $commentsForMember = $this->getVisibleCommentsForMember($member);
        $commentsByMember = $this->getVisibleCommentsByMember($member);

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
            $early20thCentury = new DateTimeImmutable('01-01-1900');
            usort(
                $comments,
                function ($a, $b) use ($early20thCentury) {
                    // get latest updates on to and from part of comments and order desc
                    $updatedATo = isset($a['to']) ? $a['to']->getUpdated() : $early20thCentury;
                    $updatedAFrom = isset($a['from']) ? $a['from']->getUpdated() : $early20thCentury;
                    $updatedA = max($updatedATo, $updatedAFrom);
                    $updatedBTo = isset($b['to']) ? $b['to']->getUpdated() : $early20thCentury;
                    $updatedBFrom = isset($b['from']) ? $b['from']->getUpdated() : $early20thCentury;
                    $updatedB = max($updatedBTo, $updatedBFrom);

                    return -1 * ($updatedA <=> $updatedB);
                }
            );
        }

        return $comments;
    }

    public function getVisibleCommentsForMemberCount(Member $member): int
    {
        return $this->getVisibleCommentsForMemberQueryBuilder($member)
            ->select('count(c.fromMember)')
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function getVisibleCommentsForMember(Member $member): array
    {
        return $this->getVisibleCommentsForMemberQueryBuilder($member)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getVisibleCommentsByMember(Member $member): array
    {
        return $this->getVisibleCommentsByMemberQueryBuilder($member)
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

    private function getVisibleCommentsByMemberQueryBuilder(Member $member): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->innerJoin('App:Member', 'm', 'WITH', $qb->expr()->andX(
                $qb->expr()->eq('m.id', 'c.toMember'),
                $qb->expr()->in('m.status', MemberStatusType::MEMBER_COMMENTS_ARRAY)
            ))
            ->where('c.fromMember = :member')
            ->andWhere('c.displayInPublic = 1')
            ->setParameter('member', $member)
        ;

        return $qb;
    }

    private function getVisibleCommentsForMemberQueryBuilder(Member $member): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->innerJoin('App:Member', 'm', 'WITH', $qb->expr()->andX(
                $qb->expr()->eq('m.id', 'c.fromMember'),
                $qb->expr()->in('m.status', MemberStatusType::MEMBER_COMMENTS_ARRAY)
            ))
            ->where('c.toMember = :member')
            ->andWhere('c.displayInPublic = 1')
            ->setParameter('member', $member)
        ;

        return $qb;
    }
}
