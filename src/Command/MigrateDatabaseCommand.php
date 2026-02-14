<?php

namespace App\Command;

use App\Doctrine\HostRestrictionsType;
use App\Doctrine\LanguageLevelType;
use App\Doctrine\StandardOffersType;
use App\Doctrine\TypicalOfferType;
use App\Entity\Languages;
use App\Entity\Location;
use App\Entity\Member;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'migrate:database',
    description: 'Migrates the existing production database to the new table layout',
)]
class MigrateDatabaseCommand extends Command
{
    private const int MEMBER_FIRSTNAME_HIDDEN = 1;
    private const int MEMBER_SECONDNAME_HIDDEN = 2;
    private const int MEMBER_LASTNAME_HIDDEN = 4;

    private const array TRANSLATED_FIELDS = [
        'Occupation',
        'ILiveWith',
        'MaxLenghtOfStay',
        'MotivationForHospitality',
        'Offer',
        'Organizations',
        'AdditionalAccomodationInfo',
        'OtherRestrictions',
        'ProfileSummary',
        'Hobbies',
        'Books',
        'Music',
        'Movies',
        'PleaseBring',
        'OfferGuests',
        'OfferHosts',
        'PublicTransport',
        'PastTrips',
        'PlannedTrips',
    ];

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

    private const string INSERT = <<<'SQL'
            INSERT INTO member ( 
                id, 
                Locale,
                Username, 
                Password, 
                Name, 
                ShortName, 
                Status, 
                Email, 
                Gender, 
                HideAttribute, 
                bewelcomed, 
                BirthDate,
                LastActive,
                LastSwitchToActive,
                Reminders,
                created,
                updated,
                RegistrationKey,
                Accommodation, 
                MaxGuests,
                HostingInterest,
                StandardOffers,
                /* Translated fields are set to null */
                Occupation,
                PleaseBring,
                OfferGuests,
                OfferHosts,
                GettingThere,
                ILiveWith,
                MaxLengthOfStay,
                Restrictions,
                HouseRules,
                Hobbies,
                Books,
                Movies,
                Music,
                Organizations,                
                PastTrips,
                PlannedTrips
            ) VALUES (
                :id,
                :Locale,
                :Username,
                :Password,
                :Name,
                :ShortName,
                :Status,
                :Email,
                :Gender,
                :HideAttribute,
                :bewelcomed,
                :BirthDate,
                :LastActive,
                :LastSwitchToActive,
                :Reminders,
                :created,
                :updated,
                :RegistrationKey,
                :Accommodation,
                :MaxGuests,
                :HostingInterest,
                :StandardOffers,
                /* Translated fields are set to null */
                null /* :Occupation*/,
                null /* :PleaseBring*/, 
                null /* :OfferGuests*/,
                null /* :OfferHosts*/,
                null /* :GettingThere*/,
                null /* :ILiveWith*/,
                null /* :MaxLengthOfStay*/,
                null /* :Restrictions*/,
                null /* :HouseRules*/,
                null /* :Hobbies*/,
                null /* :Books*/,
                null /* :Movies*/,
                null /* :Music*/,
                null /* :Organizations*/,
                null /* :PastTrips*/,
                null /* :PlannedTrips */
            )
        SQL;

    private SymfonyStyle $io;
    private array $errorMembers = [];
    private readonly Connection $connection;
    private array $languages;
    private ProgressBar $progressBar;
    private EntityRepository $locationRepository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();

        $this->connection = $this->entityManager->getConnection();
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->success('Migrating old profiles.');

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0;');
        $this->connection->executeStatement('TRUNCATE `address`; TRUNCATE `member_translations`; TRUNCATE `member_language_level`; TRUNCATE `friend`; TRUNCATE `member`;');

        $sql = "SELECT COUNT(m.id) FROM members m WHERE m.status IN ('" . implode("', '", self::MIGRATED_STATUSES) . "')";

        $countOfMembers = $this->connection->executeQuery(
            $sql
        )->fetchOne();

        $this->io->writeln('Migrating ' . $countOfMembers . ' members.');

        $this->locationRepository = $this->entityManager->getRepository(Location::class);
        $languagesRepository = $this->entityManager->getRepository(Languages::class);
        $rawLanguages = $languagesRepository->findAll();

        $this->languages = [];
        foreach ($rawLanguages as $language) {
            $this->languages[$language->getId()] = $language;
        }

        $this->errorMembers = [];
        $batchSize = 2000;

        $this->progressBar = new ProgressBar($output, $countOfMembers);
        $this->progressBar->setFormat(
            "<fg=white;bg=green>\n %info:-60s% \n</>\n%current%/%max% [%bar%] %percent:3s%%\n%elapsed:-10% %%estimated:-10s%  %memory:20s%\nLast error: %error%"
        );
        $this->progressBar->setMessage("Migrating {$countOfMembers} members to new member table including translations.", 'info');
        $this->progressBar->setMessage('none', 'error');
        $this->progressBar->minSecondsBetweenRedraws(10.0);
        $this->progressBar->start();

        // Make sure the tables member and member_translations are empty

        try {
            for ($current = 0; $current < $countOfMembers; $current += $batchSize) {
                $this->migrateMembersData($current, $batchSize);
            }
            $this->reportErrors();
        } finally {
            $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->io->success('Done migrating old profiles.');

        return Command::SUCCESS;
    }

    private function migrateMembersData(int $current, int $batchSize): void
    {
        $sql = \sprintf("
            SELECT 
                * 
            FROM 
                members m 
            WHERE 
                m.status IN ('%s') 
            ORDER BY 
                id 
            LIMIT %d, %d
        ", implode("', '", self::MIGRATED_STATUSES), $current, $batchSize);

        $members = $this->connection->executeQuery($sql)->fetchAllAssociative();

        foreach ($members as $member) {
            $this->migrateMemberData($member);
            $this->migrateMemberAddress($member);
            $this->migrateMemberTranslations($member);
            $this->migrateMemberLanguageLevels($member);
            $this->migrateFamilyAndFriends($member);

            $this->progressBar->advance();
            unset($member);
        }
    }

    private function migrateMemberData(array $member): void
    {
        $memberId = $member['id'];

        $locale = $this->connection->executeQuery('SELECT mp.Value FROM memberspreferences mp WHERE mp.IdMember = :memberId AND mp.IdPreference = 1', ['memberId' => $memberId])->fetchOne();
        if (is_numeric($locale)) {
            $locale = $this->connection->executeQuery('SELECT l.ShortCode FROM memberspreferences mp, languages l WHERE mp.IdMember = :memberId AND mp.IdPreference = 1 and mp.Value = l.id', ['memberId' => $memberId])->fetchOne();
        }
        $statement = $this->connection->prepare(self::INSERT);
        $statement->bindValue('id', $memberId);
        $statement->bindValue('Locale', false === $locale ? 'en' : $locale);
        $statement->bindValue('Username', $member['Username']);
        $statement->bindValue('Password', $member['PassWord']);
        $statement->bindValue('Name', $this->getFullname($member));
        $statement->bindValue('ShortName', $this->isFirstnameShown($member) ? $member['FirstName'] : null);
        $statement->bindValue('Status', $member['Status']);
        $statement->bindValue('Email', $member['Email']);
        $statement->bindValue('Gender', $this->migrateGender($member['Gender']));
        $statement->bindValue('HideAttribute', $this->migrateHiddenFields($member));
        $statement->bindValue('bewelcomed', $member['bewelcomed']);
        if ('0000-00-00' !== $member['BirthDate']) {
            $statement->bindValue('BirthDate', new DateTime($member['BirthDate']), 'datetime');
        } else {
            $statement->bindValue('BirthDate', new DateTime('1970-01-01 00:00:00'), 'datetime');
        }
        if (null !== $member['LastLogin'] && '0000-00-00 00:00:00' !== $member['LastLogin']) {
            $statement->bindValue('LastActive', new DateTime($member['LastLogin']), 'datetime');
        } else {
            $statement->bindValue('LastActive', null);
        }
        if (null !== $member['LastSwitchToActive']) {
            $statement->bindValue('LastSwitchToActive', new DateTime($member['LastSwitchToActive']), 'datetime');
        } else {
            $statement->bindValue('LastSwitchToActive', null);
        }
        $statement->bindValue('Reminders', $member['NbRemindWithoutLogingIn']);

        if ('0000-00-00 00:00:00' !== $member['created']) {
            $statement->bindValue('created', new DateTime($member['created']), 'datetime');
        } else {
            $statement->bindValue('created', new DateTime('1970-01-01 00:00:00'), 'datetime');
        }

        if ('0000-00-00 00:00:00' !== $member['updated']) {
            if (null === $member['updated']) {
                $statement->bindValue('updated', null, 'datetime');
            } else {
                $statement->bindValue('updated', new DateTime($member['updated']), 'datetime');
            }
        } else {
            $statement->bindValue('updated', new DateTime('1970-01-01 00:00:00'), 'datetime');
        }

        $statement->bindValue('RegistrationKey', $member['registration_key']);

        $statement->bindValue('Accommodation', $this->migrateAccommodation($member['Accomodation']));
        $statement->bindValue('MaxGuests', $member['MaxGuest']);
        $statement->bindValue('HostingInterest', $member['hosting_interest']);
        $statement->bindValue('StandardOffers', $this->migrateTypicalOffer($member['TypicOffer']));

        try {
            $statement->executeStatement();
        } catch (Exception $e) {
            $this->progressBar->setMessage($member['Username'], 'error');
            $this->addErrorMemberSql($member, $e->getMessage());
        }
    }

    private function migrateMemberAddress(mixed $member): void
    {
        if ('AskToLeave' === $member['Status'] || 'TakenOut' === $member['Status']) {
            // Do not add address for members that are not active anymore
            return;
        }

        $addressSQL = 'INSERT INTO address (member_id, active, location, latitude, longitude, wheelChairAccessible) ' .
            'VALUES (:member, 1, :city, :latitude, :longitude, :wheelchairAccessible)';

        // Migrate address
        $city = $this->locationRepository->findOneBy(['geonameId' => $member['IdCity']]);
        $statement = $this->connection->prepare($addressSQL);
        $statement->bindValue('member', $member['id']);
        if (null === $city) {
            // If we can't find a city we just do not set an address
            return;
        }

        $statement->bindValue('city', $city->getGeonameid());
        $statement->bindValue('latitude', $member['Latitude']);
        $statement->bindValue('longitude', $member['Longitude']);
        $statement->bindValue(
            'wheelchairAccessible',
            $this->isWheelchairAccessible($member['TypicOffer']) ? 1 : 0
        );

        try {
            $statement->executeStatement();
        } catch (Exception $e) {
            $this->progressBar->setMessage($member['Username'], 'error');
            $this->addErrorAddress($member, $e->getMessage());
        }
    }

    private function migrateMemberTranslations(array $member): void
    {
        $memberId = $member['id'];

        // Determine currently used translation ids
        $translationIds = [];
        foreach (self::TRANSLATED_FIELDS as $translatedField) {
            if (0 !== $member[$translatedField] && null !== $member[$translatedField]) {
                $translationIds[] = $member[$translatedField];
            }
        }

        if (empty($translationIds)) {
            if ('Active' === $member['Status'] || 'OutOfRemind' === $member['Status']) {
                // Check where this happens
                $this->addErrorNoTranslations($member);
            }
        } else {
            $processedTranslations = [];
            $processedTranslations['en']['ProfileLanguage'] = true;
            $queryString = $this->addTranslation($memberId, 'en', 'ProfileLanguage', 'en');
            $memberTranslations = $this->connection->executeQuery('
                SELECT 
                    * 
                FROM 
                    memberstrads m 
                WHERE 
                    IdTrad IN (' . implode(',', $translationIds) . ')
                ORDER BY 
                    IdTrad, id desc, IdLanguage
            ')->fetchAllAssociative();

            foreach ($memberTranslations as $memberTranslation) {
                $language = $this->languages[$memberTranslation['IdLanguage']] ?? null;
                if (null === $language) {
                    $this->addErrorLanguage($member, $memberTranslation['IdLanguage']);
                    $this->progressBar->setMessage($member['Username'], 'error');
                } else {
                    $locale = $language->getShortCode();
                    $field = $this->mapField($memberTranslation['TableColumn']);
                    if (!isset($processedTranslations[$locale][$field])) {
                        if (!isset($processedTranslations[$locale]['ProfileLanguage'])) {
                            $queryString .= $this->addTranslation($memberId, $locale, 'ProfileLanguage', $locale);
                            $processedTranslations[$locale]['ProfileLanguage'] = true;
                        }

                        $content = $memberTranslation['Sentence'];
                        if (!empty($content)) {
                            $queryString .= $this->addTranslation($memberId, $locale, $field, $content);
                        }
                        $processedTranslations[$locale][$field] = true;
                    }
                }
            }

            $values = substr($queryString, 2);

            $sql = 'INSERT INTO member_translations (object_id, Locale, Field, Content) VALUES ' . $values;

            try {
                $this->connection->executeStatement($sql);
            } catch (Exception $e) {
                $this->progressBar->setMessage($member['Username'], 'error');
                $this->addErrorTranslationSql($member, $e->getMessage());
            }
        }

        unset($translationIds);
    }

    private function migrateMemberLanguageLevels(array $member): void
    {
        $memberId = $member['id'];

        // migrate language levels
        $languageLevels = $this->connection->executeQuery(
            'select * from memberslanguageslevel where Idmember = ' . $memberId
        )->fetchAllAssociative();
        foreach ($languageLevels as $languageLevel) {
            $statement = $this->connection->prepare(
                'INSERT INTO member_language_level (member_id, language, level) ' .
                'VALUES (:member_id, :language, :level)'
            );

            $language = $this->languages[$languageLevel['IdLanguage']] ?? null;
            $level = $this->migrateLanguageLevel($languageLevel['Level']);

            if (null === $language) {
                $this->progressBar->setMessage($member['Username'], 'error');
                $this->addErrorLanguage($member, $languageLevel['IdLanguage']);
            } elseif (!empty($level)) {
                $statement->bindValue('member_id', $memberId);
                $statement->bindValue('language', $language->getShortCode());
                $statement->bindValue('level', $level);
                try {
                    $statement->executeStatement();
                } catch (Exception $e) {
                    $this->progressBar->setMessage($member['Username'], 'error');
                    $this->addErrorLanguage($member, $languageLevel['IdLanguage']);
                }
            }
        }
        unset($languageLevels);
    }

    private function migrateFamilyAndFriends(array $member): void
    {
        $memberId = $member['id'];
        // migrate special relations (family and friends)
        $statement = $this->connection->prepare('
                        select 
                            sr.* 
                        from 
                            specialrelations sr 
                        where 
                            sr.IdOwner = :memberId OR sr.IdRelation = :memberId
                    ');
        $statement->bindValue('memberId', $memberId);

        $specialRelations = $statement->executeQuery()->fetchAllAssociative();

        foreach ($specialRelations as $specialRelation) {
            $statement = $this->connection->prepare(
                'INSERT IGNORE INTO friend (created, updated, confirmed, left_id, right_id) ' .
                'VALUES (:created, :updated, :confirmed, :left_id, :right_id)'
            );

            $statement->bindValue('created', $specialRelation['created']);
            $statement->bindValue('updated', $specialRelation['updated']);
            $statement->bindValue('confirmed', 'Yes' === $specialRelation['Confirmed'] ? 1 : 0);
            $left_id = min($specialRelation['IdOwner'], $specialRelation['IdRelation']);
            $right_id = max($specialRelation['IdOwner'], $specialRelation['IdRelation']);
            $statement->bindValue('left_id', $left_id);
            $statement->bindValue('right_id', $right_id);
            try {
                $statement->executeStatement();
            } catch (Exception $e) {
                $this->progressBar->setMessage($member['Username'], 'error');
                $this->addErrorMemberSql($member, $e->getMessage());
            }
        }
        unset($specialRelations);
    }

    private function migrateAccommodation(?string $accommodation): string
    {
        return match ($accommodation) {
            'dependonrequest', 'anytime' => 'yes',
            default => 'no',
        };
    }

    private function migrateTypicalOffer(string $typicalOffer): string
    {
        $standardOffer = '';
        if (str_contains(TypicalOfferType::DINNER, $typicalOffer)) {
            $standardOffer .= ',' . StandardOffersType::DINNER;
        }
        if (str_contains(TypicalOfferType::GUIDED_TOUR, $typicalOffer)) {
            $standardOffer .= ',' . StandardOffersType::GUIDED_TOUR;
        }

        return substr($standardOffer, 1);
    }

    private function isWheelchairAccessible(string $typicalOffer): bool
    {
        return str_contains(TypicalOfferType::WHEELCHAIR_ACCESSIBLE, $typicalOffer);
    }

    private function getFullname(array $member): string
    {
        $name = $member['FirstName'] . ' ' . $member['SecondName'] . ' ' . $member['LastName'];

        return str_replace('  ', ' ', $name);
    }

    private function isFirstnameShown(array $member): bool
    {
        return ($member['HideAttribute'] & self::MEMBER_FIRSTNAME_HIDDEN) !== self::MEMBER_FIRSTNAME_HIDDEN;
    }

    private function migrateRestrictions(mixed $restrictions): string
    {
        $hostRestrictions = '';
        if (str_contains($restrictions, 'NoSmoker')) {
            $hostRestrictions .= ',' . HostRestrictionsType::NO_SMOKING;
        }
        if (str_contains($restrictions, 'NoAlchool')) {
            $hostRestrictions .= ',' . HostRestrictionsType::NO_ALCOHOL;
        }
        if (str_contains($restrictions, 'NoDrugs')) {
            $hostRestrictions .= ',' . HostRestrictionsType::NO_DRUGS;
        }

        return substr($hostRestrictions, 1);
    }

    private function mapField(string $tableColumn): string
    {
        $tableColumn = str_replace('members.', '', $tableColumn);
        $tableColumn = match ($tableColumn) {
            'ProfileSummary' => 'AboutMe',
            'AdditionalAccomodationInfo' => 'AdditionalAccommodationInfo',
            'MaxLenghtOfStay' => 'MaxLengthOfStay',
            default => $tableColumn,
        };

        return $tableColumn;
    }

    private function addTranslation(int $memberId, string $locale, string $field, string $content): string
    {
        $locale = $this->connection->quote($locale);
        $field = $this->connection->quote($field);
        $content = $this->connection->quote($content);

        return ", ({$memberId}, {$locale}, {$field}, {$content})";
    }

    private function addErrorMember(array $member): void
    {
        // Organize errors based on status and username
        if (!isset($this->errorMembers[$member['Status']])) {
            $this->errorMembers[$member['Status']] = [];
        }
        if (!isset($this->errorMembers[$member['Status']][$member['Username']])) {
            $this->errorMembers[$member['Status']][$member['Username']] = [];
        }
    }

    private function addErrorCity(array $member): void
    {
        $this->addErrorMember($member);

        $this->errorMembers[$member['Status']][$member['Username']]['city'] = $member['IdCity'];
    }

    private function addErrorAddress(array $member, string $message): void
    {
        $this->addErrorMember($member);

        $this->errorMembers[$member['Status']][$member['Username']]['address'] = $message;
    }

    private function addErrorMemberSql(array $member, string $message): void
    {
        $this->addErrorMember($member);
        $this->errorMembers[$member['Status']][$member['Username']]['member_sql'] = $message;
    }

    private function addErrorTranslationSql(array $member, string $message): void
    {
        $this->addErrorMember($member);
        $this->errorMembers[$member['Status']][$member['Username']]['translation_sql'] = $message;
    }

    private function addErrorNoTranslations(array $member): void
    {
        //        $this->addErrorMember($member);
        //        $this->errorMembers[$member['Status']][$member['Username']]['no_translations'] = 'No Translations';
    }

    private function addErrorLanguage(array $member, mixed $language): void
    {
        $this->addErrorMember($member);
        $this->errorMembers[$member['Status']][$member['Username']]['language'] = $language;
    }

    private function migrateHiddenFields(array $member): int
    {
        $hideAttribute = 0;
        if (
            ($member['HideAttribute'] && self::MEMBER_FIRSTNAME_HIDDEN)
            || ($member['HideAttribute'] && self::MEMBER_SECONDNAME_HIDDEN)
            || ($member['HideAttribute'] && self::MEMBER_LASTNAME_HIDDEN)
        ) {
            $hideAttribute |= Member::NAME_HIDDEN;
        }

        if ('Yes' === $member['HideBirthDate']) {
            $hideAttribute |= Member::AGE_HIDDEN;
        }

        if ('Yes' === $member['HideGender']) {
            $hideAttribute |= Member::GENDER_HIDDEN;
        }

        if ('Yes' === $member['AdressHidden']) {
            $hideAttribute |= Member::ADDRESS_HIDDEN;
        }

        return $hideAttribute;
    }

    private function migrateGender(string $gender): string
    {
        return match ($gender) {
            'IDontTell' => 'other',
            default => $gender,
        };
    }

    private function migrateLanguageLevel(string $level): string
    {
        try {
            return match ($level) {
                'MotherLanguage' => LanguageLevelType::MOTHER_TONGUE,
                'Expert' => LanguageLevelType::EXPERT,
                'Fluent' => LanguageLevelType::FLUENT,
                'Intermediate' => LanguageLevelType::INTERMEDIATE,
                'Beginner' => LanguageLevelType::BEGINNER,
                'HelloOnly' => LanguageLevelType::HELLO_ONLY,
                default => '',
            };
        } catch (Throwable $throwable) {
            echo 'Level: ' . $level;
            exit;
        }
    }

    private function reportErrors(): void
    {
        if (!empty($this->errorMembers)) {
            $file = fopen('migrateMembers.errors.txt', 'w');
            $countOfErrorMembers = 0;
            foreach ($this->errorMembers as $errors) {
                $countOfErrorMembers += \count($errors);
            }

            $this->io->error("Error migrating {$countOfErrorMembers} members.");

            fwrite($file, 'Members with errors: ' . $countOfErrorMembers . "\n");
            foreach ($this->errorMembers as $status => $membersWithErrors) {
                $countOfErrorMembers = \count($membersWithErrors);
                fwrite($file, "{$status}: {$countOfErrorMembers}" . \PHP_EOL);
                $errorsByCategory = [];
                foreach ($membersWithErrors as $errors) {
                    foreach ($errors as $category => $error) {
                        if (!isset($errorsByCategory[$category])) {
                            $errorsByCategory[$category] = 0;
                        }
                        ++$errorsByCategory[$category];
                    }
                }
                foreach (array_keys($errorsByCategory) as $category) {
                    fwrite($file, "    {$category}: {$errorsByCategory[$category]}" . \PHP_EOL);
                }
            }
            fwrite($file, \PHP_EOL);

            foreach ($this->errorMembers as $status => $membersWithErrors) {
                foreach ($membersWithErrors as $username => $errors) {
                    $errorText = [];
                    $errorText[] = "Error migrating member: {$status} - {$username}";

                    if (\array_key_exists('city', $errors)) {
                        $errorText[] = "City not found: {$errors['city']}";
                    }
                    if (\array_key_exists('address', $errors)) {
                        $errorText[] = "Address: {$errors['address']}";
                    }
                    if (\array_key_exists('language', $errors)) {
                        $errorText[] = "Language not found: {$errors['language']}";
                    }
                    if (\array_key_exists('no_translations', $errors)) {
                        $errorText[] = 'No translations in database!';
                    }
                    if (\array_key_exists('member_sql', $errors)) {
                        $errorText[] = $errors['member_sql'];
                    }
                    if (\array_key_exists('translation_sql', $errors)) {
                        $errorText[] = $errors['translation_sql'];
                    }
                    fwrite($file, implode(\PHP_EOL, $errorText));
                    fwrite($file, \PHP_EOL);
                }
            }

            fclose($file);
        }
    }
}
