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
        // The choices first, then the members by id: in one UPDATE with member, MariaDB can't use an index for the
        // byte comparison with language and walks every member once per language. main's values are language ids
        // (digits), develop's short codes.
        $choices = $this->connection->fetchAllKeyValue(<<<'SQL'
            SELECT mp.IdMember, l.ShortCode
            FROM preferences p
            INNER JOIN memberspreferences mp ON mp.IdPreference = p.id
            INNER JOIN language l ON BINARY l.ShortCode = BINARY mp.Value
            WHERE p.codeName = 'PreferenceLanguage' AND mp.Value NOT REGEXP '^[0-9]+$'
            SQL);
        foreach ($choices as $memberId => $locale) {
            $this->connection->executeStatement('UPDATE member SET Locale = ? WHERE id = ?', [$locale, $memberId]);
        }
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
