<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260713152915 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Remove group memberships whose group no longer exists';
    }

    public function up(Schema $schema): void
    {
        // main's tables; a new database built from develop's entities has neither. Asks for the table names only:
        // $schema reads every column, and main's ENUM columns (mapped to string without a length) then fail the
        // schema comparison after the migration
        $this->skipIf(
            !$this->connection->createSchemaManager()->tablesExist(['membersgroups', 'groups']),
            'No membersgroups or groups table.',
        );
        $this->addSql('DELETE mg FROM membersgroups mg LEFT JOIN `groups` g ON g.id = mg.IdGroup WHERE g.id IS NULL');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // The deleted memberships pointed at no group: main can't use them either, so there is nothing to bring back,
        // and the migrations can be rolled back past this one when stage switches back to main.
        $this->write('The memberships of groups that no longer exist stay deleted.');
    }
}
