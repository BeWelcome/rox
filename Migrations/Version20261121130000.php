<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * A unique index on member.Username. A member is looked up by username on every profile page and for every profile
 * picture, and the table only had the full-text index on it, which doesn't serve an equality: each lookup read all
 * members (0.14 s with 520,000 members, 0.6 s for one picture; a page with twenty pictures took seconds).
 *
 * Unique, as a username must be: the sign-up checks it, the table now holds it. member is develop's table (main's
 * is members). Two members with the same username would stop the migration; they have to be told apart first.
 */
final class Version20261121130000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add a unique index on member.Username.';
    }

    public function up(Schema $schema): void
    {
        // IF NOT EXISTS: the test database's schema comes from the entities, so there the index is there already
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_member_username ON member (Username)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS uniq_member_username ON member');
    }
}
