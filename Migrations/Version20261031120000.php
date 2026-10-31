<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Version20200601010000 copied all forum posts but only the members with the statuses develop keeps, with foreign
 * key checks off: posts of the other members point to members that don't exist. Their author is cleared; the
 * forum shows them as written by a former member. Only develop's forum_post; main's forums_posts stays.
 */
final class Version20261031120000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Clear the author of forum posts whose member does not exist.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE forum_post p LEFT JOIN member m ON m.id = p.IdWriter
            SET p.IdWriter = NULL
            WHERE p.IdWriter IS NOT NULL AND m.id IS NULL
            SQL);
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // the ids of the missing members are gone
    }
}
