<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * develop's reports to the forum moderators in a table of their own: `moderator_report` (App\Entity\ModeratorReport),
 * filled from main's `reports_to_moderators`, which stays as it is, the ids kept.
 *
 * main's table wants a moderator for every report (IdModerator NOT NULL, 0 while nobody answered). develop wrote no
 * moderator for a new report, which the table refused (a member could not report a post), and read the 0 of main's
 * reports as a member, who doesn't exist (the moderators' list of reports failed). Here a report has no moderator
 * until one answers: main's 0, and a moderator who is gone, become NULL.
 *
 * main's table has no foreign keys, so a report whose reporter is gone stays in main's table only. ModeratorComment
 * and Type, which develop neither reads nor fills (the messages are in PostComment), don't come along.
 *
 * Each step checks first: the test database's schema comes from the entities, so there the new table is there
 * already (and empty).
 */
final class Version20261121100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create moderator_report from main\'s reports_to_moderators; a report has no moderator until one answers.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS moderator_report (created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, post_comment MEDIUMTEXT NOT NULL, status VARCHAR(12) NOT NULL, post_id INT NOT NULL, thread_id INT NOT NULL, last_who_spoke VARCHAR(9) NOT NULL, reporter_id INT NOT NULL, moderator_id INT DEFAULT NULL, INDEX IDX_27FD8F1AE1CFE6F5 (reporter_id), INDEX IDX_27FD8F1AD0AFA354 (moderator_id), INDEX moderator_report_post_idx (post_id, reporter_id), INDEX moderator_report_thread_idx (thread_id), INDEX moderator_report_status_idx (status), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addForeignKey('moderator_report', 'FK_27FD8F1AE1CFE6F5', 'FOREIGN KEY (reporter_id) REFERENCES member (id)');
        $this->addForeignKey('moderator_report', 'FK_27FD8F1AD0AFA354', 'FOREIGN KEY (moderator_id) REFERENCES member (id)');

        if ($this->tableExists('reports_to_moderators')) {
            // main's created is 0000-00-00 for some reports: the last change then
            $this->connection->executeStatement(<<<'SQL'
                INSERT IGNORE INTO moderator_report (id, created, updated, post_comment, status, post_id, thread_id,
                    last_who_spoke, reporter_id, moderator_id)
                SELECT r.id, IF(r.created > '1000-01-01', r.created, r.updated), r.updated, r.PostComment, r.Status,
                    r.IdPost, r.IdThread, r.LastWhoSpoke, r.IdReporter, moderator.id
                FROM reports_to_moderators r
                INNER JOIN member reporter ON reporter.id = r.IdReporter
                LEFT JOIN member moderator ON moderator.id = r.IdModerator
                SQL);
        }
    }

    /**
     * Back to main's table. What was written to moderator_report since is lost.
     */
    #[Override]
    public function down(Schema $schema): void
    {
        $this->connection->executeStatement('DROP TABLE IF EXISTS moderator_report');
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
