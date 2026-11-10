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
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Twig\Attribute\AsTwigFunction;

/**
 * What forum moderators do: a thread's group, pin, visibility and deletion; a post's visibility, deletion and whether
 * its author can still edit it; the reports members send them about posts.
 */
class ForumModerationModel
{
    /** The reports the moderators still have to answer. */
    public const array OPEN_STATUSES = [ReportStatusType::OPEN, ReportStatusType::IN_DISCUSSION];

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
     * @param Group|null   $group    only the reports about posts in the group's threads
     *
     * @return list<ReportToModerator> oldest change first
     */
    public function findReports(array $statuses, ?Member $moderator = null, ?Group $group = null): array
    {
        $queryBuilder = $this->createReportsQueryBuilder($statuses, $group)->orderBy('r.updated', 'ASC')->addOrderBy('r.id', 'ASC');
        if (null !== $moderator) {
            $queryBuilder->andWhere('r.moderator = :moderator')->setParameter('moderator', $moderator);
        }

        /** @var list<ReportToModerator> $reports */
        $reports = $queryBuilder->getQuery()->getResult();

        return $reports;
    }

    /**
     * The reports the moderators still have to answer (the number on the Reports tab of the group's pages; without a
     * group, of all groups).
     */
    #[AsTwigFunction('forum_open_reports')]
    public function countOpenReports(?Group $group = null): int
    {
        return (int) $this->createReportsQueryBuilder(self::OPEN_STATUSES, $group)
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param list<int> $threadIds
     *
     * @return array<int, int> the number of open reports about posts of each thread, by thread id
     */
    public function countOpenReportsOfThreads(array $threadIds): array
    {
        if ([] === $threadIds) {
            return [];
        }
        $rows = $this->createReportsQueryBuilder(self::OPEN_STATUSES, null)
            ->select('r.thread AS thread, COUNT(r.id) AS reports')
            ->andWhere('r.thread IN (:threads)')
            ->setParameter('threads', $threadIds)
            ->groupBy('r.thread')
            ->getQuery()
            ->getArrayResult();

        return array_map('intval', array_column($rows, 'reports', 'thread'));
    }

    /**
     * The messages of a report, the oldest first. Both the messages written here and the legacy forum's are read;
     * a message that is neither stays one message without time and author. Messages by others than the member who
     * reported the post are the moderators'.
     *
     * @return list<ReportMessage>
     */
    #[AsTwigFunction('forum_report_messages')]
    public function getMessages(ReportToModerator $report): array
    {
        $reporter = $report->getReporter()->getUsername();
        $messages = [];
        foreach (preg_split('~\s*<hr\s*/?>\s*~i', trim((string) $report->getPostComment())) ?: [] as $part) {
            if ('' === trim($part)) {
                continue;
            }
            $time = null;
            $author = null;
            $text = $part;
            if (1 === preg_match('~^<p><small>(\d{4}-\d{2}-\d{2} \d{2}:\d{2}) (.+?):</small><br>(.*)</p>$~s', $part, $match)) {
                $time = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $match[1]) ?: null;
                $author = html_entity_decode($match[2], \ENT_QUOTES | \ENT_HTML5);
                $text = $match[3];
            } elseif (1 === preg_match('~^On (\d{2}-\d{2}-\d{4} \d{2}:\d{2}) \(server time\) <a [^>]*>(.*?)</a> wrote:<br\s*/?>(.*)$~s', $part, $match)) {
                $time = DateTimeImmutable::createFromFormat('!d-m-Y H:i', $match[1]) ?: null;
                $author = html_entity_decode(strip_tags($match[2]), \ENT_QUOTES | \ENT_HTML5);
                $text = $match[3];
            }
            $messages[] = new ReportMessage($time, $author, $text, null !== $author && $author !== $reporter);
        }

        return array_reverse($messages);
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

    /**
     * @param list<string> $statuses none for all
     */
    private function createReportsQueryBuilder(array $statuses, ?Group $group): QueryBuilder
    {
        $queryBuilder = $this->entityManager->getRepository(ReportToModerator::class)->createQueryBuilder('r');
        if ([] !== $statuses) {
            $queryBuilder->andWhere('r.status IN (:statuses)')->setParameter('statuses', $statuses);
        }
        if (null !== $group) {
            $queryBuilder
                ->andWhere('r.thread IN (SELECT gt.id FROM ' . ForumThread::class . ' gt WHERE gt.group = :group)')
                ->setParameter('group', $group);
        }

        return $queryBuilder;
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
