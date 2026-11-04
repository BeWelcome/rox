<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Community news comments get replies (parent_id), a removed state that keeps a comment's place for its replies
 * (deleted), and reports from members for the checkers (community_news_comment_report). Main's code doesn't know
 * the new columns and keeps writing comments as before.
 *
 * Every step skips what exists already: the test database is created from the entities before the migrations run.
 */
final class Version20261104100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add community_news_comment.parent_id and .deleted, and community_news_comment_report.';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // ALTER and CREATE TABLE commit implicitly.
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE community_news_comment ADD COLUMN IF NOT EXISTS deleted DATETIME DEFAULT NULL, '
            . 'ADD COLUMN IF NOT EXISTS parent_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_9C81F615727ACA70 ON community_news_comment (parent_id)');
        $this->addSql('ALTER TABLE community_news_comment ADD CONSTRAINT FK_9C81F615727ACA70 FOREIGN KEY IF NOT EXISTS '
            . '(parent_id) REFERENCES community_news_comment (id) ON DELETE CASCADE');

        $this->addSql(
            'CREATE TABLE IF NOT EXISTS community_news_comment_report (id INT AUTO_INCREMENT NOT NULL, '
            . 'created DATETIME NOT NULL, reason VARCHAR(20) NOT NULL, note LONGTEXT DEFAULT NULL, '
            . 'comment_id INT NOT NULL, reporter_id INT NOT NULL, '
            . 'UNIQUE INDEX UNIQ_CNCR_COMMENT_REPORTER (comment_id, reporter_id), '
            . 'INDEX IDX_B0211E54F8697D13 (comment_id), INDEX IDX_B0211E54E1CFE6F5 (reporter_id), PRIMARY KEY (id), '
            . 'CONSTRAINT FK_B0211E54F8697D13 FOREIGN KEY (comment_id) REFERENCES community_news_comment (id) ON DELETE CASCADE, '
            . 'CONSTRAINT FK_B0211E54E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES member (id)) '
            . 'DEFAULT CHARACTER SET utf8mb4'
        );
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS community_news_comment_report');
        $this->addSql('ALTER TABLE community_news_comment DROP FOREIGN KEY IF EXISTS FK_9C81F615727ACA70');
        $this->addSql('ALTER TABLE community_news_comment DROP INDEX IF EXISTS IDX_9C81F615727ACA70, '
            . 'DROP COLUMN IF EXISTS parent_id, DROP COLUMN IF EXISTS deleted');
    }
}
