<?php

namespace App\Command;

use App\Doctrine\CommentQualityType;
use App\Doctrine\CommentRelationsType;
use App\Doctrine\HostRestrictionsType;
use App\Doctrine\LanguageLevelType;
use App\Doctrine\StandardOffersType;
use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\ResetInterface;
use Throwable;

#[AsCommand(
    name: 'migrate:database',
    description: 'Migrates the existing production database to the new table layout using high-performance pure SQL',
)]
class MigrateDatabaseCommand extends Command implements ResetInterface
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

    private SymfonyStyle $io;
    private readonly Connection $connection;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
        $this->connection = $this->entityManager->getConnection();
        $this->entityManager->getConnection()->getConfiguration()->setMiddlewares([]);
    }

    public function reset(): void
    {
        $this->entityManager->clear();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Migrating old profiles via Bulk SQL ETL');

        $statuses = "'" . implode("', '", self::MIGRATED_STATUSES) . "'";

        try {
            $this->io->writeln('Disabling foreign key checks and truncating tables...');
            $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0;');
            $this->connection->executeStatement('TRUNCATE `address`; TRUNCATE `member_translations`; TRUNCATE `member_language_level`; TRUNCATE `friend`; TRUNCATE `member`; TRUNCATE `comment`; TRUNCATE `language`; TRUNCATE `word`; TRUNCATE `forum_thread`; TRUNCATE `forum_post`; TRUNCATE `member_photo`;');

            $steps = 13;
            $progressBar = new ProgressBar($output, $steps);
            $progressBar->setFormat("<fg=white;bg=green>\n %message% \n</>\n%current%/%max% [%bar%] %percent:3s%%\nElapsed: %elapsed:-10s% Memrory: %memory:20s%");
            $progressBar->start();

            // 1. Migrate Member Table
            $progressBar->setMessage('Migrating main `member` table...');
            $this->migrateMemberTable($statuses);
            $progressBar->advance();

            // 2. Migrate Address Table
            $progressBar->setMessage('Migrating `address` table...');
            $this->migrateAddressTable($statuses);
            $progressBar->advance();

            // 3. Migrate Base Profile Translations
            $progressBar->setMessage('Seeding default ProfileLanguage translations...');
            $this->migrateBaseTranslations($statuses);
            $progressBar->advance();

            // 4. Migrate Dynamic Profile Translations (deduced by locale usage)
            $progressBar->setMessage('Seeding derived ProfileLanguage translations...');
            $this->migrateDerivedTranslations($statuses);
            $progressBar->advance();

            // 5. Migrate Full Translations
            $progressBar->setMessage('Migrating full `member_translations`...');
            $this->migrateTranslations($statuses);
            $progressBar->advance();

            // 6. Migrate Language Levels
            $progressBar->setMessage('Migrating `member_language_level`...');
            $this->migrateLanguageLevels($statuses);
            $progressBar->advance();

            // 7. Migrate Friends & Relations
            $progressBar->setMessage('Migrating `friend` relations...');
            $this->migrateFriends($statuses);
            $progressBar->advance();

            // 8. Migrate Comments
            $progressBar->setMessage('Migrating `comments` to `comment` table...');
            $this->migrateComments($statuses);
            $progressBar->advance();

            // 9. Migrate Languages
            $progressBar->setMessage('Migrating `languages` to `language` table...');
            $this->migrateLanguages();
            $progressBar->advance();

            // 10. Migrate Words
            $progressBar->setMessage('Migrating `words` to `word` table...');
            $this->migrateWords();
            $progressBar->advance();

            // 11. Migrate Forum Threads
            $progressBar->setMessage('Migrating `ForumsThreads` to `forum_thread` table...');
            $this->migrateForumThreads();
            $progressBar->advance();

            // 12. Migrate Forum Posts
            $progressBar->setMessage('Migrating `ForumsPosts` to `forum_post` table...');
            $this->migrateForumPosts();
            $progressBar->advance();

            // 13. Migrate Member Photos
            $progressBar->setMessage('Migrating `membersphotos` to `member_photo` table...');
            $this->migrateMemberPhotos($statuses);
            $progressBar->advance();

            $progressBar->finish();
            $this->io->newLine(2);
            $this->io->success('Migration completed incredibly fast!');
        } catch (Throwable $e) {
            $this->io->error('Migration Failed: ' . $e->getMessage());

            return Command::FAILURE;
        } finally {
            $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1;');
        }

        return Command::SUCCESS;
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
                INNER JOIN geo__names g ON m.IdCity = g.geoname_id
                WHERE m.Status NOT IN ('AskToLeave', 'TakenOut')
                AND m.Status IN ($statuses)
            SQL;

        $this->connection->executeStatement($sql);
    }

    private function migrateBaseTranslations(string $statuses): void
    {
        $sql = <<<SQL
                INSERT IGNORE INTO member_translations (object_id, locale, field, content)
                SELECT id, 'en', 'ProfileLanguage', 'en'
                FROM members 
                WHERE Status IN ($statuses)
            SQL;

        $this->connection->executeStatement($sql);
    }

    private function migrateDerivedTranslations(string $statuses): void
    {
        $sql = <<<SQL
                INSERT IGNORE INTO member_translations (object_id, locale, field, content)
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
                INSERT IGNORE INTO member_translations (object_id, locale, field, content)
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

        try {
            $this->connection->executeStatement($sql);
        } catch (Exception $e) {
            // If it fails, let's dump the actual columns to help debug
            if (str_contains($e->getMessage(), 'Unknown column')) {
                $columns = $this->connection->executeQuery('SHOW COLUMNS FROM member_language_level')->fetchAllAssociative();
                $columnNames = array_column($columns, 'Field');
                throw new RuntimeException('Column error during language level migration. Actual columns in member_language_level: ' . implode(', ', $columnNames) . "\nOriginal error: " . $e->getMessage());
            }
            throw $e;
        }
    }

    private function migrateFriends(string $statuses): void
    {
        $sql = <<<SQL
                INSERT IGNORE INTO friend (created, updated, left_confirmed, left_id, right_id, right_confirmed)
                SELECT 
                    sr.created,
                    sr.updated,
                    IF(sr.Confirmed = 'Yes', 1, 0),
                    LEAST(sr.IdOwner, sr.IdRelation),
                    GREATEST(sr.IdOwner, sr.IdRelation),
                    IF(sr.Confirmed = 'Yes', 1, 0)
                FROM specialrelations sr
                INNER JOIN members m ON (m.id = sr.IdOwner OR m.id = sr.IdRelation)
                WHERE m.Status IN ($statuses)
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
                    w.Sentence,
                    ft.first_postid,
                    ft.last_postid,
                    ft.replies,
                    ft.views,
                    ft.stickyvalue,
                    l.ShortCode,
                    ft.IdGroup,
                    ft.ThreadVisibility,
                    ft.WhoCanReply,
                    ft.ThreadDeleted
                FROM forums_threads ft
                INNER JOIN words w ON ft.IdTitle = w.id
                INNER JOIN languages l ON w.IdLanguage = l.id
            SQL;

        $this->connection->executeStatement($sql);
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
                    w.Sentence,
                    fp.IdContent,
                    fp.OwnerCanStillEdit,
                    fp.last_edittime,
                    fp.last_editorid,
                    fp.edit_count,
                    l.ShortCode,
                    fp.PostDeleted
                FROM forums_posts fp
                INNER JOIN words w ON fp.IdContent = w.id
                INNER JOIN languages l ON w.IdLanguage = l.id
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
