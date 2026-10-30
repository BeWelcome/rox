<?php

namespace App\Model;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ReportStatusType;
use App\Doctrine\WhoSpokeLastType;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\Member;
use App\Entity\ReportToModerator;
use App\Service\ForumSearchIndex;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Attribute\AsTwigFunction;

/**
 * What forum moderators do: a thread's group, pin, visibility and deletion; a post's visibility, deletion and whether
 * its author can still edit it; the reports members send them about posts.
 */
class ForumModerationModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ForumSearchIndex $searchIndex,
    ) {
    }

    public function updateThread(ForumThread $thread, Group $group, int $stickyValue, string $visibility, bool $deleted, ?DateTime $expiryDate): void
    {
        $thread
            ->setGroup($group)
            ->setStickyValue($stickyValue)
            ->setVisibility($visibility)
            ->setDeleted($deleted ? ForumDeleteStatusType::DELETED : ForumDeleteStatusType::NOT_DELETED)
            ->setExpiryDate($expiryDate);
        $this->entityManager->flush();

        foreach ($thread->getPosts() as $post) {
            $this->searchIndex->index($post);
        }
    }

    /**
     * A deleted post can't be the thread's last post: the newest post that isn't deleted is; a thread without any is
     * deleted too.
     */
    public function updatePost(ForumPost $post, bool $ownerCanStillEdit, string $visibility, bool $deleted): void
    {
        $post
            ->setOwnerCanStillEdit($ownerCanStillEdit ? 'Yes' : 'No')
            ->setPostvisibility($visibility)
            ->setDeleted($deleted ? ForumDeleteStatusType::DELETED : ForumDeleteStatusType::NOT_DELETED);
        $this->entityManager->flush();

        $thread = $post->getThread();
        if (null !== $thread) {
            $last = $this->entityManager->getRepository(ForumPost::class)->findOneBy(
                ['thread' => $thread, 'deleted' => ForumDeleteStatusType::NOT_DELETED],
                ['created' => 'DESC', 'id' => 'DESC'],
            );
            if (null === $last) {
                $thread->setDeleted(ForumDeleteStatusType::DELETED);
            } else {
                $thread->setLastPost($last);
            }
            $this->entityManager->flush();
        }

        $this->searchIndex->index($post);
    }

    /**
     * A member's report about a post, or what they add to it: each message is kept with its time and author, the
     * newest first.
     */
    public function report(ForumPost $post, Member $reporter, string $comment): ReportToModerator
    {
        $report = $this->findReport($post, $reporter);
        if (null === $report) {
            $report = new ReportToModerator();
            $report->setPost($post);
            $report->setReporter($reporter);
            $report->setModeratorComment('');
            $report->setStatus(ReportStatusType::OPEN);
            $this->entityManager->persist($report);
        }
        $report->setPostComment($this->addMessage($report->getPostComment(), $reporter, $comment));
        $report->setLastWhoSpoke(WhoSpokeLastType::MEMBER);
        $this->entityManager->flush();

        return $report;
    }

    /**
     * A moderator answers a report and sets its status.
     */
    public function answerReport(ReportToModerator $report, Member $moderator, string $comment, string $status): void
    {
        if ('' !== trim($comment)) {
            $report->setPostComment($this->addMessage($report->getPostComment(), $moderator, $comment));
            $report->setLastWhoSpoke(WhoSpokeLastType::MODERATOR);
        }
        $report->setModerator($moderator);
        $report->setStatus($status);
        $this->entityManager->flush();
    }

    public function findReport(ForumPost $post, Member $reporter): ?ReportToModerator
    {
        return $this->entityManager->getRepository(ReportToModerator::class)->findOneBy([
            'post' => $post->getId(),
            'reporter' => $reporter,
        ]);
    }

    /**
     * @param list<string> $statuses none for all
     *
     * @return list<ReportToModerator> oldest change first
     */
    public function findReports(array $statuses, ?Member $moderator = null): array
    {
        $criteria = [];
        if ([] !== $statuses) {
            $criteria['status'] = $statuses;
        }
        if (null !== $moderator) {
            $criteria['moderator'] = $moderator;
        }

        return $this->entityManager->getRepository(ReportToModerator::class)->findBy($criteria, ['updated' => 'ASC']);
    }

    /**
     * The reports the moderators still have to answer (the number on their tab of the forum pages).
     */
    #[AsTwigFunction('forum_open_reports')]
    public function countOpenReports(): int
    {
        return $this->entityManager->getRepository(ReportToModerator::class)
            ->count(['status' => [ReportStatusType::OPEN, ReportStatusType::IN_DISCUSSION]]);
    }

    /**
     * @param list<int> $postIds
     *
     * @return array<int, list<ReportToModerator>> the reports of the posts, by post id
     */
    public function findReportsOfPosts(array $postIds): array
    {
        $reports = [];
        if ([] === $postIds) {
            return $reports;
        }
        foreach ($this->entityManager->getRepository(ReportToModerator::class)->findBy(['post' => $postIds]) as $report) {
            $reports[$report->getPostId()][] = $report;
        }

        return $reports;
    }

    private function addMessage(?string $messages, Member $author, string $comment): string
    {
        $message = \sprintf(
            '<p><small>%s %s:</small><br>%s</p>',
            new DateTime()->format('Y-m-d H:i'),
            htmlspecialchars($author->getUsername()),
            nl2br(htmlspecialchars(trim($comment))),
        );

        return '' === trim((string) $messages) ? $message : $message . "<hr>\n" . $messages;
    }
}
