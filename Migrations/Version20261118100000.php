<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * The site's language a member picks is kept on the member (member.Locale), which the login and the mails use. Until
 * now develop's language menu wrote it into the preference PreferenceLanguage as a language short code, which nothing
 * read: those choices move to member.Locale and leave the preference. main's values of the preference (language ids,
 * digits) stay, and so does the preference itself: main reads them (and member.Locale got them when member was
 * filled, Version20200601010000).
 *
 * memberspreferences and languages are main's tables, member and language develop's, with other collations: the
 * codes are compared as bytes.
 *
 * down() brings main's preference back where an earlier version of up() removed it (with every member's value):
 * the row as main had it, and each member's language id from member.Locale.
 */
final class Version20261118100000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Keep the member\'s site language in member.Locale; develop\'s choices leave the preference PreferenceLanguage.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        // The choices first, then the members by id: in one UPDATE with member, MariaDB can't use an index for the
        // byte comparison with language and walks every member once per language.
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
        // only develop's values (codes); main's (ids) stay for main
        $this->connection->executeStatement(<<<'SQL'
            DELETE mp FROM memberspreferences mp
            INNER JOIN preferences p ON p.id = mp.IdPreference
            WHERE p.codeName = 'PreferenceLanguage' AND mp.Value NOT REGEXP '^[0-9]+$'
            SQL);
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // up() keeps main's preference and values; only an earlier version of it removed them, with the row
        if (false !== $this->connection->fetchOne("SELECT id FROM preferences WHERE codeName = 'PreferenceLanguage'")) {
            $this->write('PreferenceLanguage is in place: nothing to bring back for main.');

            return;
        }
        // main's row, as it was, with its id if that's free
        $idFree = false === $this->connection->fetchOne('SELECT id FROM preferences WHERE id = 1');
        $this->connection->executeStatement(
            'INSERT INTO preferences (id, position, codeName, codeDescription, Description, created, DefaultValue,'
            . " PossibleValues, Status) VALUES (?, 10, 'PreferenceLanguage', 'PreferenceLanguageDesc', ?,"
            . " '2013-01-26 23:05:53', '0', '', 'Normal')",
            [
                $idFree ? 1 : null,
                "This preference will be used for language preference.\r\nIt is initially set to the language the"
                    . " member has choosen at first profile update \r\nMembers.Preferences.Value will contain the"
                    . ' corresponding IdLanguage',
            ],
        );
        $preferenceId = $this->connection->fetchOne("SELECT id FROM preferences WHERE codeName = 'PreferenceLanguage'");
        // main's values: the language id of each member's site language (member.Locale got them when member was
        // filled, so nearly every member gets the old value back)
        $this->connection->executeStatement(
            <<<'SQL'
                INSERT INTO memberspreferences (IdMember, IdPreference, Value, created)
                SELECT m.id, ?, ls.id, NOW()
                FROM member m
                INNER JOIN languages ls ON BINARY ls.ShortCode = BINARY m.Locale
                SQL,
            [$preferenceId],
        );
    }
}
