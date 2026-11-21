<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * develop's recipients of the mass mails in a table of their own: `newsletter_recipient`
 * (App\Entity\NewsletterRecipient), filled from main's `broadcastmessages`, which stays as it is.
 *
 * main's Status is an enum (ToApprove, ToSend, Sent, Failed, ToWait, ToResend). develop marked a mail that could not
 * be sent with 'Freeze', which the enum doesn't know: it was stored as an empty status. In the new table the status
 * is a string and a failed mail is 'Failed'; the empty ones come along as that.
 *
 * All recipients come along, the sent ones too: a member isn't enqueued twice for the same mass mail, the unsubscribe
 * links of the mails sent carry the recipient's unsubscribe key, and the member's own data lists the mass mails they
 * got. main's table has no foreign keys: a recipient whose mass mail or member is gone stays behind, and an enqueuer
 * who is gone becomes none.
 *
 * Each step checks first: the test database's schema comes from the entities, so there the new table is there
 * already (and empty).
 */
final class Version20261121120000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create newsletter_recipient from main\'s broadcastmessages; a failed mass mail is Failed, not an empty status.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS newsletter_recipient (status VARCHAR(16) NOT NULL, updated DATETIME NOT NULL, unsubscribe_key VARCHAR(64) DEFAULT NULL, newsletter_id INT NOT NULL, receiver_id INT NOT NULL, enqueued_by_id INT DEFAULT NULL, INDEX IDX_C5BE71C922DB1917 (newsletter_id), INDEX IDX_C5BE71C9CD53EDB6 (receiver_id), INDEX IDX_C5BE71C9E65F9FFB (enqueued_by_id), INDEX newsletter_recipient_status_idx (status, updated), INDEX newsletter_recipient_unsubscribe_key_idx (unsubscribe_key), PRIMARY KEY (newsletter_id, receiver_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addForeignKey('newsletter_recipient', 'FK_C5BE71C922DB1917', 'FOREIGN KEY (newsletter_id) REFERENCES broadcast (id)');
        $this->addForeignKey('newsletter_recipient', 'FK_C5BE71C9CD53EDB6', 'FOREIGN KEY (receiver_id) REFERENCES member (id) ON DELETE CASCADE');
        $this->addForeignKey('newsletter_recipient', 'FK_C5BE71C9E65F9FFB', 'FOREIGN KEY (enqueued_by_id) REFERENCES member (id) ON DELETE SET NULL');

        if ($this->tableExists('broadcastmessages')) {
            $this->connection->executeStatement(<<<'SQL'
                INSERT IGNORE INTO newsletter_recipient (newsletter_id, receiver_id, enqueued_by_id, status, updated,
                    unsubscribe_key)
                SELECT bm.IdBroadcast, bm.IdReceiver, enqueuer.id, IF(bm.Status = '', 'Failed', bm.Status), bm.updated,
                    LEFT(bm.unsubscribe_key, 64)
                FROM broadcastmessages bm
                INNER JOIN broadcast b ON b.id = bm.IdBroadcast
                INNER JOIN member receiver ON receiver.id = bm.IdReceiver
                LEFT JOIN member enqueuer ON enqueuer.id = bm.IdEnqueuer
                SQL);
        }
    }

    /**
     * Back to main's table. What was written to newsletter_recipient since is lost.
     */
    #[Override]
    public function down(Schema $schema): void
    {
        $this->connection->executeStatement('DROP TABLE IF EXISTS newsletter_recipient');
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
