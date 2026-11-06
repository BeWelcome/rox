<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * The countries a greeter chose on the new members page, kept for their next visit: a preference that the
 * preferences page doesn't show (Inactive), its value the geoname ids.
 */
final class Version20261106100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add the hidden preference NewMembersCountries (the new members page\'s countries)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO preferences (position, codeName, codeDescription, Description, created, DefaultValue, PossibleValues, Status) SELECT 200, 'NewMembersCountries', 'admin.newmembers.countries', 'The countries a volunteer greets new members of (geoname ids, comma separated)', NOW(), '', '', 'Inactive' WHERE NOT EXISTS (SELECT 1 FROM preferences WHERE codeName = 'NewMembersCountries')");
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql("DELETE mp FROM memberspreferences mp INNER JOIN preferences p ON p.id = mp.IdPreference WHERE p.codeName = 'NewMembersCountries'");
        $this->addSql("DELETE FROM preferences WHERE codeName = 'NewMembersCountries'");
    }
}
