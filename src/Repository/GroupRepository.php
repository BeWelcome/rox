<?php

namespace App\Repository;

use App\Doctrine\GroupMembershipStatusType;
use App\Doctrine\MemberStatusType;
use App\Dto\GroupSearch;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\Member;
use DateTimeImmutable;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends EntityRepository<Group>
 */
class GroupRepository extends EntityRepository
{
    private const array ORDERS = [
        'nameasc' => ['g.name', 'ASC'],
        'namedesc' => ['g.name', 'DESC'],
        'membersasc' => ['memberCount', 'ASC'],
        'membersdesc' => ['memberCount', 'DESC'],
        'actasc' => ['lastActivity', 'ASC'],
        'actdesc' => ['lastActivity', 'DESC'],
        'createdasc' => ['g.created', 'ASC'],
        'createddesc' => ['g.created', 'DESC'],
    ];

    /**
     * Approved groups matching the search, each row the group (key 0) with its number of members (memberCount),
     * of members who joined during the last two weeks (newMemberCount) and the time of its newest post (lastActivity).
     */
    public function createSearchQueryBuilder(GroupSearch $search): QueryBuilder
    {
        $queryBuilder = $this->createListQueryBuilder($search->order)
            ->andWhere('g.approved = :approved')
            ->setParameter('approved', Group::APPROVED);

        $nameParts = $search->getNameParts();
        if ([] !== $nameParts) {
            $orX = $queryBuilder->expr()->orX();
            foreach ($nameParts as $i => $part) {
                $orX->add('g.name LIKE :name' . $i);
                $queryBuilder->setParameter('name' . $i, '%' . addcslashes($part, '%_') . '%');
            }
            $queryBuilder->andWhere($orX);
        }

        if (null !== $search->category) {
            $queryBuilder
                ->andWhere(':category MEMBER OF g.categories')
                ->setParameter('category', $search->category);
        }

        if (null !== $search->location) {
            // the location itself or anything in it
            $queryBuilder
                ->join('g.location', 'l')
                ->andWhere('l.geonameId = :location OR IDENTITY(l.country) = :location OR IDENTITY(l.admin1) = :location'
                    . ' OR IDENTITY(l.admin2) = :location OR IDENTITY(l.admin3) = :location OR IDENTITY(l.admin4) = :location')
                ->setParameter('location', $search->location);
        }

        return $queryBuilder;
    }

    /**
     * The groups the member is in, rows as in createSearchQueryBuilder().
     */
    public function createMyGroupsQueryBuilder(Member $member, string $order = 'nameasc'): QueryBuilder
    {
        return $this->createListQueryBuilder($order)
            ->join('g.groupMemberships', 'mine', Join::WITH, 'mine.member = :member AND mine.status = :in')
            ->setParameter('member', $member)
            ->setParameter('in', GroupMembershipStatusType::CURRENT_MEMBER);
    }

    /**
     * The member's membership status in the group (GroupMembershipStatusType), null if they never had one.
     */
    public function findMembershipStatus(Group $group, Member $member): ?string
    {
        $status = $this->getEntityManager()->createQueryBuilder()
            ->select('gm.status')
            ->from(GroupMembership::class, 'gm')
            ->where('gm.group = :group')
            ->andWhere('gm.member = :member')
            ->setParameter('group', $group)
            ->setParameter('member', $member)
            ->getQuery()
            ->getOneOrNullResult();

        return null === $status ? null : $status['status'];
    }

    /**
     * The group's current members whose accounts are active, newest first, with their memberships.
     */
    public function createMembersQueryBuilder(Group $group): QueryBuilder
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('gm', 'm')
            ->from(GroupMembership::class, 'gm')
            ->join('gm.member', 'm')
            ->where('gm.group = :group')
            ->andWhere('gm.status = :in')
            ->andWhere('m.status IN (:active)')
            ->setParameter('group', $group)
            ->setParameter('in', GroupMembershipStatusType::CURRENT_MEMBER)
            ->setParameter('active', MemberStatusType::ACTIVE_ALL_ARRAY)
            ->orderBy('gm.created', 'DESC');
    }

    public function countMembers(Group $group): int
    {
        return (int) $this->createMembersQueryBuilder($group)
            ->select('COUNT(gm.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * The group's threads with the newest posts, deleted ones left out.
     *
     * @return list<ForumThread>
     */
    public function findLatestThreads(Group $group, int $limit): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('t', 'p')
            ->from(ForumThread::class, 't')
            ->join('t.lastPost', 'p')
            ->where('t.group = :group')
            ->andWhere("t.deleted = 'NotDeleted'")
            ->setParameter('group', $group)
            ->orderBy('p.created', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets all groups which name uses any of the provided name parts.
     *
     * @return array
     */
    public function findByNameParts(array $nameParts)
    {
        $qb = $this->createQueryBuilder('g');
        for ($i = 0; $i < \count($nameParts); ++$i) {
            $qb->orWhere('g.name like :part' . $i)
                ->setParameter('part' . $i, '%' . $nameParts[$i] . '%');
        }
        $qb->setMaxResults(12);

        return $qb
            ->getQuery()
            ->getResult();
    }

    private function createListQueryBuilder(string $order): QueryBuilder
    {
        $entityManager = $this->getEntityManager();
        $members = $entityManager->createQueryBuilder()
            ->select('COUNT(gm1.id)')
            ->from(GroupMembership::class, 'gm1')
            ->join('gm1.member', 'm1')
            ->where('gm1.group = g')
            ->andWhere('gm1.status = :in1')
            ->andWhere('m1.status IN (:active1)');
        $newMembers = $entityManager->createQueryBuilder()
            ->select('COUNT(gm2.id)')
            ->from(GroupMembership::class, 'gm2')
            ->join('gm2.member', 'm2')
            ->where('gm2.group = g')
            ->andWhere('gm2.status = :in1')
            ->andWhere('m2.status IN (:active1)')
            ->andWhere('gm2.created >= :newSince');
        $lastActivity = $entityManager->createQueryBuilder()
            ->select('MAX(p3.created)')
            ->from(ForumThread::class, 't3')
            ->join(ForumPost::class, 'p3', Join::WITH, 't3.lastPost = p3')
            ->where('t3.group = g')
            ->andWhere("t3.deleted = 'NotDeleted'");

        [$sort, $direction] = self::ORDERS[$order] ?? self::ORDERS['nameasc'];

        return $this->createQueryBuilder('g')
            ->addSelect('(' . $members->getDQL() . ') AS memberCount')
            ->addSelect('(' . $newMembers->getDQL() . ') AS newMemberCount')
            ->addSelect('(' . $lastActivity->getDQL() . ') AS lastActivity')
            ->setParameter('in1', GroupMembershipStatusType::CURRENT_MEMBER)
            ->setParameter('active1', MemberStatusType::ACTIVE_ALL_ARRAY)
            ->setParameter('newSince', new DateTimeImmutable('-2 weeks'))
            ->orderBy($sort, $direction)
            ->addOrderBy('g.name', 'ASC');
    }
}
