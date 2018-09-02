<?php

namespace AppBundle\Model\Admin;

use AppBundle\Doctrine\DeleteRequestType;
use AppBundle\Doctrine\InFolderType;
use AppBundle\Doctrine\MessageStatusType;
use AppBundle\Doctrine\SpamInfoType;
use AppBundle\Entity\Comment;
use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use AppBundle\Model\BaseModel;
use AppBundle\Repository\CommentRepository;
use AppBundle\Repository\MessageRepository;
use Doctrine\DBAL\DBALException;
use PDO;

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
