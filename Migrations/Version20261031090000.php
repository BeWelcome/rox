<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * The counts of the recipients of each mass mail (enqueued, triggered, sent, failed) are kept in
 * broadcast_statistics, so the mass mail pages don't count broadcastmessages. Filled once from broadcastmessages;
 * send:massmail marks a message it couldn't send as Freeze, which counts as failed.
 */
final class Version20261031090000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add broadcast_statistics and fill it from broadcastmessages.';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // CREATE TABLE commits implicitly.
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS broadcast_statistics (
            enqueued INT UNSIGNED DEFAULT 0 NOT NULL,
            triggered INT UNSIGNED DEFAULT 0 NOT NULL,
            sent INT UNSIGNED DEFAULT 0 NOT NULL,
            failed INT UNSIGNED DEFAULT 0 NOT NULL,
            updated DATETIME NOT NULL,
            broadcast_id INT NOT NULL,
            PRIMARY KEY (broadcast_id),
            CONSTRAINT FK_661EAA1C9C7E80E0 FOREIGN KEY (broadcast_id) REFERENCES broadcast (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql("INSERT INTO broadcast_statistics (broadcast_id, enqueued, triggered, sent, failed, updated)
            SELECT
                b.id,
                COUNT(CASE WHEN bm.Status = 'ToApprove' THEN 1 END),
                COUNT(CASE WHEN bm.Status = 'ToSend' THEN 1 END),
                COUNT(CASE WHEN bm.Status = 'Sent' THEN 1 END),
                COUNT(CASE WHEN bm.Status IN ('Failed', 'Freeze') THEN 1 END),
                NOW()
            FROM broadcast b
            LEFT JOIN broadcastmessages bm ON bm.IdBroadcast = b.id
            GROUP BY b.id
            ON DUPLICATE KEY UPDATE
                enqueued = VALUES(enqueued),
                triggered = VALUES(triggered),
                sent = VALUES(sent),
                failed = VALUES(failed),
                updated = VALUES(updated)");
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS broadcast_statistics');
    }
}
