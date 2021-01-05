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
final class Version20200522172912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add forum_trads view';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE
                ALGORITHM = MERGE
                SQL SECURITY DEFINER
            VIEW `forum_trads` AS
                SELECT
                    `translations`.`id` AS `id`,
                    `translations`.`IdLanguage` AS `IdLanguage`,
                    `translations`.`IdOwner` AS `IdOwner`,
                    `translations`.`IdTrad` AS `IdTrad`,
                    `translations`.`IdTranslator` AS `IdTranslator`,
                    `translations`.`updated` AS `updated`,
                    `translations`.`created` AS `created`,
                    `translations`.`Type` AS `Type`,
                    `translations`.`Sentence` AS `Sentence`,
                    `translations`.`IdRecord` AS `IdRecord`,
                    `translations`.`TableColumn` AS `TableColumn`
                FROM
                    `translations`;
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
