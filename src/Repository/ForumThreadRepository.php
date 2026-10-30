<?php

namespace App\Repository;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ForumVisibilityType;
use App\Doctrine\GroupMembershipStatusType;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends EntityRepository<ForumThread>
 */
class ForumThreadRepository extends EntityRepository
{
    /**
     * The threads the member may read, the one with the newest post first: all groups' (the forum overview), the
     * member's groups' only, or one group's (its forum, sticky threads first). Mirrors ForumVoter.
     */
    public function createLatestQueryBuilder(Member $member, bool $moderator, ?Group $group = null, bool $onlyMyGroups = false): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->select('t', 'p', 'a', 'g')
            ->join('t.lastPost', 'p')
            ->join('p.author', 'a')
            ->join('t.group', 'g')
            ->orderBy('p.created', 'DESC');

        if (null !== $group) {
            $queryBuilder
                ->andWhere('t.group = :group')
                ->setParameter('group', $group)
                ->orderBy('t.stickyValue', 'DESC')
                ->addOrderBy('p.created', 'DESC');
        }

        // the member is in the thread's group (each use needs its own alias)
        $isMember = static fn (string $alias): string => 'EXISTS (SELECT 1 FROM ' . GroupMembership::class . " {$alias}"
            . " WHERE {$alias}.group = t.group AND {$alias}.member = :member AND {$alias}.status = :in)";
        $queryBuilder
            ->setParameter('member', $member)
            ->setParameter('in', GroupMembershipStatusType::CURRENT_MEMBER);

        if ($onlyMyGroups) {
            $queryBuilder->andWhere($isMember('gm1'));
        }

        if ($moderator) {
            // moderators see deleted threads too, but the overviews leave them out
            if (null === $group) {
                $queryBuilder
                    ->andWhere('t.deleted = :notDeleted')
                    ->setParameter('notDeleted', ForumDeleteStatusType::NOT_DELETED);
            }

            return $queryBuilder;
        }

        return $queryBuilder
            ->andWhere('t.deleted = :notDeleted')
            ->andWhere('g.membersOnly = false OR ' . $isMember('gm2'))
            ->andWhere('t.visibility IN (:forAll) OR (t.visibility = :groupOnly AND (g.id = :beWelcome OR ' . $isMember('gm3') . '))')
            ->setParameter('notDeleted', ForumDeleteStatusType::NOT_DELETED)
            // an empty visibility (old rows) counts as for all members, as in ForumVoter
            ->setParameter('forAll', [ForumVisibilityType::NO_RESTRICTION, ForumVisibilityType::MEMBERS_ONLY, ''])
            ->setParameter('groupOnly', ForumVisibilityType::GROUP_ONLY)
            ->setParameter('beWelcome', Group::BEWELCOME);
    }

    /**
     * Counts the visit of a thread.
     */
    public function countView(ForumThread $thread): void
    {
        $this->getEntityManager()->getConnection()->executeStatement(
            'UPDATE forum_thread SET views = views + 1 WHERE id = ?',
            [$thread->getId()],
        );
    }
}
