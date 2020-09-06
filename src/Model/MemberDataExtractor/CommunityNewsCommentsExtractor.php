<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\CommunityNewsComment;
use App\Entity\Member;

final class CommunityNewsCommentsExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        // Get community news comments the member wrote
        $commentRepository = $this->getRepository(CommunityNewsComment::class);
        $comments = $commentRepository->findBy(['author' => $member]);

        $newsAndComments = [];
        /** @var CommunityNewsComment $comment */
        foreach ($comments as $comment) {
            if (!isset($newsAndComments[$comment->getCommunityNews()->getId()])) {
                $commentWithNews = [
                    'news' => $comment->getCommunityNews(),
                    'comments' => [],
                ];
            } else {
                $commentWithNews = $newsAndComments[$comment->getCommunityNews()->getId()];
            }
            $commentWithNews['comments'][] = $comment;
            $newsAndComments[$comment->getCommunityNews()->getId()] = $commentWithNews;
        }

        return $this->writePersonalDataFile(['newsAndComments' => $newsAndComments], 'communitynews_comments', $tempDir . 'communitynews_comments.html');
    }
}
