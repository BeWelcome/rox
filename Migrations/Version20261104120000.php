<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * An index on member (Status, updated) for the volunteer tools that look for banned and rejected members, newest
 * first: Top spammers and Damage done (MessageRepository).
 */
final class Version20261104120000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add the index idx_member_status_updated on member (Status, updated).';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // CREATE INDEX commits implicitly.
        return false;
    }

    public function up(Schema $schema): void
    {
        // the test database is created from the entities, which have the index already
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_member_status_updated ON member (Status, updated)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_member_status_updated ON member');
    }
}
