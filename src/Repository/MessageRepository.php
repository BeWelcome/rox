<?php

namespace App\Repository;

use App\Doctrine\DeleteRequestType;
use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\Message;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class MessageRepository extends EntityRepository
{
    public function getUnreadConversationsCount(Member $member): int
    {
        $q = $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->where('m.receiver = :member')
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->andWhere('NOT (m.deleteRequest LIKE :receiverDeleted)')
            ->setParameter(':receiverDeleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
            ->andWhere('NOT (m.deleteRequest LIKE :receiverPurged)')
            ->setParameter(':receiverPurged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
            ->andWhere('m.firstRead IS NULL')
            ->andWhere('m.status = :status')
            ->setParameter('status', 'Sent')
            ->getQuery();

        $unreadCount = $q->getSingleScalarResult();

        return (int) $unreadCount;
    }

    public function getReportedMessagesCount(): int
    {
        $q = $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->where('m.status = :status')
            ->setParameter('status', MessageStatusType::CHECK)
            ->andWhere('m.spaminfo LIKE :spamInfo')
            ->setParameter('spamInfo', SpamInfoType::MEMBER_SAYS_SPAM)
            ->getQuery();

        $result = $q->getSingleScalarResult();

        return $result;
    }

    public function findReportedMessages(int $page = 1, int $items = 10): Pagerfanta
    {
        $queryBuilder = $this->queryReportedMessages();
        $adapter = new QueryAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function findProcessedReportedMessages($page = 1, $items = 10)
    {
        $queryBuilder = $this->queryProcessedReportedMessages();
        $adapter = new QueryAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param $filter
     * @param $sort
     * @param $sortDirection
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function findLatestMessages(Member $member, $filter, $sort, $sortDirection, $page = 1, $items = 10)
    {
        $queryBuilder = $this->queryLatestMessages($member, $filter, $sort, $sortDirection);
        $adapter = new QueryAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param $filter
     * @param $sort
     * @param $sortDirection
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function findLatestRequests(Member $member, $filter, $sort, $sortDirection, $page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(
            new QueryAdapter(
                $this->queryLatestRequests($member, $filter, $sort, $sortDirection),
                false
            )
        );
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param $folder
     * @param $sort
     * @param $sortDirection
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function findLatestRequestsAndMessages(
        Member $member,
        $folder,
        $sort,
        $sortDirection,
        $page = 1,
        $items = 10
    ) {
        $paginator = new Pagerfanta(
            new QueryAdapter(
                $this->queryLatestRequestsAndMessages($member, $folder, $sort, $sortDirection),
                false
            )
        );
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    public function getThread(Message $message)
    {
        $qb = $this->createNativeNamedQuery('get_thread')
            ->setHint('partial', Query::HINT_FORCE_PARTIAL_LOAD);
        $result = $qb->execute([
            'message_id' => $message->getId(),
        ]);

        return $result;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param $sort
     * @param $sortDirection
     * @param int $page
     * @param int $items
     */
    public function findAllMessagesBetween(
        Member $loggedInUser,
        Member $member,
        $sort,
        $sortDirection,
        $page = 1,
        $items = 10
    ): Pagerfanta {
        $paginator = new Pagerfanta(
            new QueryAdapter(
                $this->queryAllMessagesBetween($loggedInUser, $member, $sort, $sortDirection),
                false
            )
        );
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return Message[]
     */
    public function findAllMessagesWithMember(Member $member): array
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->where(
                $qb
                    ->expr()->orX(
                        $qb->expr()->eq('m.sender', ':member'),
                        $qb->expr()->eq('m.receiver', ':member')
                    )
            )
            ->setParameter('member', $member)
            ->orderBy('m.created', 'DESC')
            ->setMaxResults(250)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Collection
     */
    public function getMessagesSentBy(Member $member)
    {
        return $this->createQueryBuilder('m')
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::SENDER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::SENDER_PURGED . '%')
            ->andWhere('m.sender = :member')
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->andWhere('m.request IS NULL')
            ->orderBy('m.created', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Collection
     */
    public function getMessagesReceivedBy(Member $member)
    {
        return $this->createQueryBuilder('m')
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
            ->andWhere('m.receiver = :member')
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->andWhere('m.request IS NULL')
            ->orderBy('m.created', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Collection
     */
    public function getRequestsSentBy(Member $member)
    {
        return $this->createQueryBuilder('m')
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::SENDER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::SENDER_PURGED . '%')
            ->andWhere('m.sender = :member')
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->join('m.request', 'r')
            ->orderBy('m.created', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Collection
     */
    public function getRequestsReceivedBy(Member $member)
    {
        return $this->createQueryBuilder('m')
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
            ->andWhere('m.receiver = :member')
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->join('m.request', 'r')
            ->orderBy('m.created', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getLatestMessagesAndRequests(Member $member, bool $unread, int $limit = 5)
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.receiver = :member')
            ->setParameter('member', $member)
            ->andWhere('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL);
        if ($unread) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('m.firstRead', "'0000-00-00 00:00.00'"),
                        $qb->expr()->isNull('m.firstRead')
                    )
                );
        }

        if (0 !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb
            ->orderBy('m.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return QueryBuilder
     */
    private function queryReportedMessages()
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->where('m.status = :status')
            ->setParameter('status', MessageStatusType::CHECK)
            ->andWhere('m.spaminfo LIKE :spaminfo')
            ->setParameter('spaminfo', '%' . SpamInfoType::MEMBER_SAYS_SPAM . '%')
        ;

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    private function queryProcessedReportedMessages()
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->where('m.status = :status')
            ->setParameter('status', MessageStatusType::CHECKED)
            ->andWhere('m.spaminfo LIKE :spaminfo')
            ->setParameter('spaminfo', '%' . SpamInfoType::CHECKER_SAYS_SPAM . '%')
            ->orderBy('m.created', 'DESC')
        ;

        return $qb;
    }

    /**
     * @param $folder
     * @param $sort
     * @param $sortDirection
     *
     * @return QueryBuilder
     */
    private function queryLatestMessages(Member $member, $folder, $sort, $sortDirection)
    {
        if ('date' === $sort) {
            $sort = 'created';
        }
        $qb = $this->createQueryBuilder('m');
        switch ($folder) {
            case 'sent':
                $qb
                    ->where('m.sender = :member')
                    ->andWhere('m.request IS NULL')
                    ->andWhere('NOT(m.deleteRequest LIKE :deleted)')
                    ->setParameter('deleted', '%' . DeleteRequestType::SENDER_DELETED . '%')
                    ->andWhere('NOT(m.deleteRequest LIKE :purged)')
                    ->setParameter('purged', '%' . DeleteRequestType::SENDER_PURGED . '%')
                ;
                break;
            default:
                $qb
                    ->where('m.receiver = :member')
                    ->andWhere('NOT(m.deleteRequest LIKE :deleted)')
                    ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
                    ->andWhere('NOT(m.deleteRequest LIKE :purged)')
                    ->setParameter('purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
                ;
        }
        $qb->setParameter('member', $member);
        switch ($folder) {
            case 'inbox':
                $qb
                    ->andWhere('m.folder = :folder')
                    ->setParameter('folder', 'normal')
                    ->andWhere('m.request IS NULL')
                ;
                break;
            case 'spam':
                $qb
                    ->andWhere('m.folder = :folder')
                    ->setParameter('folder', $folder)
                ;
                break;
            default:
                $qb
                    ->andWhere('m.request IS NULL')
                ;
                break;
        }
        $qb->orderBy('m.' . $sort, $sortDirection);

        return $qb;
    }

    /**
     * @param $folder
     * @param $sort
     * @param $sortDirection
     *
     * @return QueryBuilder
     */
    private function queryLatestRequests(Member $member, $folder, $sort, $sortDirection)
    {
        if ('date' === $sort) {
            $sort = 'created';
        }
        $qb = $this->createQueryBuilder('m');
        switch ($folder) {
            case 'sent':
                $qb
                    ->where('NOT(m.deleteRequest LIKE :deleted)')
                    ->setParameter('deleted', '%' . DeleteRequestType::SENDER_DELETED . '%')
                    ->andWhere('NOT(m.deleteRequest LIKE :purged)')
                    ->setParameter('purged', '%' . DeleteRequestType::SENDER_PURGED . '%')
                    ->andWhere('m.sender = :member')
                ;
                break;
            default:
                $qb
                    ->where('NOT(m.deleteRequest LIKE :deleted)')
                    ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
                    ->andWhere('NOT(m.deleteRequest LIKE :purged)')
                    ->setParameter('purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
                    ->andWhere('m.receiver = :member')
                ;
        }
        $qb
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->join('m.request', 'r')
            ->orderBy('m.' . $sort, $sortDirection);

        return $qb;
    }

    /**
     * @param $folder
     * @param $sort
     * @param $sortDirection
     *
     * @return QueryBuilder
     */
    private function queryLatestRequestsAndMessages(Member $member, $folder, $sort, $sortDirection)
    {
        if ('date' === $sort) {
            $sort = 'created';
        }
        switch ($folder) {
            case 'deleted':
                $qb = $this->createQueryBuilder('m');
                $qb
                    ->where(
                        $qb->expr()->andX(
                            $qb->expr()->like('m.deleteRequest', ':receiver_deleted'),
                            $qb->expr()->notLike('m.deleteRequest', ':receiver_purged'),
                            $qb->expr()->eq('m.receiver', ':member')
                        ),
                    )
                    ->orWhere(
                        $qb->expr()->andX(
                            $qb->expr()->like('m.deleteRequest', ':sender_deleted'),
                            $qb->expr()->notLike('m.deleteRequest', ':sender_purged'),
                            $qb->expr()->eq('m.sender', ':member')
                        ),
                    )
                    ->setParameter('receiver_deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
                    ->setParameter('receiver_purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
                    ->setParameter('sender_deleted', '%' . DeleteRequestType::SENDER_DELETED . '%')
                    ->setParameter('sender_purged', '%' . DeleteRequestType::SENDER_PURGED . '%')
                    ->setParameter('member', $member)
                    ->orderBy('m.' . $sort, $sortDirection);
                break;
            case 'inbox':
            default:
                $qb = $this->createQueryBuilder('m')
                    ->where('NOT(m.deleteRequest LIKE :deleted)')
                    ->setParameter(':deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
                    ->andWhere('NOT(m.deleteRequest LIKE :purged)')
                    ->setParameter(':purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
                    ->andWhere('m.folder = :folder')
                    ->setParameter('folder', InFolderType::NORMAL)
                    ->andWhere('m.receiver = :member')
                    ->setParameter('member', $member)
                    ->orderBy('m.' . $sort, $sortDirection);
                break;
        }

        return $qb;
    }

    /**
     * @param int   $page
     * @param int   $items
     * @param mixed $sort
     * @param mixed $sortDirection
     *
     * @return QueryBuilder
     */
    private function queryAllMessagesBetween(Member $loggedInUser, Member $member, $sort, $sortDirection)
    {
        if ('date' === $sort) {
            $sort = 'created';
        }
        $qb = $this->createQueryBuilder('m');
        $qb
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
            ->andWhere(
                $qb
                    ->expr()->orX(
                        $qb->expr()->andX(
                            $qb->expr()->eq('m.sender', ':loggedin'),
                            $qb->expr()->eq('m.receiver', ':member')
                        ),
                        $qb->expr()->andX(
                            $qb->expr()->eq('m.sender', ':member'),
                            $qb->expr()->eq('m.receiver', ':loggedin')
                        )
                    )
            )
            ->setParameter('loggedin', $loggedInUser)
            ->setParameter('member', $member)
            ->orderBy('m.' . $sort, $sortDirection);

        return $qb;
    }
}
