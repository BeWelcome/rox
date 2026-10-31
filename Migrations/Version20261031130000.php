<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Friendships know who asked (requester_id) and when the other member confirmed (confirmed, null while pending).
 * That replaces left_confirmed/right_confirmed, which couldn't tell who asked.
 *
 * Kept: the friendships confirmed on both sides (main's confirmed relations); the requester of these isn't known,
 * the left member stands in. Dropped: the unconfirmed ones (old relations nobody confirmed and requests that
 * couldn't be confirmed) and the ones with a member that doesn't exist.
 */
final class Version20261031130000 extends AbstractMigration
{
    private const string FOREIGN_KEY = 'FK_55EEAC61ED442CF4';

    #[Override]
    public function getDescription(): string
    {
        return 'friend: add requester_id and confirmed, drop left_confirmed/right_confirmed; keep the confirmed friendships only.';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // ALTER TABLE commits implicitly.
        return false;
    }

    public function up(Schema $schema): void
    {
        // A database built from the entities has the new columns already.
        if (!$this->columnExists('friend', 'left_confirmed')) {
            return;
        }

        $this->connection->executeStatement('ALTER TABLE friend ADD requester_id INT DEFAULT NULL, ADD confirmed DATETIME DEFAULT NULL');

        $this->connection->executeStatement(<<<'SQL'
            DELETE f FROM friend f
            LEFT JOIN member l ON l.id = f.left_id
            LEFT JOIN member r ON r.id = f.right_id
            WHERE l.id IS NULL OR r.id IS NULL OR f.left_confirmed = 0 OR f.right_confirmed = 0
            SQL);
        $this->connection->executeStatement('UPDATE friend SET requester_id = left_id, confirmed = COALESCE(updated, created)');

        $this->connection->executeStatement('ALTER TABLE friend MODIFY requester_id INT NOT NULL, DROP left_confirmed, DROP right_confirmed');
        $this->connection->executeStatement('CREATE INDEX idx_friend_requester ON friend (requester_id)');
        $this->connection->executeStatement(
            'ALTER TABLE friend ADD CONSTRAINT ' . self::FOREIGN_KEY . ' FOREIGN KEY (requester_id) REFERENCES member (id)'
        );
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE friend ADD left_confirmed TINYINT NOT NULL DEFAULT 0, ADD right_confirmed TINYINT NOT NULL DEFAULT 0');
        $this->addSql('UPDATE friend SET left_confirmed = confirmed IS NOT NULL, right_confirmed = confirmed IS NOT NULL');
        $this->addSql('ALTER TABLE friend DROP FOREIGN KEY ' . self::FOREIGN_KEY);
        $this->addSql('DROP INDEX idx_friend_requester ON friend');
        $this->addSql('ALTER TABLE friend DROP requester_id, DROP confirmed');
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );
    }
}
