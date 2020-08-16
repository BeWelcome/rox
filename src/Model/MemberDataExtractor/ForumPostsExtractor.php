<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\ForumPost;
use App\Entity\Member;

final class ForumPostsExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        // now all posts to the forum or groups including status
        $forumRepository = $this->getRepository(ForumPost::class);
        /** @var ForumPost $posts */
        $posts = $forumRepository->findBy(['author' => $member], ['created' => 'DESC']);
        $i = 1;
        $postsPerYear = [];
        $threadsPerYear = [];
        $threadsContributed = [];
        /** @var ForumPost $post */
        foreach ($posts as $post) {
            list($thread, $group) = $this->getThreadAndGroup($post);
            $this->writePersonalDataFileSubdirectory(
                [
                    'thread' => $thread,
                    'group' => $group,
                    'post' => $post,
                ],
                'post',
                $tempDir . 'posts',
                'post-' . $post->getCreated()->toDateString() . '-' . $i
            );
            $year = $post->getCreated()->year;
            if (!isset($threadsPerYear[$year])) {
                $threadsPerYear[$year] = [];
                $postsPerYear[$year] = 0;
            }
            $thread = $post->getThread();
            $threadId = (null === $thread) ? 0 : $thread->getId();
            if (!isset($threadsPerYear[$year][$threadId])) {
                $threadsContributed[$threadId] = $threadId;
                $threadsPerYear[$year][$threadId] = [
                    'thread' => $thread,
                    'posts' => [],
                    'count' => 0,
                ];
            }
            $threadsPerYear[$year][$threadId]['count'] = $threadsPerYear[$year][$threadId]['count'] + 1;
            $threadsPerYear[$year][$threadId]['posts'][$i] = $post;
            $postsPerYear[$year] = $postsPerYear[$year] + 1;
            ++$i;
        }
        foreach (array_keys($threadsPerYear) as $year) {
            $this->writePersonalDataFileSubDirectory(
                [
                    'year' => $year,
                    'post_count' => $postsPerYear[$year],
                    'threads' => $threadsPerYear[$year],
                    'thread_count' => \count(array_keys($threadsPerYear[$year])),
                ],
                'posts_year',
                $tempDir . 'posts',
                'posts-' . $year
            );
        }

        return $this->writePersonalDataFile(
            [
                'years' => array_keys($threadsPerYear),
                'threadsPerYear' => $threadsPerYear,
                'postsPerYear' => $postsPerYear,
                'threads_contributed' => \count($threadsContributed),
                'posts_written' => $i - 1,
            ],
            'posts'
        );
    }

    private function getThreadAndGroup(ForumPost $post)
    {
        $group = null;
        try {
            // Some posts do not have an valid thread id. We check by trying to access the thread's title
            $thread = $post->getThread();
            if ($thread) {
                $thread->getTitle();
            }
        } catch (\Exception $e) {
            $thread = null;
        }
        if (null !== $thread) {
            $group = $thread->getGroup();
            if ($group) {
                $group->getName();
            }
        }

        return [$thread, $group];
    }
}
