<?php

namespace App\Service;

use App\Doctrine\CommentQualityType;
use App\Entity\Comment;
use App\Entity\Member;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Attribute\AsTwigFunction;

/**
 * The number of positive comments a member got, for the thumbs-up tag next to their name (conversation header).
 */
final class MemberCommentCount
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[AsTwigFunction('member_positive_comments')]
    public function positive(Member $member): int
    {
        /** @var CommentRepository $repository */
        $repository = $this->entityManager->getRepository(Comment::class);
        $counts = $repository->getCommentsCountMemberByQuality($member);

        return (int) ($counts[CommentQualityType::POSITIVE] ?? 0);
    }
}
