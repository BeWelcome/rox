<?php

namespace App\Repository;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ForumVisibilityType;
use App\Doctrine\GroupMembershipStatusType;
use App\Entity\ForumThread;
use App\Entity\GroupMembership;
use App\Entity\Member;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class ForumPostRepository extends EntityRepository
{
    /**
     * The posts of a thread the member may read (ForumVoter::POST_VIEW), oldest or newest first.
     */
    public function createThreadPostsQueryBuilder(ForumThread $thread, Member $member, bool $moderator, bool $ascending): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('fp')
            ->select('fp', 'a')
            // the author of posts by members who aren't on BeWelcome any more is empty
            ->leftJoin('fp.author', 'a')
            ->where('fp.thread = :thread')
            ->setParameter('thread', $thread)
            ->orderBy('fp.created', $ascending ? 'ASC' : 'DESC')
            ->addOrderBy('fp.id', $ascending ? 'ASC' : 'DESC');
        if ($moderator) {
            return $queryBuilder;
        }

        $queryBuilder
            ->andWhere('fp.deleted = :notDeleted')
            ->andWhere('fp.postvisibility <> :moderators')
            ->setParameter('notDeleted', ForumDeleteStatusType::NOT_DELETED)
            ->setParameter('moderators', ForumVisibilityType::MODERATOR_ONLY);

        $group = $thread->getGroup();
        if (null !== $group && !$group->isBeWelcome()) {
            $queryBuilder
                ->andWhere('fp.postvisibility <> :groupOnly OR EXISTS (SELECT 1 FROM ' . GroupMembership::class . ' gm'
                    . ' WHERE gm.group = :group AND gm.member = :member AND gm.status = :in)')
                ->setParameter('groupOnly', ForumVisibilityType::GROUP_ONLY)
                ->setParameter('group', $group)
                ->setParameter('member', $member)
                ->setParameter('in', GroupMembershipStatusType::CURRENT_MEMBER);
        }

        return $queryBuilder;
    }

    public function getForumPostsByMemberCount(Member $member): int
    {
        return $this->getForumPostsByMemberQueryBuilder($member)
            ->select('count(fp.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getForumPostsByMember(Member $member, string $search, int $page, int $itemsPerPage): Pagerfanta
    {
        $queryBuilder = $this->getForumPostsByMemberQueryBuilder($member);
        if (!empty($search)) {
            $parts = explode(' ', $search);
            foreach ($parts as $part) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->like('fp.message', $queryBuilder->expr()->literal('%' . $part . '%'))
                );
            }
        }
        $queryBuilder->orderBy('fp.created', 'DESC');

        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta
            ->setMaxPerPage($itemsPerPage)
            ->setCurrentPage($page)
        ;

        return $pagerfanta;
    }

    public function countSearchIndexDocuments(): int
    {
        return (int) $this->getEntityManager()->getConnection()->fetchOne(<<<'SQL'
                SELECT COUNT(*) FROM forum_post fp JOIN forum_thread ft ON fp.threadid = ft.id
            SQL);
    }

    /**
     * Documents for the forum_rt search index: one per post, with the post's own text and language
     * (forum_post.message and .ShortCode; translations in forum_post_translation are optional).
     *
     * @return list<array{post_id: int, post_deleted: string, post_visibility: string, thread_id: int,
     *     thread_deleted: string, thread_visibility: string, content: string, group: int, author: int,
     *     locale: string}>
     */
    public function getSearchIndexDocuments(int $offset, int $limit): array
    {
        $rows = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            <<<'SQL'
                SELECT
                    fp.id AS post_id,
                    fp.PostDeleted AS post_deleted,
                    fp.PostVisibility AS post_visibility,
                    ft.id AS thread_id,
                    ft.ThreadDeleted AS thread_deleted,
                    ft.ThreadVisibility AS thread_visibility,
                    fp.message AS content,
                    COALESCE(ft.IdGroup, 0) AS `group`,
                    fp.IdWriter AS author,
                    fp.ShortCode AS locale
                FROM forum_post fp
                JOIN forum_thread ft ON fp.threadid = ft.id
                ORDER BY fp.id
                LIMIT ? OFFSET ?
                SQL,
            [$limit, $offset],
            [ParameterType::INTEGER, ParameterType::INTEGER],
        );

        // Manticore integer attributes need integers
        return array_map(static fn (array $row): array => [
            'post_id' => (int) $row['post_id'],
            'post_deleted' => (string) $row['post_deleted'],
            'post_visibility' => (string) $row['post_visibility'],
            'thread_id' => (int) $row['thread_id'],
            'thread_deleted' => (string) $row['thread_deleted'],
            'thread_visibility' => (string) $row['thread_visibility'],
            'content' => (string) $row['content'],
            'group' => (int) $row['group'],
            'author' => (int) $row['author'],
            'locale' => (string) $row['locale'],
        ], $rows);
    }

    private function getForumPostsByMemberQueryBuilder(Member $member): QueryBuilder
    {
        $qb = $this->createQueryBuilder('fp');
        $qb
            ->where('fp.author = :member')
            ->setParameter('member', $member)
        ;

        return $qb;
    }
}
