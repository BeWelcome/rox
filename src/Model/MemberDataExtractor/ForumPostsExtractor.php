<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\ForumPost;
use App\Entity\Member;
use App\Repository\ForumPostRepository;

final class ForumPostsExtractor extends AbstractExtractor implements ExtractorInterface
{
    public function extract(Member $member, string $tempDir): array
    {
        // now all posts to the forum or groups including status
        /** @var ForumPostRepository $forumRepository */
        $forumRepository = $this->getRepository(ForumPost::class);
        /** @var ForumPost $posts */
        $posts = $forumRepository->findBy(['author' => $member], ['created' => 'DESC']);

        $this->writePersonalDataFile(
            [
                'posts' => $posts,
            ],
            'posts',
            $tempDir . 'posts.html'
        );

        return ['posts' => \count($posts)];
    }
}
