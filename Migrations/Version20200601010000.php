<?php

namespace DoctrineMigrations;

use App\Doctrine\CommentQualityType;
use App\Doctrine\CommentRelationsType;
use App\Doctrine\HostRestrictionsType;
use App\Doctrine\LanguageLevelType;
use App\Doctrine\StandardOffersType;
use App\Entity\Member;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

use function implode;
use function mb_substr;

use Override;

use function str_contains;
use function strip_tags;
use function urldecode;

final class Version20200601010000 extends AbstractMigration
{
    private const array MIGRATED_STATUSES = [
        'Active',
        'ActiveHidden',
        'Banned',
        'AskToLeave',
        'OutOfRemind',
        'ChoiceInactive',
        'SuspendedBeta',
        'PassedAway',
    ];

    private const int ADDRESS_DECRYPT_BATCH_SIZE = 2000;

    #[Override]
    public function getDescription(): string
    {
        return 'Copy member/address/translation/language-level/friend/comment/language/word/forum/photo data '
            . 'from the legacy tables (members, addresses, cryptedfields, memberstrads, specialrelations, '
            . 'comments, languages, words, forums_threads, forums_posts, translations, membersphotos) into the tables created '
            . 'by Version20200601000000. Ports the logic previously run by hand via `bin/console migrate:database` '
            . '(src/Command/MigrateDatabaseCommand.php), plus the address House Number/Street Name/Zip decryption '
            . 'that command never performed. No-ops on databases that never had the legacy tables (fresh/CI).';
    }

    #[Override]
    public function isTransactional(): bool
    {
        // Several of the source tables hold millions of rows (e.g. memberstrads); running the whole
        // copy as one transaction would hold a very long-lived transaction/undo log on production.
        // Each step below commits independently, same as the command this replaces.
        return false;
    }

    public function up(Schema $schema): void
    {
        if (!$this->tableExists('members')) {
            $this->write('Legacy `members` table not found - nothing to migrate, skipping.');

            return;
        }

        $statuses = "'" . implode("', '", self::MIGRATED_STATUSES) . "'";

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        try {
            $this->write('Migrating `members` -> `member`...');
            $this->migrateMemberTable($statuses);

            $this->write('Migrating `members` -> `address`...');
            $this->migrateAddressTable($statuses);

            $this->write('Decrypting legacy House Number/Street Name/Zip into `address`...');
            $this->decryptLegacyAddressFields($statuses);

            $this->write('Seeding default/derived `ProfileLanguage` translations...');
            $this->migrateBaseTranslations($statuses);
            $this->migrateDerivedTranslations($statuses);

            $this->write('Migrating `memberstrads` -> `member_translation`...');
            $this->migrateTranslations($statuses);

            $this->write('Migrating `memberslanguageslevel` -> `member_language_level`...');
            $this->migrateLanguageLevels($statuses);

            $this->write('Migrating `specialrelations` -> `friend`...');
            $this->migrateFriends($statuses);

            $this->write('Migrating `comments` -> `comment`...');
            $this->migrateComments($statuses);

            $this->write('Migrating `languages` -> `language`...');
            $this->migrateLanguages();

            $this->write('Migrating `words` -> `word`...');
            $this->migrateWords();

            $this->write('Migrating `forums_threads` -> `forum_thread`...');
            $this->migrateForumThreads();

            $this->write('Migrating `forums_posts` -> `forum_post`...');
            $this->migrateForumPosts();

            $this->write('Migrating `translations` -> `forum_post_translation`, `forum_thread_translation`...');
            $this->migrateForumTexts();

            $this->write('Migrating `membersphotos` -> `member_photo`...');
            $this->migrateMemberPhotos($statuses);
        } finally {
            $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql('DELETE FROM member_photo');
        $this->addSql('DELETE FROM forum_post');
        $this->addSql('DELETE FROM forum_thread_translation');
        $this->addSql('DELETE FROM forum_post_translation');
        $this->addSql('DELETE FROM forum_thread');
        $this->addSql('DELETE FROM word');
        $this->addSql('DELETE FROM language');
        $this->addSql('DELETE FROM comment');
        $this->addSql('DELETE FROM friend');
        $this->addSql('DELETE FROM member_language_level');
        $this->addSql('DELETE FROM member_translation');
        $this->addSql('DELETE FROM address_translation');
        $this->addSql('DELETE FROM address');
        $this->addSql('DELETE FROM member');
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    private function tableExists(string $tableName): bool
    {
        $count = $this->connection->executeQuery(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$tableName],
        )->fetchOne();

        return (int) $count > 0;
    }

    private function migrateMemberTable(string $statuses): void
    {
        $nameHidden = Member::NAME_HIDDEN;
        $ageHidden = Member::AGE_HIDDEN;
        $genderHidden = Member::GENDER_HIDDEN;
        $addressHidden = Member::ADDRESS_HIDDEN;
        $dinnerOffer = StandardOffersType::DINNER;
        $guidedTourOffer = StandardOffersType::GUIDED_TOUR;
        $noSmoking = HostRestrictionsType::NO_SMOKING;
        $noAlcohol = HostRestrictionsType::NO_ALCOHOL;
        $noDrugs = HostRestrictionsType::NO_DRUGS;

        $sql = <<<SQL
                INSERT IGNORE INTO member (
                    id, Locale, Username, Password, Name, ShortName, Status, Email, Gender,
                    HideAttribute, bewelcomed, BirthDate, LastActive, LastSwitchToActive,
                    Reminders, created, updated, RegistrationKey, Accommodation, MaxGuests,
                    HostingInterest, StandardOffers, Occupation, PleaseBring, OfferGuests,
                    OfferHosts, GettingThere, ILiveWith, MaxLengthOfStay, Restrictions,
                    HouseRules, Hobbies, Books, Movies, Music, Organizations, PastTrips,
                    PlannedTrips
                )
                SELECT
                    m.id,
                    COALESCE(l.ShortCode, 'en'),
                    m.Username,
                    m.PassWord,
                    REPLACE(CONCAT_WS(' ', m.FirstName, NULLIF(m.SecondName, ''), NULLIF(m.LastName, '')), '  ', ' '),
                    IF((m.HideAttribute & 1) != 1, m.FirstName, NULL),
                    IF(m.Status IN ($statuses), m.Status, 'ChoiceInactive'),
                    m.Email,
                    IF(m.Gender = 'IDontTell', 'other', m.Gender),
                    (
                        IF((m.HideAttribute & 1) OR (m.HideAttribute & 2) OR (m.HideAttribute & 4), $nameHidden, 0) |
                        IF(m.HideBirthDate = 'Yes', $ageHidden, 0) |
                        IF(m.HideGender = 'Yes', $genderHidden, 0) |
                        IF(m.AdressHidden = 'Yes', $addressHidden, 0)
                    ),
                    m.bewelcomed,
                    IF(m.BirthDate = '0000-00-00', '1970-01-01', m.BirthDate),
                    IF(m.LastLogin = '0000-00-00 00:00:00', NULL, m.LastLogin),
                    m.LastSwitchToActive,
                    m.NbRemindWithoutLogingIn,
                    IF(m.created = '0000-00-00 00:00:00', '1970-01-01 00:00:00', m.created),
                    IF(m.updated = '0000-00-00 00:00:00' OR m.updated IS NULL, '1970-01-01 00:00:00', m.updated),
                    m.registration_key,
                    CASE WHEN m.Accomodation IN ('dependonrequest', 'anytime') THEN 'yes' ELSE 'no' END,
                    IF(m.MaxGuest > 100, 100, m.MaxGuest),
                    m.hosting_interest,
                    TRIM(LEADING ',' FROM CONCAT_WS(',',
                        IF(m.TypicOffer LIKE '%Dinner%', '$dinnerOffer', NULL),
                        IF(m.TypicOffer LIKE '%GuidedTour%', '$guidedTourOffer', NULL)
                    )),
                    NULL, NULL, NULL, NULL, NULL, NULL, NULL,
                    CASE m.Restrictions
                        WHEN 'NoSmoker' THEN '$noSmoking'
                        WHEN 'NoDrugs' THEN '$noDrugs'
                        WHEN 'NoAlchool' THEN '$noAlcohol'
                    END,
                    NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL
                FROM members m
                LEFT JOIN memberspreferences mp ON mp.IdMember = m.id AND mp.IdPreference = 1
                LEFT JOIN languages l ON mp.Value = l.id
                WHERE m.Status IN ($statuses)
            SQL;

        $this->connection->executeStatement($sql);
    }

    private function migrateAddressTable(string $statuses): void
    {
        // `address` has no unique constraint on member_id (it's a surrogate-keyed OneToMany on
        // Member), so INSERT IGNORE alone does not make this step idempotent - re-running it would
        // insert a second address row per member every time. The MigrateDatabaseCommand version of
        // this query lacked this guard; it's added here since migrations can legitimately be replayed.
        $sql = <<<SQL
                INSERT IGNORE INTO address (member_id, active, location, latitude, longitude, wheelChairAccessible)
                SELECT
                    m.id,
                    1,
                    g.geoname_id,
                    m.Latitude,
                    m.Longitude,
                    IF(m.TypicOffer LIKE '%WheelchairAccessible%', 1, 0)
                FROM members m
                INNER JOIN location g ON m.IdCity = g.geoname_id
                WHERE m.Status NOT IN ('AskToLeave', 'TakenOut')
                AND m.Status IN ($statuses)
                AND NOT EXISTS (SELECT 1 FROM address ex WHERE ex.member_id = m.id)
            SQL;

        $this->connection->executeStatement($sql);
    }

    /**
     * `addresses.HouseNumber`/`StreetName`/`Zip` are not plain text on the legacy schema: they are
     * integer pointers into `cryptedfields` (0 means "not set"). The value stored there is wrapped in
     * a `<membercrypted>...</membercrypted>` or `<admincrypted>...</admincrypted>` tag and urlencode()'d
     * - not actual cryptography (see modules/crypt/lib/crypt.lib.php MOD_crypt::GetDeCryptM/GetDeCryptA).
     * MySQL has no urldecode(), so this step runs in PHP rather than as a plain addSql() statement.
     */
    private function decryptLegacyAddressFields(string $statuses): void
    {
        $sql = <<<SQL
                SELECT
                    a.IdMember AS member_id,
                    cf_h.MemberCryptedValue AS house_raw,
                    cf_s.MemberCryptedValue AS street_raw,
                    cf_z.MemberCryptedValue AS zip_raw
                FROM addresses a
                INNER JOIN members m ON m.id = a.IdMember
                LEFT JOIN cryptedfields cf_h ON cf_h.id = a.HouseNumber AND a.HouseNumber <> 0
                LEFT JOIN cryptedfields cf_s ON cf_s.id = a.StreetName AND a.StreetName <> 0
                LEFT JOIN cryptedfields cf_z ON cf_z.id = a.Zip AND a.Zip <> 0
                WHERE m.Status NOT IN ('AskToLeave', 'TakenOut')
                AND m.Status IN ($statuses)
                AND (a.HouseNumber <> 0 OR a.StreetName <> 0 OR a.Zip <> 0)
            SQL;

        $rows = $this->connection->executeQuery($sql)->iterateAssociative();

        $update = $this->connection->prepare(
            'UPDATE address SET HouseNumber = ?, StreetName = ?, Zip = ? WHERE member_id = ?',
        );

        $truncated = 0;
        $processed = 0;
        foreach ($rows as $row) {
            [$house, $houseTruncated] = $this->decodeLegacyCryptedValue($row['house_raw']);
            [$street, $streetTruncated] = $this->decodeLegacyCryptedValue($row['street_raw']);
            [$zip, $zipTruncated] = $this->decodeLegacyCryptedValue($row['zip_raw']);
            $truncated += ($houseTruncated ? 1 : 0) + ($streetTruncated ? 1 : 0) + ($zipTruncated ? 1 : 0);

            $update->bindValue(1, $house);
            $update->bindValue(2, $street);
            $update->bindValue(3, $zip);
            $update->bindValue(4, $row['member_id']);
            $update->executeStatement();

            if (0 === ++$processed % self::ADDRESS_DECRYPT_BATCH_SIZE) {
                $this->write("  ...decrypted $processed address rows so far");
            }
        }

        $this->write("Decrypted $processed address rows ($truncated value(s) exceeded 255 chars and were truncated).");
    }

    /** @return array{0: string|null, 1: bool} [decoded value truncated to column length, whether it was truncated] */
    private function decodeLegacyCryptedValue(?string $value): array
    {
        if (null === $value) {
            return [null, false];
        }

        $decoded = str_contains($value, '<membercrypted>') || str_contains($value, '<admincrypted>')
            ? urldecode(strip_tags($value))
            : $value;

        $truncatedValue = mb_substr($decoded, 0, 255);

        return [$truncatedValue, $truncatedValue !== $decoded];
    }

    private function migrateBaseTranslations(string $statuses): void
    {
        $sql = <<<SQL
                INSERT IGNORE INTO member_translation (object_id, locale, field, content)
                SELECT id, 'en', 'ProfileLanguage', 'en'
                FROM members
                WHERE Status IN ($statuses)
            SQL;

        $this->connection->executeStatement($sql);
    }

    private function migrateDerivedTranslations(string $statuses): void
    {
        $sql = <<<SQL
                INSERT IGNORE INTO member_translation (object_id, locale, field, content)
                SELECT DISTINCT mt.IdOwner, l.ShortCode, 'ProfileLanguage', l.ShortCode
                FROM memberstrads mt
                INNER JOIN members m ON m.id = mt.IdOwner
                INNER JOIN languages l ON mt.IdLanguage = l.id
                WHERE mt.TableColumn LIKE 'members.%'
                AND m.Status IN ($statuses)
            SQL;

        $this->connection->executeStatement($sql);
    }

    private function migrateTranslations(string $statuses): void
    {
        $sql = <<<SQL
                INSERT IGNORE INTO member_translation (object_id, locale, field, content)
                SELECT mt.IdOwner, l.ShortCode,
                    CASE REPLACE(mt.TableColumn, 'members.', '')
                        WHEN 'ProfileSummary' THEN 'AboutMe'
                        WHEN 'AdditionalAccomodationInfo' THEN 'AdditionalAccommodationInfo'
                        WHEN 'MaxLenghtOfStay' THEN 'MaxLengthOfStay'
                        ELSE REPLACE(mt.TableColumn, 'members.', '')
                    END,
                    mt.Sentence
                FROM memberstrads mt
                INNER JOIN members m ON m.id = mt.IdOwner
                INNER JOIN languages l ON mt.IdLanguage = l.id
                WHERE mt.Sentence IS NOT NULL AND mt.Sentence != ''
                AND mt.TableColumn LIKE 'members.%'
                AND m.Status IN ($statuses)
            SQL;

        $this->connection->executeStatement($sql);
    }

    private function migrateLanguageLevels(string $statuses): void
    {
        $motherTongue = LanguageLevelType::MOTHER_TONGUE;
        $expert = LanguageLevelType::EXPERT;
        $fluent = LanguageLevelType::FLUENT;
        $intermediate = LanguageLevelType::INTERMEDIATE;
        $beginner = LanguageLevelType::BEGINNER;
        $helloOnly = LanguageLevelType::HELLO_ONLY;

        $sql = <<<SQL
                INSERT IGNORE INTO member_language_level (member_id, language, level)
                SELECT
                    mll.IdMember,
                    l.ShortCode,
                    CASE mll.Level
                        WHEN 'MotherLanguage' THEN '$motherTongue'
                        WHEN 'Expert' THEN '$expert'
                        WHEN 'Fluent' THEN '$fluent'
                        WHEN 'Intermediate' THEN '$intermediate'
                        WHEN 'Beginner' THEN '$beginner'
                        WHEN 'HelloOnly' THEN '$helloOnly'
                        ELSE ''
                    END AS parsed_level
                FROM memberslanguageslevel mll
                INNER JOIN languages l ON mll.IdLanguage = l.id
                INNER JOIN members m ON m.id = mll.IdMember
                WHERE m.Status IN ($statuses)
                HAVING parsed_level != ''
            SQL;

        $this->connection->executeStatement($sql);
    }

    private function migrateFriends(string $statuses): void
    {
        // One row per pair (A→B and B→A are one friendship), confirmed if either relation was; both members
        // must be copied too.
        $sql = <<<SQL
                INSERT IGNORE INTO friend (created, updated, left_confirmed, left_id, right_id, right_confirmed)
                SELECT
                    MIN(sr.created),
                    MAX(sr.updated),
                    MAX(IF(sr.Confirmed = 'Yes', 1, 0)),
                    LEAST(sr.IdOwner, sr.IdRelation),
                    GREATEST(sr.IdOwner, sr.IdRelation),
                    MAX(IF(sr.Confirmed = 'Yes', 1, 0))
                FROM specialrelations sr
                INNER JOIN members owner ON owner.id = sr.IdOwner
                INNER JOIN members relation ON relation.id = sr.IdRelation
                WHERE owner.Status IN ($statuses) AND relation.Status IN ($statuses)
                    AND sr.IdOwner <> sr.IdRelation
                GROUP BY LEAST(sr.IdOwner, sr.IdRelation), GREATEST(sr.IdOwner, sr.IdRelation)
            SQL;

        $this->connection->executeStatement($sql);
    }

    private function migrateComments(string $statuses): void
    {
        $positive = CommentQualityType::POSITIVE;
        $neutral = CommentQualityType::NEUTRAL;
        $negative = CommentQualityType::NEGATIVE;

        $wasGuest = CommentRelationsType::WAS_GUEST;
        $wasHost = CommentRelationsType::WAS_HOST;
        $metOnce = CommentRelationsType::ONLY_MET_ONCE;
        $family = CommentRelationsType::IS_FAMILY;
        $closeFriend = CommentRelationsType::IS_CLOSE_FRIEND;
        $travelBuddy = CommentRelationsType::TRAVEL_BUDDY;
        $friend = CommentRelationsType::IS_FRIEND;
        $chatted = CommentRelationsType::ONLINE_COMMUNICATION;

        $sql = <<<SQL
                INSERT IGNORE INTO comment (
                    id, to_member_id, from_member_id, relations, quality, comment, created, updated,
                    admin_action, show_to_other_members, allow_edit
                )
                SELECT
                    c.id,
                    c.IdToMember,
                    c.IdFromMember,
                    REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(c.Relations,
                        'hewasmyguest', '$wasGuest'),
                        'hehostedme', '$wasHost'),
                        'OnlyOnce', '$metOnce'),
                        'HeIsMyFamily', '$family'),
                        'HeHisMyOldCloseFriend', '$closeFriend'),
                        'TravelledTogether', '$travelBuddy'),
                        'WeAreFriends', '$friend'),
                        'CommunicatedOnline', '$chatted'
                    ),
                    CASE c.Quality
                        WHEN 'Good' THEN '$positive'
                        WHEN 'Neutral' THEN '$neutral'
                        WHEN 'Bad' THEN '$negative'
                        ELSE '$neutral'
                    END,
                    c.TextFree,
                    c.created,
                    c.updated,
                    c.AdminAction,
                    c.DisplayInPublic,
                    c.AllowEdit
                FROM comments c
                INNER JOIN member m_to ON c.IdToMember = m_to.id
                INNER JOIN member m_from ON c.IdFromMember = m_from.id
                WHERE m_to.Status IN ($statuses) AND m_from.Status IN ($statuses)
            SQL;

        $this->connection->executeStatement($sql);
    }

    private function migrateLanguages(): void
    {
        $sql = <<<'SQL'
                INSERT IGNORE INTO language (
                    shortCode, name, isWrittenLanguage, isSpokenLanguage, isSignLanguage
                )
                SELECT
                    l.ShortCode,
                    l.Name,
                    l.IsWrittenLanguage,
                    l.IsSpokenLanguage,
                    l.IsSignLanguage
                FROM languages l
            SQL;

        $this->connection->executeStatement($sql);
    }

    private function migrateWords(): void
    {
        $sql = <<<'SQL'
                INSERT IGNORE INTO word (
                    code, domain, shortCode, sentence, created, updated, majorupdate,
                    donottranslate, author_id, description, translationPriority, isarchived
                )
                SELECT
                    w.code,
                    w.domain,
                    l.ShortCode,
                    w.Sentence,
                    w.created,
                    w.updated,
                    w.majorUpdate,
                    w.donottranslate,
                    w.IdMember,
                    w.Description,
                    w.TranslationPriority,
                    w.isarchived
                FROM words w
                INNER JOIN languages l ON w.IdLanguage = l.id
            SQL;

        $this->connection->executeStatement($sql);
    }

    private function migrateForumThreads(): void
    {
        $sql = <<<'SQL'
                INSERT IGNORE INTO forum_thread (
                    id, expiredate, IdTitle, title, first_postid, last_postid, replies, views,
                    stickyvalue, ShortCode, IdGroup, ThreadVisibility, WhoCanReply, ThreadDeleted
                )
                SELECT
                    ft.id,
                    ft.expiredate,
                    ft.IdTitle,
                    COALESCE(
                        (SELECT t.Sentence FROM translations t WHERE t.IdTrad = ft.IdTitle AND t.IdLanguage = ft.IdFirstLanguageUsed ORDER BY t.id LIMIT 1),
                        (SELECT t.Sentence FROM translations t WHERE t.IdTrad = ft.IdTitle ORDER BY t.id LIMIT 1),
                        ft.title
                    ),
                    -- A first/last post of another thread (a moved post; once in production) would break the
                    -- unique keys of the one-to-one mappings: take the thread's own first/last post instead.
                    COALESCE(
                        (SELECT p.id FROM forums_posts p WHERE p.id = ft.first_postid AND p.threadid = ft.id),
                        (SELECT MIN(p.id) FROM forums_posts p WHERE p.threadid = ft.id),
                        ft.first_postid
                    ),
                    COALESCE(
                        (SELECT p.id FROM forums_posts p WHERE p.id = ft.last_postid AND p.threadid = ft.id),
                        (SELECT MAX(p.id) FROM forums_posts p WHERE p.threadid = ft.id),
                        ft.last_postid
                    ),
                    ft.replies,
                    ft.views,
                    ft.stickyvalue,
                    COALESCE(l.ShortCode, 'en'),
                    ft.IdGroup,
                    ft.ThreadVisibility,
                    ft.WhoCanReply,
                    ft.ThreadDeleted
                FROM forums_threads ft
                LEFT JOIN languages l ON l.id = ft.IdFirstLanguageUsed
            SQL;

        $this->connection->executeStatement($sql);
    }

    /**
     * Every language of the posts' texts and the threads' titles, from `translations` (rows are found by the
     * IdTrad in forum_post.IdContent / forum_thread.IdTitle), with who translated it and when. The tags'
     * texts are left behind.
     */
    private function migrateForumTexts(): void
    {
        foreach ([
            ['forum_post_translation', 'forum_post', 'IdContent', 'message'],
            ['forum_thread_translation', 'forum_thread', 'IdTitle', 'title'],
        ] as [$translationTable, $table, $idTradColumn, $field]) {
            $this->connection->executeStatement(
                "INSERT IGNORE INTO {$translationTable} (locale, field, content, object_id, translator_id, created_at, updated_at)
                 SELECT COALESCE(l.ShortCode, 'en'), ?, t.Sentence, o.id, m.id,
                     IF(t.created < '1970-01-02', NULL, t.created),
                     IF(t.updated < '1970-01-02' OR t.updated = t.created, NULL, t.updated)
                 FROM {$table} o
                 JOIN translations t ON t.IdTrad = o.{$idTradColumn}
                 LEFT JOIN languages l ON l.id = t.IdLanguage
                 LEFT JOIN member m ON m.id = t.IdTranslator
                 WHERE o.{$idTradColumn} <> 0
                 ORDER BY t.id",
                [$field]
            );
        }
    }

    private function migrateForumPosts(): void
    {
        $sql = <<<'SQL'
                INSERT IGNORE INTO forum_post (
                    id, threadid, PostVisibility, IdWriter, create_time, message, IdContent,
                    OwnerCanStillEdit, last_edittime, last_editorid, edit_count, ShortCode, PostDeleted
                )
                SELECT
                    fp.id,
                    fp.threadid,
                    fp.PostVisibility,
                    fp.IdWriter,
                    fp.create_time,
                    COALESCE(
                        (SELECT t.Sentence FROM translations t WHERE t.IdTrad = fp.IdContent AND t.IdLanguage = fp.IdFirstLanguageUsed ORDER BY t.id LIMIT 1),
                        (SELECT t.Sentence FROM translations t WHERE t.IdTrad = fp.IdContent ORDER BY t.id LIMIT 1),
                        fp.message
                    ),
                    fp.IdContent,
                    fp.OwnerCanStillEdit,
                    fp.last_edittime,
                    fp.last_editorid,
                    fp.edit_count,
                    COALESCE(l.ShortCode, 'en'),
                    fp.PostDeleted
                FROM forums_posts fp
                LEFT JOIN languages l ON l.id = fp.IdFirstLanguageUsed
            SQL;

        $this->connection->executeStatement($sql);
    }

    private function migrateMemberPhotos(string $statuses): void
    {
        $sql = <<<SQL
                INSERT IGNORE INTO member_photo (
                    id, FilePath, member_id, created
                )
                SELECT
                    mp.id,
                    mp.FilePath,
                    mp.IdMember,
                    mp.created
                FROM membersphotos mp
                INNER JOIN member m ON m.id = mp.IdMember
                WHERE m.Status IN ($statuses)
            SQL;

        $this->connection->executeStatement($sql);
    }
}
