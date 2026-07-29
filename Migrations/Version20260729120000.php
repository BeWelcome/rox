<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260729120000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create rememberme_token table used by the Doctrine remember-me token provider';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS rememberme_token (
                series VARCHAR(88) NOT NULL,
                value VARCHAR(88) NOT NULL,
                lastUsed DATETIME NOT NULL,
                class VARCHAR(100) DEFAULT \'\' NOT NULL,
                username VARCHAR(200) NOT NULL,
                PRIMARY KEY(series)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS rememberme_token');
    }
}
