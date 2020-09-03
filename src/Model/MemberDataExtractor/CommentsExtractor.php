<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Comment;
use App\Entity\Member;
use App\Repository\CommentRepository;

final class CommentsExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        // Comments the member left others
        /** @var CommentRepository $commentRepository */
        $commentRepository = $this->getRepository(Comment::class);
        /** @var Comment[] $comments */
        $commentsForMember = $commentRepository->getCommentsForMember($member);
        $commentsFromMember = $commentRepository->getCommentsFromMember($member);
        $comments = [];
        /** @var Comment $comment */
        foreach ($commentsForMember as $comment) {
            $commentArray = [
                'to' => null,
                'from' => $comment,
            ];
            $comments[$comment->getFromMember()->getId()] = $commentArray;
        }

        /** @var Comment $comment */
        foreach ($commentsFromMember as $comment) {
            if (isset($comments[$comment->getToMember()->getId()])) {
                $commentArray = $comments[$comment->getToMember()->getId()];
                $commentArray['to'] = $comment;
            } else {
                $commentArray = [
                    'to' => $comment,
                    'from' => null,
                ];
            }
            $comments[$comment->getToMember()->getId()] = $commentArray;
        }

        return $this->writePersonalDataFile(['comments' => $comments], 'comments', $tempDir . 'comments.html');
    }
}
