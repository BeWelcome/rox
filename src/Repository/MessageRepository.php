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
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class MessageRepository extends EntityRepository
{
    /**
     * @param Member $member
     *
     * @return int
     */
    public function getUnreadMessagesCount(Member $member)
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
            ->andWhere('m.request IS NULL')
            ->getQuery();

        $unreadCount = 0;
        try {
            $unreadCount = $q->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
        }catch (NoResultException $e) {
        }

        return (int) $unreadCount;
    }

    /**
     * @param Member $member
     *
     * @return int
     */
    public function getUnreadRequestsCount(Member $member)
    {
        $q = $this->createQueryBuilder('m')
            ->join('m.request', 'r')
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
            ->setParameter(':status', 'Sent')
            ->getQuery();

        $unreadCount = 0;
        try {
            $unreadCount = $q->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
        }

        return (int) $unreadCount;
    }

    /**
     * @return int
     */
    public function getReportedMessagesCount()
    {
        $q = $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->where('m.status = :status')
            ->setParameter('status', MessageStatusType::CHECK)
            ->andWhere('m.spaminfo LIKE :spamInfo')
            ->setParameter('spamInfo', SpamInfoType::MEMBER_SAYS_SPAM)
            ->getQuery();

        $result = 0;
        try {
            $result = $q->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
        }

        return $result;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function findReportedMessages($page = 1, $items = 10)
    {
        $queryBuilder = $this->queryReportedMessages();
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return QueryBuilder
     */
    public function queryReportedMessages()
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->where('m.status = :status')
            ->setParameter('status', MessageStatusType::CHECK)
            ->andWhere('m.spaminfo LIKE :spaminfo')
            ->setParameter('spaminfo', SpamInfoType::MEMBER_SAYS_SPAM);

        return $qb;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param Member $member
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
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @param Member $member
     * @param $folder
     * @param $sort
     * @param $sortDirection
     *
     * @return QueryBuilder
     */
    public function queryLatestMessages(Member $member, $folder, $sort, $sortDirection)
    {
        if ('date' === $sort) {
            $sort = 'created';
        }
        $qb = $this->createQueryBuilder('m');
        if ('sent' === $folder) {
            $qb->where('m.sender = :member');
        } else {
            $qb->where('m.receiver = :member');
        }
        $qb
            ->setParameter('member', $member)
            ->andWhere('m.request IS NULL')
            ->andWhere('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%');
        switch ($folder) {
            case 'inbox':
                $qb
                    ->andWhere('m.folder = :folder')
                    ->setParameter('folder', 'normal');
                break;
            case 'spam':
                $qb
                    ->andWhere('m.folder = :folder')
                    ->setParameter('folder', $folder);
                break;
        }
        $qb->orderBy('m.' . $sort, $sortDirection);

        return $qb;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param Member $member
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
            new DoctrineORMAdapter(
                $this->queryLatestRequests($member, $filter, $sort, $sortDirection),
                false
            )
        );
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @param Member $member
     * @param $folder
     * @param $sort
     * @param $sortDirection
     *
     * @return QueryBuilder
     */
    public function queryLatestRequests(Member $member, $folder, $sort, $sortDirection)
    {
        if ('date' === $sort) {
            $sort = 'created';
        }
        $qb = $this->createQueryBuilder('m')
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%');
        if ('sent' === $folder) {
            $qb->andWhere('m.sender = :member');
        } else {
            $qb->andWhere('m.receiver = :member');
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
     * Returns a Pagerfanta object encapsulating the matching paginated activities.
     *
     * @param Member $member
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
            new DoctrineORMAdapter(
                $this->queryLatestRequestsAndMessages($member, $folder, $sort, $sortDirection),
                false
            )
        );
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @param Member $member
     * @param $folder
     * @param $sort
     * @param $sortDirection
     *
     * @return QueryBuilder
     */
    public function queryLatestRequestsAndMessages(Member $member, $folder, $sort, $sortDirection)
    {
        if ('date' === $sort) {
            $sort = 'created';
        }
        switch ($folder) {
            case 'deleted':
                $qb = $this->createQueryBuilder('m')
                    ->where('m.deleteRequest LIKE :deleted')
                    ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
                    ->andWhere('m.receiver = :member')
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
     * @param Member $loggedInUser
     * @param Member $member
     * @param $sort
     * @param $sortDirection
     * @param int $page
     * @param int $items
     *
     * @return Pagerfanta
     */
    public function findAllMessagesBetween(Member $loggedInUser, Member $member, $sort, $sortDirection, $page = 1, $items = 10)
    {
        $paginator = new Pagerfanta(
            new DoctrineORMAdapter(
                $this->queryAllMessagesBetween($loggedInUser, $member, $sort, $sortDirection),
                false
            )
        );
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @param Member $loggedInUser
     * @param Member $member
     * @param int    $page
     * @param int    $items
     * @param mixed  $sort
     * @param mixed  $sortDirection
     *
     * @return QueryBuilder
     */
    public function queryAllMessagesBetween(Member $loggedInUser, Member $member, $sort, $sortDirection)
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

    /**
     * @param Member $member
     *
     * @return Message[]
     */
    public function findAllMessagesWithMember(Member $member)
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
     * @param Member $member
     *
     * @return Collection
     */
    public function getMessagesSentBy(Member $member)
    {
        return $this->createQueryBuilder('m')
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
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
     * @param Member $member
     *
     * @return Collection
     */
    public function getRequestsSentBy(Member $member)
    {
        return $this->createQueryBuilder('m')
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
            ->andWhere('m.sender = :member')
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->join('m.request', 'r')
            ->orderBy('m.created', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
