<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Members choose which of their groups their profile shows (group_membership.shown_on_profile; all of them so far).
 */
final class Version20261030100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add group_membership.shown_on_profile.';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // ALTER TABLE commits implicitly.
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE group_membership ADD COLUMN IF NOT EXISTS shown_on_profile TINYINT DEFAULT 1 NOT NULL');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE group_membership DROP COLUMN IF EXISTS shown_on_profile');
    }
}
