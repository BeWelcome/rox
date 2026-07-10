<?php

namespace App\Repository;

use App\Entity\BrowserPushNotification;
use App\Entity\Member;
use DateTimeInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityRepository;
use Throwable;

class BrowserPushNotificationRepository extends EntityRepository
{
    private const int PROCESSING_LEASE_MINUTES = 30;

    /**
     * @return BrowserPushNotification[]
     */
    public function claimScheduledNotifications(int $batchSize): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $connection->beginTransaction();
        try {
            $this->releaseStaleProcessingNotifications();
            $ids = $connection->fetchFirstColumn(
                '
                    SELECT id
                    FROM browser_push_notification
                    WHERE status = ?
                    ORDER BY created ASC, id ASC
                    LIMIT ?
                    FOR UPDATE SKIP LOCKED
                ',
                [BrowserPushNotification::STATUS_SCHEDULED, $batchSize],
                [ParameterType::STRING, ParameterType::INTEGER]
            );

            if ([] !== $ids) {
                $connection->executeStatement(
                    'UPDATE browser_push_notification SET status = ?, updated = CURRENT_TIMESTAMP WHERE id IN (?)',
                    [BrowserPushNotification::STATUS_PROCESSING, array_map('intval', $ids)],
                    [ParameterType::STRING, ArrayParameterType::INTEGER]
                );
            }

            $connection->commit();
        } catch (Throwable $throwable) {
            $connection->rollBack();

            throw $throwable;
        }

        if ([] === $ids) {
            return [];
        }

        return $this->createQueryBuilder('n')
            ->where('n.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('n.created', 'asc')
            ->getQuery()
            ->getResult()
        ;
    }

    public function deleteNotificationsOlderThan(DateTimeInterface $olderThan): int
    {
        try {
            return $this->createQueryBuilder('n')
                ->delete()
                ->where('n.created < :olderThan')
                ->setParameter('olderThan', $olderThan)
                ->getQuery()
                ->execute()
            ;
        } catch (TableNotFoundException) {
            return 0;
        }
    }

    public function releaseStaleProcessingNotifications(): int|string
    {
        return $this->getEntityManager()->getConnection()->executeStatement(
            '
                UPDATE browser_push_notification
                SET status = ?
                WHERE status = ?
                  AND updated < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ? MINUTE)
            ',
            [
                BrowserPushNotification::STATUS_SCHEDULED,
                BrowserPushNotification::STATUS_PROCESSING,
                self::PROCESSING_LEASE_MINUTES,
            ],
            [ParameterType::STRING, ParameterType::STRING, ParameterType::INTEGER]
        );
    }

    /**
     * @param int[] $notificationIds
     */
    public function renewProcessingLease(array $notificationIds): int|string
    {
        if ([] === $notificationIds) {
            return 0;
        }

        return $this->getEntityManager()->getConnection()->executeStatement(
            'UPDATE browser_push_notification SET updated = CURRENT_TIMESTAMP WHERE status = ? AND id IN (?)',
            [BrowserPushNotification::STATUS_PROCESSING, $notificationIds],
            [ParameterType::STRING, ArrayParameterType::INTEGER]
        );
    }

    public function deletePendingNotificationsForMember(Member $member): int
    {
        try {
            return $this->createQueryBuilder('n')
                ->delete()
                ->where('n.receiver = :member')
                ->andWhere('n.status IN (:pendingStatuses)')
                ->setParameter('member', $member)
                ->setParameter('pendingStatuses', [
                    BrowserPushNotification::STATUS_SCHEDULED,
                    BrowserPushNotification::STATUS_PROCESSING,
                    BrowserPushNotification::STATUS_OPEN_ONLY,
                ])
                ->getQuery()
                ->execute()
            ;
        } catch (TableNotFoundException) {
            return 0;
        }
    }

    /**
     * @return BrowserPushNotification[]
     */
    public function findOpenOnlyNotificationsSince(Member $member, int $sinceId, int $limit = 5): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.receiver = :member')
            ->andWhere('n.status = :status')
            ->andWhere('n.id > :sinceId')
            ->setParameter('member', $member)
            ->setParameter('status', BrowserPushNotification::STATUS_OPEN_ONLY)
            ->setParameter('sinceId', $sinceId)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findLatestOpenOnlyNotificationId(Member $member): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COALESCE(MAX(n.id), 0)')
            ->where('n.receiver = :member')
            ->andWhere('n.status = :status')
            ->setParameter('member', $member)
            ->setParameter('status', BrowserPushNotification::STATUS_OPEN_ONLY)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function deleteNotificationsFromSender(string $username): int
    {
        try {
            return $this->createQueryBuilder('n')
                ->delete()
                ->where('n.senderUsername = :username')
                ->setParameter('username', $username)
                ->getQuery()
                ->execute()
            ;
        } catch (TableNotFoundException) {
            return 0;
        }
    }

    public function updateSenderUsername(string $oldUsername, string $newUsername): int|string
    {
        try {
            return $this->getEntityManager()->getConnection()->executeStatement(
                'UPDATE browser_push_notification SET sender_username = ? WHERE sender_username = ?',
                [$newUsername, $oldUsername],
                [ParameterType::STRING, ParameterType::STRING]
            );
        } catch (TableNotFoundException) {
            return 0;
        }
    }
}
