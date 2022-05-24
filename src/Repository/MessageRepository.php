<?php

namespace App\Repository;

use App\Doctrine\DeleteRequestType;
use App\Doctrine\InFolderType;
use App\Doctrine\MessageResultSetMapping;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\Message;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class MessageRepository extends EntityRepository
{
    public function getUnreadConversationsCount(Member $member): int
    {
        $sql = $this->getSql(true);

        $sql = str_replace('SELECT * FROM (', 'SELECT count(*) AS count FROM (', $sql);

        $result = $this->getEntityManager()->getConnection()->executeQuery($sql, [
            ':memberId' => $member->getId(),
        ]);

        $unread = $result->fetchOne();

        return (int) $unread;
    }

    public function getConversations(Member $member, bool $unreadOnly, int $limit = 5)
    {
        $sql = $this->getSql($unreadOnly);

        $sql .= ' ORDER BY `m`.`created` DESC LIMIT ' . $limit . ' OFFSET 0';

        $query = $this->getEntityManager()->createNativeQuery($sql, new MessageResultSetMapping())
            ->setParameter(':memberId', $member->getId())
        ;

        return $query->getResult();
    }

    public function getReportedMessagesCount(): int
    {
        $q = $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->where('m.status = :status')
            ->setParameter('status', MessageStatusType::CHECK)
            ->andWhere('m.spamInfo LIKE :spamInfo')
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
     * Returns a Pagerfanta object encapsulating the matching paginated processed reported messages.
     *
     * @param mixed $page
     * @param mixed $items
     */
    public function findProcessedReportedMessages($page = 1, $items = 10): Pagerfanta
    {
        $queryBuilder = $this->queryProcessedReportedMessages();
        $adapter = new QueryAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
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

    public function findAllMessagesBetween(
        Member $loggedInUser,
        Member $member,
        string $sort,
        string $sortDirection,
        int $page = 1,
        int $items = 10
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
            ->setMaxResults(500)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Message[]
     */
    public function getMessagesSentBy(Member $member): array
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
     * @return Message[]
     */
    public function getMessagesReceivedBy(Member $member): array
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
     * @return Message[]
     */
    public function getRequestsSentBy(Member $member): array
    {
        $qb = $this->createQueryBuilder('m');

        return $qb
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::SENDER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::SENDER_PURGED . '%')
            ->andWhere('m.sender = :member')
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->join('m.request', 'r', Join::WITH, $qb->expr()->isNull('r.inviteForLeg'))
            ->orderBy('m.created', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Message[]
     */
    public function getRequestsReceivedBy(Member $member): array
    {
        $qb = $this->createQueryBuilder('m');

        return $qb
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
            ->andWhere('m.receiver = :member')
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->join('m.request', 'r', Join::WITH, $qb->expr()->isNull('r.inviteForLeg'))
            ->orderBy('m.created', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Message[]
     */
    public function getInvitationsSentBy(Member $member): array
    {
        $qb = $this->createQueryBuilder('m');

        return $qb
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::SENDER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::SENDER_PURGED . '%')
            ->andWhere('m.sender = :member')
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->join('m.request', 'r', Join::WITH, $qb->expr()->isNotNull('r.inviteForLeg'))
            ->orderBy('m.created', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Message[]
     */
    public function getInvitationsReceivedBy(Member $member): array
    {
        $qb = $this->createQueryBuilder('m');

        return $qb
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
            ->andWhere('m.receiver = :member')
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->join('m.request', 'r', Join::WITH, $qb->expr()->isNotNull('r.inviteForLeg'))
            ->orderBy('m.created', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function queryReportedMessages(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->where('m.status = :status')
            ->setParameter('status', MessageStatusType::CHECK)
            ->andWhere('m.spamInfo LIKE :spamInfo')
            ->setParameter('spamInfo', '%' . SpamInfoType::MEMBER_SAYS_SPAM . '%')
        ;

        return $qb;
    }

    private function queryProcessedReportedMessages(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->where('m.status = :status')
            ->setParameter('status', MessageStatusType::CHECKED)
            ->andWhere('m.spamInfo LIKE :spamInfo')
            ->setParameter('spamInfo', '%' . SpamInfoType::CHECKER_SAYS_SPAM . '%')
            ->orderBy('m.created', 'DESC')
        ;

        return $qb;
    }

    private function queryAllMessagesBetween(
        Member $loggedInUser,
        Member $member,
        string $sort,
        string $sortDirection
    ): QueryBuilder {
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

    private function getSql(bool $unreadOnly): string
    {
        if ($unreadOnly) {
            $unreadCondition = '(m.IdReceiver = :memberId) '
                . 'AND (`m`.WhenFirstRead IS NULL OR `m`.WhenFirstRead = \'0000-00-00 00:00:00\')';
        } else {
            $unreadCondition = '(m.IdReceiver = :memberId)';
        }

        $sql = '
            SELECT * FROM (
                    SELECT *
                    FROM `messages` m
                    WHERE ' . $unreadCondition . '
                    AND `m`.`id` IN (
                        SELECT max(`m`.`id`)
                        FROM `messages` m
                        WHERE m.IdReceiver = :memberId AND (
                            m.DeleteRequest NOT LIKE \'%' . DeleteRequestType::RECEIVER_DELETED . '%\'
                            AND m.DeleteRequest NOT LIKE \'%' . DeleteRequestType::RECEIVER_PURGED . '%\'
                        )
                    GROUP BY `m`.`subject_id`
                    )
                UNION
                    SELECT *
                    FROM `messages` m
                    WHERE ' . $unreadCondition . '
                    AND `m`.`subject_id` IS NULL
                    AND (
                            m.DeleteRequest NOT LIKE \'%' . DeleteRequestType::RECEIVER_DELETED . '%\'
                            AND m.DeleteRequest NOT LIKE \'%' . DeleteRequestType::RECEIVER_PURGED . '%\'
                        )
             ) m
         ';

        return $sql;
    }
}
