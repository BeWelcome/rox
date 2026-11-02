<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * comment_report: the reports members send about comments on their profile (App\Entity\CommentReport), so that the
 * volunteers read them in the comments tool. Until now a report was only mailed.
 */
final class Version20261102120000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add comment_report.';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // CREATE TABLE commits implicitly.
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE IF NOT EXISTS comment_report (id INT AUTO_INCREMENT NOT NULL, created DATETIME NOT NULL, '
            . 'text LONGTEXT NOT NULL, comment_id INT NOT NULL, reporter_id INT NOT NULL, '
            . 'INDEX IDX_E3C2F96F8697D13 (comment_id), INDEX IDX_E3C2F96E1CFE6F5 (reporter_id), PRIMARY KEY (id), '
            . 'CONSTRAINT FK_E3C2F96F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE, '
            . 'CONSTRAINT FK_E3C2F96E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES member (id)) '
            . 'DEFAULT CHARACTER SET utf8mb4'
        );
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS comment_report');
    }
}
