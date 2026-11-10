<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * develop's `log` (App\Entity\Log), filled from main's `logs`, ids kept:
 * - snake_case column names: member_id, type, message;
 * - indexes for the logs page: newest first, by type and by member;
 * - members missing from `member` (main writes 0 for entries without a member) are cleared.
 *
 * The rows are copied in batches of ids, and a run that stopped goes on after the last copied id. The indexes are
 * added after the copy, which is faster on a large table.
 *
 * `logs` stays: main reads and writes it.
 */
final class Version20261110100000 extends AbstractMigration
{
    private const int BATCH = 100000;

    private const array INDEXES = [
        'log_created' => '(created)',
        'log_type_created' => '(type, created)',
        'log_member_created' => '(member_id, created)',
        'IDX_8F3F68C57597D3FE' => '(member_id)',
    ];

    private const array FOREIGN_KEYS = [
        'FK_8F3F68C57597D3FE' => 'FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE SET NULL',
    ];

    #[Override]
    public function getDescription(): string
    {
        return 'Create log and copy main\'s logs into it (snake_case columns, indexes for the logs page).';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // CREATE TABLE commits implicitly, and the copy is done in batches.
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS log (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(64) NOT NULL, message MEDIUMTEXT NOT NULL, created DATETIME NOT NULL, member_id INT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        if ($this->tableExists('logs')) {
            $this->copyLogs();
        }

        foreach (self::INDEXES as $name => $columns) {
            if (!$this->indexExists('log', $name)) {
                $this->connection->executeStatement(\sprintf('ALTER TABLE log ADD INDEX %s %s', $name, $columns));
            }
        }
        foreach (self::FOREIGN_KEYS as $name => $definition) {
            if (!$this->foreignKeyExists('log', $name)) {
                $this->connection->executeStatement(\sprintf('ALTER TABLE log ADD CONSTRAINT %s %s', $name, $definition));
            }
        }
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS log');
    }

    private function copyLogs(): void
    {
        $last = (int) $this->connection->fetchOne('SELECT MAX(id) FROM logs');
        $from = (int) $this->connection->fetchOne('SELECT COALESCE(MAX(id), 0) FROM log') + 1;

        // Zero dates (from main's old rows) are compared with a real date rather than with '0000-00-00', which
        // strict SQL modes refuse. The column names are main's; MariaDB doesn't tell IdMember from idMember (a
        // database built from develop's entities).
        for (; $from <= $last; $from += self::BATCH) {
            $this->connection->executeStatement(
                <<<'SQL'
                    INSERT IGNORE INTO log (id, member_id, type, message, created)
                    SELECT l.id, m.id, LEFT(l.Type, 64), l.Str, IF(l.created > '1970-01-02', l.created, '1970-01-02')
                    FROM logs l
                    LEFT JOIN member m ON m.id = l.IdMember
                    WHERE l.id BETWEEN ? AND ?
                    SQL,
                [$from, $from + self::BATCH - 1],
            );
        }
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table],
        );
    }

    private function indexExists(string $table, string $name): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $name],
        );
    }

    private function foreignKeyExists(string $table, string $name): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.referential_constraints
             WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ?',
            [$table, $name],
        );
    }
}
