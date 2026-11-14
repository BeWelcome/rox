<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * The languages a greeter chose on the new members page, kept for their next visit like the countries
 * (Version20261106100000): a preference that the preferences page doesn't show (Inactive), its value the language
 * codes.
 */
final class Version20261113100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add the hidden preference NewMembersLanguages (the new members page\'s languages)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO preferences (position, codeName, codeDescription, Description, created, DefaultValue, PossibleValues, Status) SELECT 201, 'NewMembersLanguages', 'admin.newmembers.languages', 'The languages of the new members a volunteer greets (language codes, comma separated)', NOW(), '', '', 'Inactive' WHERE NOT EXISTS (SELECT 1 FROM preferences WHERE codeName = 'NewMembersLanguages')");
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql("DELETE mp FROM memberspreferences mp INNER JOIN preferences p ON p.id = mp.IdPreference WHERE p.codeName = 'NewMembersLanguages'");
        $this->addSql("DELETE FROM preferences WHERE codeName = 'NewMembersLanguages'");
    }
}
