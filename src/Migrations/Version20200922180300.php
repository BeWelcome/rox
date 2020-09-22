<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * @SuppressWarnings(PHPMD)
 */
final class Version20200922180300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add rememberme_token table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
            CREATE TABLE `rememberme_token` (
                `series`   char(88)     UNIQUE PRIMARY KEY NOT NULL,
                `value`    varchar(88)  NOT NULL,
                `lastUsed` datetime     NOT NULL,
                `class`    varchar(100) NOT NULL,
                `username` varchar(200) NOT NULL
            );
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE `rememberme_token`;
        ');
    }
}
