<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * activitiesattendees.id is the AUTO_INCREMENT primary key on production. App\Entity\ActivityAttendee
 * used (activityId, attendeeId) as its key instead, so databases created from the mapping
 * (test:database:create) got a plain id column and inserts without an id failed. The mapping now matches
 * production; this aligns databases that were created from the old mapping and does nothing on production.
 */
final class Version20261025120000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Make activitiesattendees.id the AUTO_INCREMENT primary key where it is not yet';
    }

    #[Override]
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $extra = $this->connection->fetchOne(
            "SELECT EXTRA FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = 'activitiesattendees' AND column_name = 'id'"
        );
        $this->skipIf(false === $extra || str_contains((string) $extra, 'auto_increment'), 'activitiesattendees.id is already the AUTO_INCREMENT key.');

        $this->addSql('ALTER TABLE activitiesattendees DROP PRIMARY KEY, MODIFY id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // Nothing to restore: production always had this key.
    }
}
