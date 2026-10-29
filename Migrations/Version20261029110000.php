<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * develop's `message` (App\Entity\Message), filled from main's `messages` (App\Entity\Messages), ids kept:
 * - snake_case column names;
 * - created is the time the message was sent (DateSent, created where DateSent is empty);
 * - MessageType, updated and the columns only main has (IdMessageFromLocalVol, IdentityInformation,
 *   SendConfirmation, IdChecker, IdTriggerer, JoinMemberPict) are not copied;
 * - messages of members that don't exist (any more) are not copied.
 *
 * `messages` stays: main reads and writes it.
 */
final class Version20261029110000 extends AbstractMigration
{
    private const int BATCH_SIZE = 200000;

    private const array FOREIGN_KEYS = [
        'FK_B6BD307F727ACA70' => 'FOREIGN KEY (parent_id) REFERENCES message (id) ON DELETE SET NULL',
        'FK_B6BD307F7DB3B714' => 'FOREIGN KEY (initiator_id) REFERENCES member (id)',
        'FK_B6BD307FCD53EDB6' => 'FOREIGN KEY (receiver_id) REFERENCES member (id)',
        'FK_B6BD307FF624B39D' => 'FOREIGN KEY (sender_id) REFERENCES member (id)',
        'FK_B6BD307F23EDC87' => 'FOREIGN KEY (subject_id) REFERENCES subject (id)',
        'FK_B6BD307F427EB8A5' => 'FOREIGN KEY (request_id) REFERENCES request (id)',
    ];

    #[Override]
    public function getDescription(): string
    {
        return 'Create message and copy main\'s messages into it (snake_case columns, without the unused ones).';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // CREATE TABLE commits implicitly, and the copy runs in batches.
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS message (created DATETIME NOT NULL, delete_request VARCHAR(57) DEFAULT NULL, spam_info VARCHAR(71) NOT NULL, status VARCHAR(7) NOT NULL, message LONGTEXT NOT NULL, folder VARCHAR(8) NOT NULL, first_read DATETIME DEFAULT NULL, checker_comment LONGTEXT DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, initiator_id INT NOT NULL, receiver_id INT NOT NULL, sender_id INT NOT NULL, subject_id INT DEFAULT NULL, request_id INT DEFAULT NULL, INDEX message_spam_info (spam_info), INDEX message_status (status), INDEX message_delete_request (delete_request), INDEX message_first_read (first_read), INDEX message_created (created), INDEX IDX_B6BD307F727ACA70 (parent_id), INDEX IDX_B6BD307F7DB3B714 (initiator_id), INDEX IDX_B6BD307FCD53EDB6 (receiver_id), INDEX IDX_B6BD307FF624B39D (sender_id), INDEX IDX_B6BD307F23EDC87 (subject_id), INDEX IDX_B6BD307F427EB8A5 (request_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        foreach (self::FOREIGN_KEYS as $name => $definition) {
            if (!$this->foreignKeyExists('message', $name)) {
                $this->connection->executeStatement(\sprintf('ALTER TABLE message ADD CONSTRAINT %s %s', $name, $definition));
            }
        }

        if ($this->tableExists('messages') && !$this->connection->fetchOne('SELECT 1 FROM message LIMIT 1')) {
            $this->copyMessages();
        }

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS message');
    }

    private function copyMessages(): void
    {
        $maxId = (int) $this->connection->fetchOne('SELECT MAX(id) FROM messages');
        for ($from = 0; $from <= $maxId; $from += self::BATCH_SIZE) {
            // Zero dates (from main's old rows) are compared with a real date rather than with '0000-00-00', which
            // strict SQL modes refuse.
            $this->connection->executeStatement(<<<'SQL'
                INSERT IGNORE INTO message (
                    id, created, delete_request, spam_info, status, message, folder, first_read, checker_comment,
                    parent_id, initiator_id, receiver_id, sender_id, subject_id, request_id
                )
                SELECT
                    m.id,
                    IF(m.DateSent > '1970-01-02', m.DateSent, m.created),
                    NULLIF(m.DeleteRequest, ''),
                    m.SpamInfo,
                    m.Status,
                    m.Message,
                    m.InFolder,
                    IF(m.WhenFirstRead > '1970-01-02', m.WhenFirstRead, NULL),
                    NULLIF(m.CheckerComment, ''),
                    NULLIF(m.IdParent, 0),
                    IF(i.id IS NULL, m.IdSender, m.initiator_id),
                    m.IdReceiver,
                    m.IdSender,
                    m.subject_id,
                    m.request_id
                FROM messages m
                JOIN member s ON s.id = m.IdSender
                JOIN member r ON r.id = m.IdReceiver
                LEFT JOIN member i ON i.id = m.initiator_id
                WHERE m.id > ? AND m.id <= ?
                SQL, [$from, $from + self::BATCH_SIZE]);
        }

        // Links to rows that weren't copied or don't exist.
        $this->connection->executeStatement(<<<'SQL'
            UPDATE message m LEFT JOIN message p ON p.id = m.parent_id
            SET m.parent_id = NULL
            WHERE m.parent_id IS NOT NULL AND p.id IS NULL
            SQL);
        $this->connection->executeStatement(<<<'SQL'
            UPDATE message m LEFT JOIN subject s ON s.id = m.subject_id
            SET m.subject_id = NULL
            WHERE m.subject_id IS NOT NULL AND s.id IS NULL
            SQL);
        $this->connection->executeStatement(<<<'SQL'
            UPDATE message m LEFT JOIN request r ON r.id = m.request_id
            SET m.request_id = NULL
            WHERE m.request_id IS NOT NULL AND r.id IS NULL
            SQL);
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table],
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
