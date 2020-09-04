<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @SuppressWarnings(PHPMD)
 */
final class Version20200919061813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add failure_login_attempt table for anyx/login-gate-bundle, and refresh_token for API';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS failure_login_attempt (id INT AUTO_INCREMENT NOT NULL, ip VARCHAR(45) NOT NULL, username VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX ip (ip), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_520_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS refresh_token (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id INT NOT NULL, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, INDEX IDX_C74F2195A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_520_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F2195A76ED395 FOREIGN KEY IF NOT EXISTS (user_id) REFERENCES members (id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE refresh_token');
        $this->addSql('DROP TABLE failure_login_attempt');
    }
}
