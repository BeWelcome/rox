<?php

namespace App\Repository;

use App\Doctrine\MemberStatusType;
use App\Entity\Friend;
use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @extends ServiceEntityRepository<Friend>
 */
class FriendRepository extends ServiceEntityRepository
{
    /** The friends pages show 6 friends a row on large screens. */
    private const int FRIENDS_PER_ROW = 6;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friend::class);
    }

    /**
     * Number of confirmed friends of $member (whose accounts are active).
     */
    public function countFriends(Member $member): int
    {
        return (int) $this->confirmedFor($member)
            ->select('COUNT(f.left)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * The confirmed friendships of $member, the latest first.
     *
     * @return Friend[]
     */
    public function findFriendsFor(Member $member): array
    {
        return $this->confirmedFor($member)->getQuery()->getResult();
    }

    /**
     * The confirmed friendships of $member as pages. The page size is rounded up to a whole number of rows.
     *
     * @return Pagerfanta<Friend>
     */
    public function getFriends(Member $member, int $page, int $itemsPerPage = 50): Pagerfanta
    {
        $friends = new Pagerfanta(new QueryAdapter($this->confirmedFor($member), false));
        $rows = intdiv(max(1, $itemsPerPage) - 1, self::FRIENDS_PER_ROW) + 1;
        $friends->setMaxPerPage($rows * self::FRIENDS_PER_ROW);
        $friends->setCurrentPage($page);

        return $friends;
    }

    /**
     * The friendship of the two members, confirmed or not.
     */
    public function findFriendshipBetween(Member $one, Member $other): ?Friend
    {
        $left = $one->getId() < $other->getId() ? $one : $other;
        $right = $one->getId() < $other->getId() ? $other : $one;

        return $this->findOneBy(['left' => $left, 'right' => $right]);
    }

    /**
     * The friendships others asked $member to confirm, the latest first.
     *
     * @return Friend[]
     */
    public function findRequestsFor(Member $member): array
    {
        return $this->pendingFor($member)
            ->andWhere('f.requester <> :member')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * The friendships $member asked for that aren't confirmed yet, the latest first.
     *
     * @return Friend[]
     */
    public function findSentBy(Member $member): array
    {
        return $this->pendingFor($member)
            ->andWhere('f.requester = :member')
            ->getQuery()
            ->getResult()
        ;
    }

    private function confirmedFor(Member $member): QueryBuilder
    {
        return $this->withActiveMembers($member)
            ->andWhere('f.confirmed IS NOT NULL')
            ->orderBy('f.confirmed', 'DESC')
        ;
    }

    private function pendingFor(Member $member): QueryBuilder
    {
        return $this->withActiveMembers($member)
            ->andWhere('f.confirmed IS NULL')
            ->orderBy('f.created', 'DESC')
        ;
    }

    private function withActiveMembers(Member $member): QueryBuilder
    {
        return $this->createQueryBuilder('f')
            ->join('f.left', 'l')
            ->join('f.right', 'r')
            ->where('f.left = :member OR f.right = :member')
            ->andWhere('l.status IN (:status)')
            ->andWhere('r.status IN (:status)')
            ->setParameter('member', $member)
            ->setParameter('status', MemberStatusType::ACTIVE_ALL_ARRAY)
        ;
    }
}
