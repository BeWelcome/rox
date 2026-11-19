<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * The site's language a member picks is kept on the member (member.Locale), which the login and the mails use. Until
 * now develop's language menu wrote it into the preference PreferenceLanguage, which nothing read: those choices
 * (language short codes) move to member.Locale, and the preference goes. main's numeric values of the preference
 * (language ids) came along when member was filled (Version20200601010000).
 *
 * memberspreferences is main's table and language develop's, with other collations: the codes are compared as bytes.
 */
final class Version20261118100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Keep the member\'s site language in member.Locale, remove the preference PreferenceLanguage.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement(<<<'SQL'
            UPDATE member m
            INNER JOIN memberspreferences mp ON mp.IdMember = m.id
            INNER JOIN preferences p ON p.id = mp.IdPreference AND p.codeName = 'PreferenceLanguage'
            INNER JOIN language l ON BINARY l.ShortCode = BINARY mp.Value
            SET m.Locale = l.ShortCode
            SQL);
        $this->connection->executeStatement(<<<'SQL'
            DELETE mp FROM memberspreferences mp
            INNER JOIN preferences p ON p.id = mp.IdPreference
            WHERE p.codeName = 'PreferenceLanguage'
            SQL);
        $this->connection->executeStatement("DELETE FROM preferences WHERE codeName = 'PreferenceLanguage'");
    }

    public function down(Schema $schema): void
    {
        // the members' choices are in member.Locale; the preference isn't read any more
        $this->throwIrreversibleMigrationException();
    }
}
