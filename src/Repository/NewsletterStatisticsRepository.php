<?php

namespace App\Repository;

use App\Entity\Newsletter;
use App\Entity\NewsletterStatistics;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NewsletterStatistics>
 */
class NewsletterStatisticsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsletterStatistics::class);
    }

    /**
     * Changes the counts of a mass mail by the given numbers, in one statement (no count goes below 0); the row is
     * created if it doesn't exist yet.
     */
    public function add(Newsletter|int $newsletter, int $enqueued = 0, int $triggered = 0, int $sent = 0, int $failed = 0): void
    {
        if (0 === $enqueued && 0 === $triggered && 0 === $sent && 0 === $failed) {
            return;
        }

        $this->getEntityManager()->getConnection()->executeStatement(
            'INSERT INTO broadcast_statistics (broadcast_id, enqueued, triggered, sent, failed, updated)
            VALUES (:id, GREATEST(0, :enqueued), GREATEST(0, :triggered), GREATEST(0, :sent), GREATEST(0, :failed), NOW())
            ON DUPLICATE KEY UPDATE
                enqueued = GREATEST(0, CAST(enqueued AS SIGNED) + :enqueued),
                triggered = GREATEST(0, CAST(triggered AS SIGNED) + :triggered),
                sent = GREATEST(0, CAST(sent AS SIGNED) + :sent),
                failed = GREATEST(0, CAST(failed AS SIGNED) + :failed),
                updated = NOW()',
            [
                'id' => $newsletter instanceof Newsletter ? $newsletter->getId() : $newsletter,
                'enqueued' => $enqueued,
                'triggered' => $triggered,
                'sent' => $sent,
                'failed' => $failed,
            ],
        );
    }
}
