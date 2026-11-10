<?php

namespace App\Repository;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ForumVisibilityType;
use App\Doctrine\GroupMembershipStatusType;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group;
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

    /**
     * The page of the thread the post is on, for the member and their order of the posts.
     */
    public function findPageOf(ForumPost $post, Member $member, bool $moderator, bool $ascending, int $perPage): int
    {
        $thread = $post->getThread();
        if (null === $thread) {
            return 1;
        }
        $before = (int) $this->createThreadPostsQueryBuilder($thread, $member, $moderator, $ascending)
            ->select('COUNT(fp.id)')
            ->resetDQLPart('orderBy')
            ->andWhere($ascending
                ? 'fp.created < :created OR (fp.created = :created AND fp.id < :post)'
                : 'fp.created > :created OR (fp.created = :created AND fp.id > :post)')
            ->setParameter('created', $post->getCreated())
            ->setParameter('post', $post->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return intdiv($before, max(1, $perPage)) + 1;
    }

    /**
     * How many of the member's posts the viewer may read (see getForumPostsByMemberQueryBuilder()).
     */
    public function getForumPostsByMemberCount(Member $member, Member $viewer, bool $moderator): int
    {
        return (int) $this->getForumPostsByMemberQueryBuilder($member, $viewer, $moderator)
            ->select('count(fp.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * The member's posts the viewer may read, newest first; with $search those with all its words.
     */
    public function getForumPostsByMember(
        Member $member,
        Member $viewer,
        bool $moderator,
        string $search,
        int $page,
        int $itemsPerPage,
    ): Pagerfanta {
        $queryBuilder = $this->getForumPostsByMemberQueryBuilder($member, $viewer, $moderator);
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

    /**
     * The member's posts the viewer may read in the forum (ForumVoter::POST_VIEW), as the thread lists check it
     * (ForumThreadRepository::createLatestQueryBuilder()): not deleted, in a thread that isn't deleted, of a group
     * whose forum the viewer may read (members-only groups for their members), and, when a thread or post is for the
     * group's members only, the viewer is one (everybody is in the agora). Posts for moderators only are left out.
     * Forum moderators and admins see all of them.
     */
    private function getForumPostsByMemberQueryBuilder(Member $member, Member $viewer, bool $moderator): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('fp')
            ->join('fp.thread', 't')
            ->join('t.group', 'g')
            ->where('fp.author = :member')
            ->setParameter('member', $member)
        ;
        if ($moderator) {
            return $queryBuilder;
        }

        // the viewer is in the thread's group (each use needs its own alias)
        $isMember = static fn (string $alias): string => 'EXISTS (SELECT 1 FROM ' . GroupMembership::class . " {$alias}"
            . " WHERE {$alias}.group = t.group AND {$alias}.member = :viewer AND {$alias}.status = :in)";

        return $queryBuilder
            ->andWhere('fp.deleted = :notDeleted')
            ->andWhere('t.deleted = :notDeleted')
            ->andWhere('g.membersOnly = false OR ' . $isMember('gm1'))
            ->andWhere('t.visibility IN (:forAll) OR (t.visibility = :groupOnly AND (g.id = :beWelcome OR ' . $isMember('gm2') . '))')
            ->andWhere('fp.postvisibility IN (:forAll) OR (fp.postvisibility = :groupOnly AND (g.id = :beWelcome OR ' . $isMember('gm3') . '))')
            ->setParameter('viewer', $viewer)
            ->setParameter('in', GroupMembershipStatusType::CURRENT_MEMBER)
            ->setParameter('notDeleted', ForumDeleteStatusType::NOT_DELETED)
            // an empty visibility (old rows) counts as for all members, as in ForumVoter
            ->setParameter('forAll', [ForumVisibilityType::NO_RESTRICTION, ForumVisibilityType::MEMBERS_ONLY, ''])
            ->setParameter('groupOnly', ForumVisibilityType::GROUP_ONLY)
            ->setParameter('beWelcome', Group::BEWELCOME);
    }
}
