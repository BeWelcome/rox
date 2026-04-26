<?php

namespace App\Model\Admin;

use App\Entity\Comment;
use App\Entity\Member;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;

/**
 * Class MessageModel.
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 * Hide logic in DeleteRequestType
 */
class CommentModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function getComments($page = 1, $limit = 10)
    {
        /** @var CommentRepository $repository */
        $repository = $this->entityManager->getRepository(Comment::class);

        return $repository->pageAll($page, $limit);
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function getCommentsForMember(Member $member, $page = 1, $limit = 10)
    {
        /** @var CommentRepository $repository */
        $repository = $this->entityManager->getRepository(Comment::class);

        return $repository->pageAllForMember($member, $page, $limit);
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function getCommentsFromMember(Member $member, $page = 1, $limit = 10)
    {
        /** @var CommentRepository $repository */
        $repository = $this->entityManager->getRepository(Comment::class);

        return $repository->pageAllFromMember($member, $page, $limit);
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function getCommentsByQuality($quality, $page = 1, $limit = 10)
    {
        /** @var CommentRepository $repository */
        $repository = $this->entityManager->getRepository(Comment::class);

        return $repository->pageAllByQuality($quality, $page, $limit);
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function getCommentsByAdminAction($action, $page = 1, $limit = 10)
    {
        /** @var CommentRepository $repository */
        $repository = $this->entityManager->getRepository(Comment::class);

        return $repository->pageAllByAdmiNAction($action, $page, $limit);
    }
}
