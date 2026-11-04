<?php

namespace App\Model;

use App\Entity\CommunityNews;
use App\Entity\CommunityNewsComment;
use App\Entity\CommunityNewsCommentReport;
use App\Entity\Member;
use App\Repository\CommunityNewsCommentReportRepository;
use App\Repository\CommunityNewsCommentRepository;
use App\Repository\CommunityNewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;

/**
 * Community news: the members' list and article with its threaded comments, the news admin's list, members'
 * reports of comments and the checkers' handling of them.
 */
class CommunityNewsModel
{
    /** Replies deeper than this stay at this indentation and name whom they answer. */
    public const int MAX_INDENT = 2;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getPaginator(int $page, int $limit): Pagerfanta
    {
        return $this->news()->pagePublic($page, $limit);
    }

    /**
     * The admin's list: all news, or the published (true) or hidden (false) ones.
     */
    public function getAdminPaginator(?bool $public, int $page, int $limit): Pagerfanta
    {
        return $this->news()->pageByVisibility($public, $page, $limit);
    }

    /**
     * @return array{all: int, published: int, hidden: int}
     */
    public function countByVisibility(): array
    {
        return $this->news()->countByVisibility();
    }

    public function getLatest(): mixed
    {
        return $this->news()->getLatest();
    }

    /**
     * @param iterable<CommunityNews> $news
     *
     * @return array<int, int> the comments of each news, by its id
     */
    public function countComments(iterable $news): array
    {
        $ids = [];
        foreach ($news as $item) {
            $ids[] = $item->getId();
        }

        return $this->comments()->countByNews($ids);
    }

    /**
     * The comments of the news as threads, in the order to show them: each comment with its replies right after it,
     * oldest first. A removed comment stays when it has replies and is left out otherwise.
     *
     * @return list<array{comment: CommunityNewsComment, depth: int, indent: int}> depth 0 for a comment on the news
     */
    public function getThread(CommunityNews $communityNews): array
    {
        $children = [];
        foreach ($communityNews->getComments() as $comment) {
            $children[$comment->getParent()?->getId() ?? 0][] = $comment;
        }

        $thread = [];
        $add = static function (int $parentId, int $depth) use (&$add, &$thread, $children): bool {
            $shown = false;
            foreach ($children[$parentId] ?? [] as $comment) {
                $position = \count($thread);
                $thread[] = ['comment' => $comment, 'depth' => $depth, 'indent' => min($depth, self::MAX_INDENT)];
                $hasReplies = $add($comment->getId(), $depth + 1);
                if ($comment->isDeleted() && !$hasReplies) {
                    array_splice($thread, $position, 1);
                    continue;
                }
                $shown = true;
            }

            return $shown;
        };
        $add(0, 0);

        return $thread;
    }

    public function countVisibleComments(CommunityNews $communityNews): int
    {
        return \count(array_filter(
            $communityNews->getComments()->toArray(),
            static fn (CommunityNewsComment $comment): bool => !$comment->isDeleted(),
        ));
    }

    /**
     * Writes a comment on the news, or a reply when $parent is given. Its title (main shows it) is the start of the
     * text.
     */
    public function addComment(CommunityNews $communityNews, Member $author, string $text, ?CommunityNewsComment $parent): CommunityNewsComment
    {
        $text = trim($text);
        $comment = new CommunityNewsComment();
        $comment->setCommunityNews($communityNews)
            ->setAuthor($author)
            ->setText($text)
            ->setTitle(mb_substr(trim(preg_replace('/\s+/u', ' ', $text) ?? ''), 0, 75))
            ->setParent($parent);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return $comment;
    }

    /**
     * A member's report of a comment; false if they reported it already.
     */
    public function report(CommunityNewsComment $comment, Member $reporter, string $reason, ?string $note): bool
    {
        if (null !== $this->reports()->findOneBy(['comment' => $comment, 'reporter' => $reporter])) {
            return false;
        }
        $note = null === $note || '' === trim($note) ? null : trim($note);
        $this->entityManager->persist(new CommunityNewsCommentReport($comment, $reporter, $reason, $note));
        $this->entityManager->flush();

        return true;
    }

    /**
     * @return array<int, true> the comments of the news the member reported, by id
     */
    public function getReportedBy(Member $member, CommunityNews $communityNews): array
    {
        return $this->reports()->findReportedBy($member, $communityNews);
    }

    /**
     * @return Pagerfanta<CommunityNewsComment>
     */
    public function getReportedComments(int $page, int $limit): Pagerfanta
    {
        return $this->comments()->findReported($page, $limit);
    }

    public function countReportedComments(): int
    {
        return $this->reports()->countReportedComments();
    }

    /**
     * Keep: the comment stays, its reports go.
     *
     * @param list<int> $commentIds
     */
    public function keep(array $commentIds): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(CommunityNewsCommentReport::class, 'r')
            ->where('r.comment IN (:comments)')
            ->setParameter('comments', $commentIds)
            ->getQuery()
            ->execute();
    }

    /**
     * Delete: a comment with replies stays as "Comment removed" for them (its text goes, and its reports), one
     * without is deleted (with its reports).
     *
     * @param list<int> $commentIds
     */
    public function deleteAsSpamByChecker(array $commentIds): void
    {
        $this->keep($commentIds);
        foreach ($this->comments()->findBy(['id' => $commentIds]) as $comment) {
            if ($comment->getReplies()->isEmpty()) {
                $this->entityManager->remove($comment);
            } else {
                $comment->markDeleted();
            }
        }
        $this->entityManager->flush();
    }

    private function news(): CommunityNewsRepository
    {
        /** @var CommunityNewsRepository $repository */
        $repository = $this->entityManager->getRepository(CommunityNews::class);

        return $repository;
    }

    private function comments(): CommunityNewsCommentRepository
    {
        /** @var CommunityNewsCommentRepository $repository */
        $repository = $this->entityManager->getRepository(CommunityNewsComment::class);

        return $repository;
    }

    private function reports(): CommunityNewsCommentReportRepository
    {
        /** @var CommunityNewsCommentReportRepository $repository */
        $repository = $this->entityManager->getRepository(CommunityNewsCommentReport::class);

        return $repository;
    }
}
