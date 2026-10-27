<?php

namespace App\Repository;

use App\Doctrine\DeleteRequestType;
use App\Doctrine\InFolderType;
use App\Doctrine\MemberStatusType;
use App\Doctrine\MessageResultSetMapping;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\Message;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class MessageRepository extends EntityRepository
{
    /**
     * Banned or rejected members updated during the last 90 days and the number of messages they sent, last
     * updated first (volunteer tool "top spammer").
     *
     * @return list<array{MessagesSent: int, Username: string, Status: string, Updated: ?DateTimeInterface}>
     */
    public function findTopSpammers(int $limit = 100): array
    {
        return $this->createQueryBuilder('msg')
            ->select('COUNT(msg.id) AS MessagesSent, s.username AS Username, s.status AS Status, s.updated AS Updated')
            ->innerJoin('msg.sender', 's')
            ->where('s.status IN (:spammers)')
            ->andWhere('s.updated > :since')
            ->setParameter('spammers', [MemberStatusType::BANNED, MemberStatusType::REJECTED])
            ->setParameter('since', new DateTimeImmutable('-91 days'))
            ->groupBy('s.id')
            ->orderBy('s.updated', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Messages of banned or rejected members to members who then left (volunteer tool "damage done").
     *
     * @return list<array{Receiver: string, ReceiverStatus: string, LastUpdated: ?DateTimeInterface, Sender: string, SenderStatus: string}>
     */
    public function findDamageDone(int $limit = 40): array
    {
        return $this->createQueryBuilder('msg')
            ->select('r.username AS Receiver, r.status AS ReceiverStatus, r.updated AS LastUpdated')
            ->addSelect('s.username AS Sender, s.status AS SenderStatus')
            ->innerJoin('msg.sender', 's')
            ->innerJoin('msg.receiver', 'r')
            ->where('s.status IN (:spammers)')
            ->andWhere('r.status IN (:left)')
            ->setParameter('spammers', [MemberStatusType::BANNED, MemberStatusType::REJECTED])
            ->setParameter('left', [MemberStatusType::TAKEN_OUT, MemberStatusType::ASKED_TO_LEAVE])
            ->orderBy('s.updated', 'DESC')
            ->addOrderBy('s.username')
            ->addOrderBy('r.updated', 'DESC')
            ->addOrderBy('r.id')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Members who sent more than $moreThan messages (or only hosting requests and replies to them) during the last
     * $days days, with the place and country code of their active address (volunteer tools "messages sent" and
     * "requests sent").
     *
     * @return list<array{Username: string, City: ?string, Country: ?string, Count: int}>
     */
    public function findFrequentSenders(int $days, bool $requestsOnly, int $moreThan = 9): array
    {
        $qb = $this->createQueryBuilder('msg')
            ->select('s.username AS Username, l.name AS City, l.countryId AS Country, COUNT(DISTINCT msg.id) AS Count')
            ->innerJoin('msg.sender', 's')
            ->leftJoin('s.addresses', 'a', Join::WITH, 'a.active = true')
            ->leftJoin('a.location', 'l')
            ->where('msg.created > :since')
            ->setParameter('since', new DateTimeImmutable('-' . $days . ' days'))
            ->groupBy('s.id')
            ->addGroupBy('l.geonameId')
            ->having('COUNT(DISTINCT msg.id) > :moreThan')
            ->setParameter('moreThan', $moreThan)
            ->orderBy('Count', 'DESC');
        if ($requestsOnly) {
            $qb->andWhere('msg.request IS NOT NULL');
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function getUnreadConversationsCount(Member $member): int
    {
        $sql = $this->getSql(true);

        $sql = str_replace('SELECT * FROM (', 'SELECT count(*) AS count FROM (', $sql);

        $result = $this->getEntityManager()->getConnection()->executeQuery($sql, [
            'memberId' => $member->getId(),
        ]);

        $unread = $result->fetchOne();

        return (int) $unread;
    }

    public function getMessagesBetweenCount(Member $loggedInMember, Member $member): int
    {
        /** @var Connection $connection */
        $connection = $this->getEntityManager()->getConnection();
        $count = $connection->fetchOne(
            'SELECT count(*) AS cnt FROM (SELECT `m`.*
            FROM `messages` m
            WHERE
				NOT m.subject_id IS NULL
				AND m.id IN (
					SELECT max(m.id)
					FROM messages m
                    WHERE
						((m.IdReceiver = :loggedIn AND m.IdSender = :member) OR
						(m.IdReceiver = :member AND m.IdSender = :loggedIn))
					GROUP BY m.subject_id
				)
			UNION
            SELECT `m`.*
            FROM `messages` m
            WHERE
				m.subject_id is null
				AND	((m.IdReceiver = :loggedIn AND m.IdSender = :member) OR
						(m.IdReceiver = :member AND m.IdSender = :loggedIn))) AS a',
            [
                'loggedIn' => $loggedInMember->getId(),
                'member' => $member->getId(),
            ]
        );

        return $count;
    }

    public function getConversations(Member $member, bool $unreadOnly, int $limit = 5)
    {
        $sql = $this->getSql($unreadOnly);

        $sql .= ' ORDER BY `m`.`created` DESC LIMIT ' . $limit . ' OFFSET 0';

        $query = $this->getEntityManager()->createNativeQuery($sql, new MessageResultSetMapping())
            ->setParameter('memberId', $member->getId())
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

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated processed reported messages.
     */
    public function findBlockWordsMessages($page = 1, $items = 10): Pagerfanta
    {
        $queryBuilder = $this->queryBlockWordsMessages();
        $adapter = new QueryAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * Returns a Pagerfanta object encapsulating the matching paginated processed reported messages.
     */
    public function findProcessedBlockWordsMessages($page = 1, $items = 10): Pagerfanta
    {
        $queryBuilder = $this->queryProcessedBlockWordsMessages();
        $adapter = new QueryAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage($items);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    public function findAllMessagesBetween(
        Member $loggedInUser,
        Member $member,
        string $sort,
        string $sortDirection,
        int $page = 1,
        int $items = 10,
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
            ->join('m.request', 'r')
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::SENDER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::SENDER_PURGED . '%')
            ->andWhere('m.sender = :member')
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->andWhere($qb->expr()->isNull('r.inviteForLeg'))
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
            ->join('m.request', 'r')
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
            ->andWhere('m.receiver = :member')
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->andWhere($qb->expr()->isNull('r.inviteForLeg'))
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
            ->join('m.request', 'r')
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::SENDER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::SENDER_PURGED . '%')
            ->andWhere('m.sender = :member')
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->andWhere($qb->expr()->isNotNull('r.inviteForLeg'))
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
            ->join('m.request', 'r')
            ->where('NOT(m.deleteRequest LIKE :deleted)')
            ->setParameter('deleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
            ->andWhere('NOT(m.deleteRequest LIKE :purged)')
            ->setParameter('purged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
            ->andWhere('m.receiver = :member')
            ->setParameter('member', $member)
            ->andWhere('m.folder = :folder')
            ->setParameter('folder', InFolderType::NORMAL)
            ->andWhere($qb->expr()->isNotNull('r.inviteForLeg'))
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
            ->orderBy('m.created', 'DESC')
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

    private function queryBlockWordsMessages(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->join(Member::class, 'mb', Join::WITH, 'm.sender = mb.id')
            ->where('m.status = :status')
            ->setParameter('status', MessageStatusType::CHECK)
            ->andWhere('m.spamInfo LIKE :spamInfo')
            ->setParameter('spamInfo', '%' . SpamInfoType::SPAM_BLOCKED_WORD . '%')
            ->andWhere($qb->expr()->eq('mb.status', ':active'))
            ->setParameter('active', MemberStatusType::ACTIVE)
            ->orderBy('m.created', 'DESC')
        ;

        return $qb;
    }

    private function queryProcessedBlockWordsMessages(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->join(Member::class, 'mb', Join::WITH, 'm.sender = mb.id')
            ->where($qb->expr()->notLike('m.spamInfo', ':blockedWord'))
            ->andWhere($qb->expr()->notLike('m.spamInfo', ':checkerSaysSpam'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('m.status', ':status'),
                    $qb->expr()->eq('mb.status', ':banned'),
                )
            )
            ->setParameter('blockedWord', '%' . SpamInfoType::SPAM_BLOCKED_WORD . '%')
            ->setParameter('checkerSaysSpam', '%' . SpamInfoType::CHECKER_SAYS_SPAM . '%')
            ->setParameter('status', MessageStatusType::CHECKED)
            ->setParameter('banned', MemberStatusType::BANNED)
            ->orderBy('m.created', 'DESC')
        ;

        return $qb;
    }

    private function queryAllMessagesBetween(
        Member $loggedInUser,
        Member $member,
        string $sort,
        string $sortDirection,
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
                        WHERE m.IdReceiver = :memberId AND m.InFolder = \'Normal\' AND (
                            m.DeleteRequest NOT LIKE \'%' . DeleteRequestType::RECEIVER_DELETED . '%\'
                            AND m.DeleteRequest NOT LIKE \'%' . DeleteRequestType::RECEIVER_PURGED . '%\'
                        )
                    GROUP BY `m`.`subject_id`
                    )
                UNION
                    SELECT *
                    FROM `messages` m
                    WHERE ' . $unreadCondition . ' AND m.InFolder = \'Normal\'
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
