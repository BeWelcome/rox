<?php

namespace App\Command;

use App\Doctrine\HostRestrictionsType;
use App\Doctrine\StandardOffersType;
use App\Doctrine\TypicalOfferType;
use App\Entity\Language;
use App\Entity\Location;
use App\Entity\Member;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'migrate:members',
    description: 'Migrates the old profile (members and memberstrads table) to the new profile tables (member and member_translations)',
)]
class MigrateMembersCommand extends Command
{
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
                Username, 
                Password, 
                Name, 
                ShortName, 
                Status, 
                Email, 
                Gender, 
                Accommodation, 
                MaxGuests,
                HideAttribute, 
                bewelcomed, 
                BirthDate,
                GenderOfGuests,
                HostingInterest,
                Reminders,
                LastLogin,
                LastSwitchToActive,
                created,
                updated,
                RegistrationKey,
                StandardOffers,
                Restrictions,
                Occupation,
                ILiveWith,
                MaxLengthOfStay,
                Organizations,
                AdditionalAccommodationInfo,
                OtherRestrictions,
                ProfileLanguage,
                AboutMe,
                Hobbies,
                Books,
                Music,
                Movies,
                PleaseBring,
                OfferGuests,
                OfferHosts,
                PublicTransport,
                PastTrips,
                PlannedTrips                          
            ) VALUES (
                :id, 
                :Username, 
                :Password, 
                :Name, 
                :ShortName, 
                :Status, 
                :Email, 
                :Gender, 
                :Accommodation,
                :MaxGuests,
                :HideAttribute, 
                :bewelcomed, 
                :BirthDate,
                :GenderOfGuests,
                :HostingInterest,
                :Reminders,
                :LastLogin,
                :LastSwitchToActive,
                :created,
                :updated,
                :RegistrationKey,
                :StandardOffers,
                :Restrictions,
                      null, null, null, null, null,
                      null, null, null, null, null,
                      null, null, null, null, null,
                      null, null, null
            )
        SQL;

    private SymfonyStyle $io;
    private array $errorMembers = [];
    private readonly Connection $connection;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();

        $this->connection = $this->entityManager->getConnection();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->success('Migrating old profiles.');

        $sql = "SELECT COUNT(m.id) FROM members m WHERE m.status IN ('" . implode("', '", self::MIGRATED_STATUSES) . "')";
        $countOfMembers = $this->connection->executeQuery(
            "SELECT COUNT(m.id) FROM members m WHERE m.status IN ('" . implode("', '", self::MIGRATED_STATUSES) . "')"
        )->fetchOne();

        $this->io->note('Migrating ' . $countOfMembers . ' members.');

        // load members in chunks of 100
        $locationRepository = $this->entityManager->getRepository(Location::class);
        $languageRepository = $this->entityManager->getRepository(Language::class);
        $rawLanguages = $languageRepository->findAll();
        $languages = [];
        foreach ($rawLanguages as $language) {
            $languages[$language->getId()] = $language;
        }

        $this->errorMembers = [];
        /* $countOfMembers = */ $batchSize = 2000;

        $progressBar = new ProgressBar($output, $countOfMembers);
        $progressBar->setFormat(
            "<fg=white;bg=green>\n %info:-60s% \n</>\n%current%/%max% [%bar%] %percent:3s%%\n%estimated:-20s%  %memory:20s%\nLast error: %error%"
        );
        $progressBar->setMessage("Migrating {$countOfMembers} members to new member table including translations.", 'info');
        $progressBar->setMessage('none', 'error');
        $progressBar->minSecondsBetweenRedraws(10.0);
        $progressBar->start();

        // Make sure the tables member and member_translations are empty

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0; TRUNCATE `member`; TRUNCATE `member_translations`; SET FOREIGN_KEY_CHECKS=1;');

        for ($i = 0; $i < $countOfMembers; $i += $batchSize) {
            $query = $this->connection->executeQuery("SELECT * FROM members m WHERE m.status IN ('" . implode("', '", self::MIGRATED_STATUSES) . "') ORDER BY ID LIMIT " . $i . ",$batchSize");
            $members = $query->fetchAllAssociative();

            foreach ($members as $member) {
                // First non translated elements
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
                $statement->bindValue('Gender', $member['Gender']);
                $statement->bindValue('Accommodation', $member['Accomodation']);
                $statement->bindValue('MaxGuests', $member['MaxGuest']);
                $statement->bindValue('HideAttribute', $this->migrateHiddenFields($member));
                $statement->bindValue('bewelcomed', $member['bewelcomed']);
                if ('0000-00-00' !== $member['BirthDate']) {
                    $statement->bindValue('BirthDate', new DateTime($member['BirthDate']), 'datetime');
                } else {
                    $statement->bindValue('BirthDate', new DateTime('1970-01-01 00:00:00'), 'datetime');
                }
                $statement->bindValue('GenderOfGuests', $member['GenderOfGuest']);
                $statement->bindValue('HostingInterest', $member['hosting_interest']);
                $statement->bindValue('Reminders', $member['NbRemindWithoutLogingIn']);
                if (null !== $member['LastLogin'] && '0000-00-00 00:00:00' !== $member['LastLogin']) {
                    $statement->bindValue('LastLogin', new DateTime($member['LastLogin']), 'datetime');
                } else {
                    $statement->bindValue('LastLogin', null);
                }
                if (null !== $member['LastSwitchToActive']) {
                    $statement->bindValue('LastSwitchToActive', new DateTime($member['LastSwitchToActive']), 'datetime');
                } else {
                    $statement->bindValue('LastSwitchToActive', null);
                }

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
                $statement->bindValue('StandardOffers', $this->migrateTypicalOffer($member['TypicOffer']));
                $statement->bindValue('Restrictions', $this->migrateRestrictions($member['Restrictions']));

                try {
                    $statement->executeStatement();
                } catch (Exception $e) {
                    $progressBar->setMessage($member['Username'], 'error');
                    $this->addErrorMemberSql($member, $e->getMessage());
                }

                // Migrate address
                $city = $locationRepository->findOneBy(['geonameId' => $member['IdCity']]);
                if (null === $city) {
                    if ('AskToLeave' !== $member['Status'] && 'TakenOut' !== $member['Status']) {
                        $this->addErrorCity($member);
                        $progressBar->setMessage($member['Username'], 'error');
                    }
                    $statement->bindValue('City', null);
                } else {
                    $statement->bindValue('City', $city->getGeonameid());
                }

                // Now handle translated fields
                $memberLocales = [];

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
                    $memberTranslations = $this->connection->executeQuery('SELECT * FROM memberstrads m WHERE IdTrad IN (' . implode(',', $translationIds) . ') ORDER BY IdTrad, id desc, IdLanguage')->fetchAllAssociative();
                    foreach ($memberTranslations as $memberTranslation) {
                        $language = $languages[$memberTranslation['IdLanguage']] ?? null;
                        if (null === $language) {
                            $this->addErrorLanguage($member, $memberTranslation['IdLanguage']);
                            $progressBar->setMessage($member['Username'], 'error');
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

                    $sql = 'INSERT INTO member_translations (object_id, Locale, Field, Content) VALUES ' . substr($queryString, 2);

                    try {
                        $this->connection->executeStatement($sql);
                    } catch (Exception $e) {
                        $progressBar->setMessage($member['Username'], 'error');
                        $this->addErrorTranslationSql($member, $e->getMessage());
                    }
                }
                $progressBar->advance();
            }
            unset($members);
        }

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

        $this->io->success('Done migrating old profiles.');

        return Command::SUCCESS;
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
        if (str_contains(TypicalOfferType::WHEELCHAIR_ACCESSIBLE, $typicalOffer)) {
            $standardOffer .= ',' . StandardOffersType::WHEELCHAIR_ACCESSIBLE;
        }

        return substr($standardOffer, 1);
    }

    private function getFullname(array $member): string
    {
        $name = $member['FirstName'] . ' ' . $member['SecondName'] . ' ' . $member['LastName'];

        return str_replace('  ', ' ', $name);
    }

    private function isFirstnameShown(array $member): bool
    {
        return ($member['HideAttribute'] & Member::MEMBER_FIRSTNAME_HIDDEN) !== Member::MEMBER_FIRSTNAME_HIDDEN;
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

    private function addErrorMemberSql(array $member, string $message): void
    {
        $this->addErrorMember($member);
        $this->errorMembers[$member['Status']][$member['Username']]['member_sql'] = $message;
    }

    private function addErrorTranslationSql(array $member, string $message): void
    {
        $this->addErrorMember($member);
        $this->errorMembers[$member['Status']][$member['Username']]['translation_sql'] = $message();
    }

    private function addErrorNoTranslations(array $member): void
    {
        $this->addErrorMember($member);
        $this->errorMembers[$member['Status']][$member['Username']]['no_translations'] = 'No Translations';
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
            ($member['HideAttribute'] && Member::MEMBER_FIRSTNAME_HIDDEN)
            || ($member['HideAttribute'] && Member::MEMBER_SECONDNAME_HIDDEN)
            || ($member['HideAttribute'] && Member::MEMBER_LASTNAME_HIDDEN)
        ) {
            $hideAttribute = Member::NAME_HIDDEN;
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
}
