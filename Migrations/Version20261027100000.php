<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Drops geo__countries where it still exists. Neither main nor develop reads it: every country is a place
 * in location, and the continent follows from the country code (PlacesModel). Stage never had the table.
 */
final class Version20261027100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Drop geo__countries, which nothing uses';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // DROP TABLE commits implicitly.
        return false;
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('DROP TABLE IF EXISTS geo__countries');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('geo__countries was filled by geonames:update --countries, which is gone.');
    }
}
