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
        // Nothing to bring back for main: it doesn't read geo__countries (and the command that filled it is gone). So
        // the migrations can be rolled back past this one when stage switches back to main.
        $this->write('geo__countries stays dropped: nothing reads it.');
    }
}
