<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Creates the poll tables of App\Entity\Poll, PollChoice, PollVote, PollTranslation and PollChoiceTranslation
 * (        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS poll (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description MEDIUMTEXT NOT NULL, status VARCHAR(10) NOT NULL, choice_type VARCHAR(10) NOT NULL, results_visibility VARCHAR(12) NOT NULL, anonymous TINYINT NOT NULL, allow_comments TINYINT NOT NULL, can_change_vote TINYINT NOT NULL, starts_at DATETIME DEFAULT NULL, ends_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, creator_id INT DEFAULT NULL, INDEX IDX_84BCFA4561220EA6 (creator_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS poll_group (poll_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_B7139043C947C0F (poll_id), INDEX IDX_B713904FE54D947 (group_id), PRIMARY KEY (poll_id, group_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS poll_choice (id INT AUTO_INCREMENT NOT NULL, text LONGTEXT NOT NULL, votes INT NOT NULL, created_at DATETIME NOT NULL, poll_id INT NOT NULL, INDEX IDX_2DAE19C93C947C0F (poll_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS poll_vote (id INT AUTO_INCREMENT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, poll_id INT NOT NULL, member_id INT DEFAULT NULL, UNIQUE INDEX poll_vote_member (poll_id, member_id), INDEX IDX_ED568EBE3C947C0F (poll_id), INDEX IDX_ED568EBE7597D3FE (member_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS poll_vote_choice (poll_vote_id INT NOT NULL, poll_choice_id INT NOT NULL, INDEX IDX_FF2E5FCA8E8957C3 (poll_vote_id), INDEX IDX_FF2E5FCA52514F25 (poll_choice_id), PRIMARY KEY (poll_vote_id, poll_choice_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS poll_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, UNIQUE INDEX lookup_unique_idx (locale, object_id, field), INDEX IDX_860B326D232D562B (object_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS poll_choice_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, UNIQUE INDEX lookup_unique_idx (locale, object_id, field), INDEX IDX_364DB0C8232D562B (object_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addConstraintIfMissing('poll_vote', 'FK_ED568EBE3C947C0F', 'ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('poll_vote', 'FK_ED568EBE7597D3FE', 'ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE7597D3FE FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE SET NULL');
        $this->addConstraintIfMissing('poll_vote_choice', 'FK_FF2E5FCA8E8957C3', 'ALTER TABLE poll_vote_choice ADD CONSTRAINT FK_FF2E5FCA8E8957C3 FOREIGN KEY (poll_vote_id) REFERENCES poll_vote (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('poll_vote_choice', 'FK_FF2E5FCA52514F25', 'ALTER TABLE poll_vote_choice ADD CONSTRAINT FK_FF2E5FCA52514F25 FOREIGN KEY (poll_choice_id) REFERENCES poll_choice (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('poll_choice', 'FK_2DAE19C93C947C0F', 'ALTER TABLE poll_choice ADD CONSTRAINT FK_2DAE19C93C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('poll', 'FK_84BCFA4561220EA6', 'ALTER TABLE poll ADD CONSTRAINT FK_84BCFA4561220EA6 FOREIGN KEY (creator_id) REFERENCES member (id) ON DELETE SET NULL');
        $this->addConstraintIfMissing('poll_group', 'FK_B7139043C947C0F', 'ALTER TABLE poll_group ADD CONSTRAINT FK_B7139043C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('poll_group', 'FK_B713904FE54D947', 'ALTER TABLE poll_group ADD CONSTRAINT FK_B713904FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('poll_choice_translation', 'FK_364DB0C8232D562B', 'ALTER TABLE poll_choice_translation ADD CONSTRAINT FK_364DB0C8232D562B FOREIGN KEY (object_id) REFERENCES poll_choice (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('poll_translation', 'FK_860B326D232D562B', 'ALTER TABLE poll_translation ADD CONSTRAINT FK_860B326D232D562B FOREIGN KEY (object_id) REFERENCES poll (id) ON DELETE CASCADE'); as Doctrine generates it for the entities) and copies the legacy poll data into them: polls,
 * polls_choices, polls_contributions, polls_record_of_choices and polls_list_allowed_groups (still mapped as
 * App\Entity\Polls* and left as they are), plus the poll texts from translations.
 *
 * The ids are kept, so links to /polls/contribute/{id} and the like keep working. Texts go into the
 * translation tables in every language; the columns get the default text (English, or the first language
 * a text was written in). Legacy zero dates become NULL; values become the new names (Project -> draft,
 * Inclusive -> multiple, VisibleAfterVisit -> after_vote, ...).
 *
 * A database created from the mapping (test:database:create) already has the tables and no legacy polls.
 */
final class Version20261026100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create poll, poll_choice, poll_vote and their translation tables and copy the legacy poll data into them';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // CREATE TABLE and ALTER TABLE commit implicitly.
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS poll (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description MEDIUMTEXT NOT NULL, status VARCHAR(10) NOT NULL, choice_type VARCHAR(10) NOT NULL, results_visibility VARCHAR(12) NOT NULL, anonymous TINYINT NOT NULL, allow_comments TINYINT NOT NULL, can_change_vote TINYINT NOT NULL, starts_at DATETIME DEFAULT NULL, ends_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, creator_id INT DEFAULT NULL, INDEX IDX_84BCFA4561220EA6 (creator_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS poll_group (poll_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_B7139043C947C0F (poll_id), INDEX IDX_B713904FE54D947 (group_id), PRIMARY KEY (poll_id, group_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS poll_choice (id INT AUTO_INCREMENT NOT NULL, text LONGTEXT NOT NULL, votes INT NOT NULL, created_at DATETIME NOT NULL, poll_id INT NOT NULL, INDEX IDX_2DAE19C93C947C0F (poll_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS poll_vote (id INT AUTO_INCREMENT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, poll_id INT NOT NULL, member_id INT DEFAULT NULL, UNIQUE INDEX poll_vote_member (poll_id, member_id), INDEX IDX_ED568EBE3C947C0F (poll_id), INDEX IDX_ED568EBE7597D3FE (member_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS poll_vote_choice (poll_vote_id INT NOT NULL, poll_choice_id INT NOT NULL, INDEX IDX_FF2E5FCA8E8957C3 (poll_vote_id), INDEX IDX_FF2E5FCA52514F25 (poll_choice_id), PRIMARY KEY (poll_vote_id, poll_choice_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS poll_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, UNIQUE INDEX lookup_unique_idx (locale, object_id, field), INDEX IDX_860B326D232D562B (object_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS poll_choice_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, UNIQUE INDEX lookup_unique_idx (locale, object_id, field), INDEX IDX_364DB0C8232D562B (object_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addConstraintIfMissing('poll_vote', 'FK_ED568EBE3C947C0F', 'ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('poll_vote', 'FK_ED568EBE7597D3FE', 'ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE7597D3FE FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE SET NULL');
        $this->addConstraintIfMissing('poll_vote_choice', 'FK_FF2E5FCA8E8957C3', 'ALTER TABLE poll_vote_choice ADD CONSTRAINT FK_FF2E5FCA8E8957C3 FOREIGN KEY (poll_vote_id) REFERENCES poll_vote (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('poll_vote_choice', 'FK_FF2E5FCA52514F25', 'ALTER TABLE poll_vote_choice ADD CONSTRAINT FK_FF2E5FCA52514F25 FOREIGN KEY (poll_choice_id) REFERENCES poll_choice (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('poll_choice', 'FK_2DAE19C93C947C0F', 'ALTER TABLE poll_choice ADD CONSTRAINT FK_2DAE19C93C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('poll', 'FK_84BCFA4561220EA6', 'ALTER TABLE poll ADD CONSTRAINT FK_84BCFA4561220EA6 FOREIGN KEY (creator_id) REFERENCES member (id) ON DELETE SET NULL');
        $this->addConstraintIfMissing('poll_group', 'FK_B7139043C947C0F', 'ALTER TABLE poll_group ADD CONSTRAINT FK_B7139043C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('poll_group', 'FK_B713904FE54D947', 'ALTER TABLE poll_group ADD CONSTRAINT FK_B713904FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('poll_choice_translation', 'FK_364DB0C8232D562B', 'ALTER TABLE poll_choice_translation ADD CONSTRAINT FK_364DB0C8232D562B FOREIGN KEY (object_id) REFERENCES poll_choice (id) ON DELETE CASCADE');
        $this->addConstraintIfMissing('poll_translation', 'FK_860B326D232D562B', 'ALTER TABLE poll_translation ADD CONSTRAINT FK_860B326D232D562B FOREIGN KEY (object_id) REFERENCES poll (id) ON DELETE CASCADE');

        if (!$this->hasRows('polls') || $this->hasRows('poll')) {
            return;
        }

        $this->copyPolls();
        $this->copyTexts('polls', 'Title', 'polls.Title', 'poll_translation', 'title');
        $this->copyTexts('polls', 'Description', 'polls.Description', 'poll_translation', 'description');
        $this->fillDefault('poll', 'title', 'poll_translation', 'title');
        $this->fillDefault('poll', 'description', 'poll_translation', 'description');

        $this->connection->executeStatement(
            'INSERT IGNORE INTO poll_group (poll_id, group_id)
             SELECT a.IdPoll, a.IdGroup FROM polls_list_allowed_groups a
             JOIN poll p ON p.id = a.IdPoll
             JOIN `groups` g ON g.id = a.IdGroup'
        );

        $this->connection->executeStatement(
            "INSERT INTO poll_choice (id, poll_id, text, votes, created_at)
             SELECT ch.id, ch.IdPoll, '', ch.Counter, ch.created
             FROM polls_choices ch JOIN poll p ON p.id = ch.IdPoll"
        );
        $this->copyTexts('polls_choices', 'IdChoiceText', 'polls_choices.IdChoiceText', 'poll_choice_translation', 'text');
        $this->fillDefault('poll_choice', 'text', 'poll_choice_translation', 'text');

        $this->connection->executeStatement(
            "INSERT IGNORE INTO poll_vote (id, poll_id, member_id, comment, created_at)
             SELECT c.id, c.IdPoll, m.id, NULLIF(c.comment, ''), c.created
             FROM polls_contributions c
             JOIN poll p ON p.id = c.IdPoll
             LEFT JOIN member m ON m.id = c.IdMember"
        );
        // Who chose what: only recorded for polls that were not anonymous.
        $this->connection->executeStatement(
            'INSERT IGNORE INTO poll_vote_choice (poll_vote_id, poll_choice_id)
             SELECT v.id, ch.id
             FROM polls_record_of_choices r
             JOIN polls_contributions c ON c.IdPoll = r.IdPoll AND c.IdMember = r.IdMember
             JOIN poll_vote v ON v.id = c.id
             JOIN poll_choice ch ON ch.id = r.IdPollChoice AND ch.poll_id = r.IdPoll'
        );
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS poll_vote_choice');
        $this->addSql('DROP TABLE IF EXISTS poll_vote');
        $this->addSql('DROP TABLE IF EXISTS poll_choice_translation');
        $this->addSql('DROP TABLE IF EXISTS poll_choice');
        $this->addSql('DROP TABLE IF EXISTS poll_translation');
        $this->addSql('DROP TABLE IF EXISTS poll_group');
        $this->addSql('DROP TABLE IF EXISTS poll');
    }

    private function copyPolls(): void
    {
        $this->connection->executeStatement(
            "INSERT INTO poll (id, creator_id, title, description, status, choice_type, results_visibility,
                anonymous, allow_comments, can_change_vote, starts_at, ends_at, created_at, updated_at)
             SELECT
                p.id,
                m.id,
                '',
                '',
                CASE p.Status WHEN 'Project' THEN 'draft' WHEN 'Open' THEN 'open' ELSE 'closed' END,
                CASE p.TypeOfChoice WHEN 'Inclusive' THEN 'multiple' WHEN 'Ordered' THEN 'ordered' ELSE 'single' END,
                CASE p.ResultsVisibility WHEN 'Visible' THEN 'visible' WHEN 'Not Visible' THEN 'hidden' ELSE 'after_vote' END,
                p.Anonym = 'Yes',
                p.AllowComment = 'Yes',
                p.CanChangeVote = 'Yes',
                IF(p.Started < '1970-01-02', NULL, p.Started),
                IF(p.Ended < '1970-01-02', NULL, p.Ended),
                p.created,
                p.updated
             FROM polls p
             LEFT JOIN member m ON m.id = p.IdCreator"
        );
    }

    /**
     * One translation per language of the text the IdTrad column of the legacy table points to.
     */
    private function copyTexts(string $legacyTable, string $idTradColumn, string $tableColumn, string $translationTable, string $field): void
    {
        $this->connection->executeStatement(
            "INSERT IGNORE INTO {$translationTable} (locale, field, content, object_id)
             SELECT COALESCE(l.ShortCode, 'en'), ?, t.Sentence, o.id
             FROM {$legacyTable} o
             JOIN translations t ON t.IdTrad = o.{$idTradColumn} AND t.TableColumn = ?
             LEFT JOIN languages l ON l.id = t.IdLanguage
             ORDER BY t.id",
            [$field, $tableColumn]
        );
    }

    /**
     * The column keeps the default text: English, or else the language the text was written in first.
     */
    private function fillDefault(string $table, string $column, string $translationTable, string $field): void
    {
        $this->connection->executeStatement(
            "UPDATE {$table} o SET o.{$column} = COALESCE(
                (SELECT tr.content FROM {$translationTable} tr WHERE tr.object_id = o.id AND tr.field = ? AND tr.locale = 'en'),
                (SELECT tr.content FROM {$translationTable} tr WHERE tr.object_id = o.id AND tr.field = ? ORDER BY tr.id LIMIT 1),
                ''
            )",
            [$field, $field]
        );
    }

    private function hasRows(string $table): bool
    {
        $exists = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table]
        );

        return $exists > 0 && false !== $this->connection->fetchOne("SELECT 1 FROM `{$table}` LIMIT 1");
    }

    private function addConstraintIfMissing(string $table, string $constraint, string $sql): void
    {
        $exists = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.table_constraints WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ?',
            [$table, $constraint]
        );
        if (0 === $exists) {
            $this->connection->executeStatement($sql);
        }
    }
}
