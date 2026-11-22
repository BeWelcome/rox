<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * develop's queue of the forum's notification mails in a table of its own: `forum_post_notification`
 * (App\Entity\ForumPostNotification). main's `posts_notificationqueue` stays as it is.
 *
 * main's Status is an enum of ToSend, Sent and Failed. develop marks a notification that isn't sent (the receiver
 * isn't active, the mail failed) with 'Freeze', which the enum doesn't know: it was stored as an empty status.
 * In the new table the status is a string.
 *
 * Two kinds of notifications come along, the ids kept: those that still wait to be sent (Status ToSend), and the
 * sent ones that have a Message-ID. The mails of a thread refer to the Message-IDs of the mails sent before, so a
 * member's mail program keeps them in one conversation; without them the next mail about a thread would start a
 * new one. The other sent notifications (main's, and develop's from before it kept the Message-ID) are history and
 * stay. main's table has no foreign keys, so a notification whose receiver or post is gone stays behind.
 *
 * Each step checks first: the test database's schema comes from the entities, so there the new table is there
 * already (and empty).
 */
final class Version20261121110000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create forum_post_notification with the notifications of posts_notificationqueue that wait to be sent or have a Message-ID.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS forum_post_notification (created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, status VARCHAR(16) NOT NULL, type VARCHAR(32) NOT NULL, subscription_id INT NOT NULL, subscription_table VARCHAR(64) NOT NULL, message_id VARCHAR(255) DEFAULT NULL, receiver_id INT NOT NULL, post_id INT NOT NULL, INDEX IDX_CD3F62FACD53EDB6 (receiver_id), INDEX IDX_CD3F62FA4B89032C (post_id), INDEX forum_post_notification_status_idx (status, created), UNIQUE INDEX forum_post_notification_message_id_unique (message_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addForeignKey('forum_post_notification', 'FK_CD3F62FACD53EDB6', 'FOREIGN KEY (receiver_id) REFERENCES member (id) ON DELETE CASCADE');
        $this->addForeignKey('forum_post_notification', 'FK_CD3F62FA4B89032C', 'FOREIGN KEY (post_id) REFERENCES forum_post (id) ON DELETE CASCADE');

        if ($this->tableExists('posts_notificationqueue')) {
            $this->connection->executeStatement(<<<'SQL'
                INSERT IGNORE INTO forum_post_notification (id, created, updated, status, type, subscription_id,
                    subscription_table, message_id, receiver_id, post_id)
                SELECT q.id, q.created, q.updated, q.Status, q.Type, q.IdSubscription, q.TableSubscription, q.MessageId,
                    q.IdMember, q.IdPost
                FROM posts_notificationqueue q
                INNER JOIN member m ON m.id = q.IdMember
                INNER JOIN forum_post p ON p.id = q.IdPost
                WHERE q.Status = 'ToSend' OR (q.Status = 'Sent' AND q.MessageId IS NOT NULL)
                SQL);
        }
    }

    /**
     * Back to main's table. What was written to forum_post_notification since is lost.
     */
    #[Override]
    public function down(Schema $schema): void
    {
        $this->connection->executeStatement('DROP TABLE IF EXISTS forum_post_notification');
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table],
        );
    }

    /**
     * Adds the foreign key unless it exists (the test database's schema comes from the entities).
     */
    private function addForeignKey(string $table, string $name, string $definition): void
    {
        $exists = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.referential_constraints
             WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ?',
            [$table, $name],
        );
        if (!$exists) {
            $this->connection->executeStatement(\sprintf('ALTER TABLE %s ADD CONSTRAINT %s %s', $table, $name, $definition));
        }
    }
}
