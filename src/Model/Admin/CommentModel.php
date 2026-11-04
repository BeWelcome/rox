<?php

namespace App\Model\Admin;

use App\Doctrine\CommentAdminActionType;
use App\Doctrine\CommentQualityType;
use App\Entity\Comment;
use App\Entity\Member;
use App\Repository\CommentReportRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * The comments tool of the volunteers: its lists, the counts of its tabs, and what it shows of one comment.
 */
class CommentModel
{
    public const int PER_PAGE = 10;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CommentReportRepository $reportRepository,
    ) {
    }

    /**
     * @param ?Member $member     the member of CommentQueue::About and CommentQueue::By
     * @param bool    $safetyTeam whether the list is for the Safety Team: only they see the comments handed to them
     *
     * @return Pagerfanta<Comment> the newest first; a page after the last one is the last page
     */
    public function getQueue(CommentQueue $queue, int $page, ?Member $member = null, bool $safetyTeam = true): Pagerfanta
    {
        $repository = $this->comments();
        $query = match ($queue) {
            CommentQueue::SafetyTeam => $repository->queryAllByAdminAction(CommentAdminActionType::SAFETY_TEAM_CHECK),
            CommentQueue::Reported => $repository->queryAllByAdminAction(CommentAdminActionType::ADMIN_CHECK),
            CommentQueue::Checked => $repository->queryAllByAdminAction(CommentAdminActionType::ADMIN_CHECKED),
            CommentQueue::Negative => $repository->queryAllByQuality(CommentQualityType::NEGATIVE),
            CommentQueue::All => $repository->queryAll(),
            CommentQueue::About => $repository->queryAllForMember($member ?? throw new InvalidArgumentException('About whom?')),
            CommentQueue::By => $repository->queryAllFromMember($member ?? throw new InvalidArgumentException('By whom?')),
        };
        if (!$safetyTeam) {
            $query
                ->andWhere('c.adminAction != :safetyTeamCheck')
                ->setParameter('safetyTeamCheck', CommentAdminActionType::SAFETY_TEAM_CHECK);
        }

        $pager = new Pagerfanta(new QueryAdapter($query));
        $pager->setMaxPerPage(self::PER_PAGE);
        $pager->setNormalizeOutOfRangePages(true);
        $pager->setCurrentPage(max(1, $page));

        return $pager;
    }

    /**
     * @return array<string, int> the comments to check per tab (CommentQueue values)
     */
    public function getCountsToCheck(): array
    {
        return [
            CommentQueue::Reported->value => $this->comments()->countByAdminAction(CommentAdminActionType::ADMIN_CHECK),
            CommentQueue::SafetyTeam->value => $this->comments()->countByAdminAction(CommentAdminActionType::SAFETY_TEAM_CHECK),
        ];
    }

    public function find(int $id): ?Comment
    {
        return $this->comments()->find($id);
    }

    public function findBetween(Member $toMember, Member $fromMember): ?Comment
    {
        return $this->comments()->findOneBy(['toMember' => $toMember, 'fromMember' => $fromMember]);
    }

    /**
     * The comment, the other member's comment back, the reports about it, and in the list which comments have a
     * report.
     *
     * @param iterable<Comment> $list
     *
     * @return array{reply: ?Comment, reports: list<\App\Entity\CommentReport>, reported: array<int, true>}
     */
    public function getDetails(?Comment $comment, iterable $list): array
    {
        return [
            'reply' => null === $comment ? null : $this->findBetween($comment->getFromMember(), $comment->getToMember()),
            'reports' => null === $comment ? [] : $this->reportRepository->findForComment($comment),
            'reported' => $this->reportRepository->reportedAmong($list),
        ];
    }

    private function comments(): CommentRepository
    {
        /** @var CommentRepository $repository */
        $repository = $this->entityManager->getRepository(Comment::class);

        return $repository;
    }
}
