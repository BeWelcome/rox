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

        return $this->writePersonalDataFile(
            [
                'posts' => $posts,
            ],
            'posts',
            $tempDir . 'posts.html'
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
            try {
                // Some threads do not have an valid group id. We check by trying to access the group's name
                if ($group) {
                    $group->getName();
                }
            } catch (\Exception $e) {
                $group = null;
            }
        }

        return [$thread, $group];
    }
}
