<?php

namespace App\Repository;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ForumVisibilityType;
use App\Doctrine\GroupMembershipStatusType;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\Member;
use App\Entity\ReportToModerator;
use App\Model\ForumModerationModel;
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
            // the author of posts by members who aren't on BeWelcome any more is empty
            ->leftJoin('p.author', 'a')
            ->join('t.group', 'g')
            ->orderBy('p.created', 'DESC');

        if (null !== $group) {
            $queryBuilder
                ->andWhere('t.group = :group')
                ->setParameter('group', $group)
                ->orderBy('t.stickyValue', 'DESC')
                ->addOrderBy('p.created', 'DESC');
        }

        // the member is in the thread's group (each use needs its own alias); binds its parameters only when used,
        // as Doctrine refuses parameters a query doesn't use
        $isMember = static function (string $alias) use ($queryBuilder, $member): string {
            $queryBuilder
                ->setParameter('member', $member)
                ->setParameter('in', GroupMembershipStatusType::CURRENT_MEMBER);

            return 'EXISTS (SELECT 1 FROM ' . GroupMembership::class . " {$alias}"
                . " WHERE {$alias}.group = t.group AND {$alias}.member = :member AND {$alias}.status = :in)";
        };

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
     * The forum moderators' filters of a group's discussions: the threads with open reports (reported), or the ones
     * not every member sees (hidden: only for the group or the moderators, hidden after a day, or deleted).
     */
    public function filterForModerators(QueryBuilder $queryBuilder, string $filter): QueryBuilder
    {
        return match ($filter) {
            'reported' => $queryBuilder
                ->andWhere('t.id IN (SELECT rr.thread FROM ' . ReportToModerator::class . ' rr WHERE rr.status IN (:openReports))')
                ->setParameter('openReports', ForumModerationModel::OPEN_STATUSES),
            'hidden' => $queryBuilder
                ->andWhere('t.visibility IN (:restricted) OR t.expiryDate IS NOT NULL OR t.deleted = :deletedThread')
                ->setParameter('restricted', [ForumVisibilityType::GROUP_ONLY, ForumVisibilityType::MODERATOR_ONLY])
                ->setParameter('deletedThread', ForumDeleteStatusType::DELETED),
            default => $queryBuilder,
        };
    }

    /**
     * @return array{reported: int, hidden: int} the number of the group's threads in each of the moderators' filters
     */
    public function countForModerators(Member $member, Group $group): array
    {
        $count = fn (string $filter): int => (int) $this->filterForModerators($this->createLatestQueryBuilder($member, true, $group), $filter)
            ->select('COUNT(t.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();

        return ['reported' => $count('reported'), 'hidden' => $count('hidden')];
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
