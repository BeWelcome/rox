<?php

namespace App\Model\Admin;

use App\Entity\Comment;
use App\Entity\Member;
use App\Model\BaseModel;
use App\Repository\CommentRepository;

/**
 * Class MessageModel.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * Hide logic in DeleteRequestType
 */
class CommentModel extends BaseModel
{
    /**
     * @param int $page
     * @param int $limit
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getComments($page = 1, $limit = 10)
    {
        /** @var CommentRepository $repository */
        $repository = $this->em->getRepository(Comment::class);

        return $repository->pageAll($page, $limit);
    }

    /**
     * @param Member $member
     * @param int    $page
     * @param int    $limit
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getCommentsForMember(Member $member, $page = 1, $limit = 10)
    {
        /** @var CommentRepository $repository */
        $repository = $this->em->getRepository(Comment::class);

        return $repository->pageAllForMember($member, $page, $limit);
    }

    /**
     * @param Member $member
     * @param int    $page
     * @param int    $limit
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getCommentsFromMember(Member $member, $page = 1, $limit = 10)
    {
        /** @var CommentRepository $repository */
        $repository = $this->em->getRepository(Comment::class);

        return $repository->pageAllFromMember($member, $page, $limit);
    }

    /**
     * @param $quality
     * @param int $page
     * @param int $limit
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getCommentsByQuality($quality, $page = 1, $limit = 10)
    {
        /** @var CommentRepository $repository */
        $repository = $this->em->getRepository(Comment::class);

        return $repository->pageAllByQuality($quality, $page, $limit);
    }

    /**
     * @param $action
     * @param int $page
     * @param int $limit
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getCommentsByAdminAction($action, $page = 1, $limit = 10)
    {
        /** @var CommentRepository $repository */
        $repository = $this->em->getRepository(Comment::class);

        return $repository->pageAllByAdmiNAction($action, $page, $limit);
    }
}
