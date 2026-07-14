<?php

namespace App\Repository;

use App\Doctrine\NotificationStatusType;
use App\Entity\ForumThread;
use App\Entity\Member;
use DateTime;
use Doctrine\ORM\EntityRepository;

/**
 * Class PostNotificationRepository.
 */
class PostNotificationRepository extends EntityRepository
{
    /**
     * @return int|mixed|string
     */
    public function getScheduledNotifications(int $batchSize)
    {
        $date = new DateTime();
        $date->modify('-5 minutes');

        return $this->createQueryBuilder('n')
            ->where('n.status = :toSend')
            ->setParameter('toSend', NotificationStatusType::SCHEDULED)
            ->andWhere('n.created < :date')
            ->setParameter('date', $date)
            ->orderBy('n.created', 'asc')
            ->addOrderBy('n.id', 'asc')
            ->setMaxResults($batchSize)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<string>
     */
    public function getSentMessageIds(Member $receiver, ForumThread $thread): array
    {
        return $this->createQueryBuilder('n')
            ->select('n.messageId')
            ->innerJoin('n.post', 'p')
            ->where('n.receiver = :receiver')
            ->setParameter('receiver', $receiver)
            ->andWhere('p.thread = :thread')
            ->setParameter('thread', $thread)
            ->andWhere('n.status = :sent')
            ->setParameter('sent', NotificationStatusType::SENT)
            ->andWhere('n.messageId IS NOT NULL')
            ->orderBy('n.created', 'asc')
            ->addOrderBy('n.id', 'asc')
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }
}
