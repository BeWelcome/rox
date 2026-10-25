<?php

namespace App\Repository;

use App\Doctrine\GroupMembershipStatusType;
use App\Entity\GroupMembership;
use App\Entity\Member;
use App\Entity\Poll;
use App\Entity\PollVote;
use DateTimeImmutable;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @extends EntityRepository<Poll>
 */
class PollRepository extends EntityRepository
{
    public const string LIST_OPEN = 'open';
    public const string LIST_CLOSED = 'closed';
    public const string LIST_DRAFTS = 'drafts';
    public const string LIST_ALL = 'all';
    public const string LIST_CONTRIBUTED = 'contributed';

    /**
     * The polls of a list the member may see, the newest first. Drafts are only listed for their creator
     * and for poll admins; polls limited to groups only for members of one of these groups.
     */
    public function findList(string $list, Member $member, bool $isAdmin, int $page, int $items): Pagerfanta
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
        ;
        $ended = 'p.endsAt IS NOT NULL AND p.endsAt <= :now';
        if (\in_array($list, [self::LIST_OPEN, self::LIST_CLOSED], true)) {
            $qb->setParameter('now', new DateTimeImmutable());
        }

        match ($list) {
            self::LIST_OPEN => $qb->andWhere("p.status = :open AND NOT ({$ended})")->setParameter('open', Poll::STATUS_OPEN),
            self::LIST_CLOSED => $qb->andWhere("p.status = :closed OR (p.status = :open AND {$ended})")
                ->setParameter('closed', Poll::STATUS_CLOSED)->setParameter('open', Poll::STATUS_OPEN),
            self::LIST_DRAFTS => $qb->andWhere('p.status = :draft')->setParameter('draft', Poll::STATUS_DRAFT),
            self::LIST_CONTRIBUTED => $qb->andWhere(
                'EXISTS (SELECT pv.id FROM ' . PollVote::class . ' pv WHERE pv.poll = p AND pv.member = :member)'
            ),
            default => $qb,
        };

        if (!$isAdmin || self::LIST_CONTRIBUTED === $list) {
            $qb->setParameter('member', $member);
        }
        if (!$isAdmin) {
            $qb
                ->andWhere('p.status <> :draftOnly OR p.creator = :member')
                ->setParameter('draftOnly', Poll::STATUS_DRAFT)
            ;
            $this->restrictToAllowedGroups($qb);
        }

        $paginator = new Pagerfanta(new QueryAdapter($qb));
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    private function restrictToAllowedGroups(QueryBuilder $qb): void
    {
        $qb
            ->andWhere('SIZE(p.groups) = 0 OR p.creator = :member OR EXISTS ('
                . 'SELECT gm.id FROM ' . GroupMembership::class . ' gm JOIN gm.group gg'
                . ' WHERE gm.member = :member AND gm.status = :in AND gg MEMBER OF p.groups)')
            ->setParameter('in', GroupMembershipStatusType::CURRENT_MEMBER)
        ;
    }
}
