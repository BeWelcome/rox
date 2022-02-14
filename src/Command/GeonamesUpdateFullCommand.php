<?php

namespace App\Command;

use App\Entity\AdminCode;
use App\Entity\AdminUnit;
use App\Entity\AlternateLocation;
use App\Entity\Country;
use App\Entity\Location;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ZipArchive;

/**
 * @SuppressWarnings(PHPMD)
 */
class GeonamesUpdateFullCommand extends Command
{
    private HttpClientInterface $httpClient;
    private EntityManagerInterface $entityManager;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $entityManager)
    {
        parent::__construct('geonames:update');

        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Downloads geonames data dump and imports them')
            ->addOption(
                'full',
                null,
                InputOption::VALUE_NONE,
                'Fetches all data; truncates the database tables and imports the data. Sets all options ' .
                '(except --continue-on-errors and --country). Does download the files if not explicitly forbidden.'
            )
            ->addOption(
                'countries',
                null,
                InputOption::VALUE_NONE,
                'Fetch country info and import.'
            )
            ->addOption(
                'admin-units',
                null,
                InputOption::VALUE_NONE,
                'Fetch admin unit info and import.'
            )
            ->addOption(
                'geonames',
                null,
                InputOption::VALUE_NONE,
                ''
            )
            ->addOption(
                'alternate',
                null,
                InputOption::VALUE_NONE,
                'Downloads alternatenames data dump and imports them'
            )
            ->addOption(
                'country',
                null,
                InputOption::VALUE_REQUIRED,
                'Imports only the entries for the given country (2 letter code like US, GB, IN, ...).' . PHP_EOL .
                'Can be combined with --full.'
            )
            ->addOption(
                'download',
                null,
                InputOption::VALUE_NONE,
                'If not set the command assumes the files have already been downloaded and will access them.'
            )
            ->addOption(
                'continue-on-errors',
                null,
                InputOption::VALUE_NONE,
                'Continue importing the alternate names even if an error happened while importing geonames.'
            )
            ->setHelp('Downloads geonames or alternatenames data dump and imports them')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        gc_disable();

        $io = new SymfonyStyle($input, $output);

        $returnCode = 0;

        $downloadFiles = ($input->getOption('download'));

        $continueOnErrors = $input->getOption('continue-on-errors');
        $geonames = $input->getOption('geonames');
        $alternateNames = $input->getOption('alternate');
        $countries = $input->getOption('countries');
        $adminUnits = $input->getOption('admin-units');

        if ($input->getOption('full')) {
            $geonames = true;
            $alternateNames = true;
            $countries = true;
            $adminUnits = true;

            $this->truncateTables();
        }

        if ($countries) {
            $returnCode = $this->updateCountries($io, $downloadFiles);
            if (0 !== $returnCode && !$continueOnErrors) {
                return $returnCode;
            }
        }

        if ($adminUnits) {
            $returnCode = $this->updateAdminUnits($io, $downloadFiles);
            if (0 !== $returnCode && !$continueOnErrors) {
                return $returnCode;
            }
        }

        if ($geonames) {
            $returnCode = $this->updateGeonames($io, $downloadFiles);
            if (0 !== $returnCode && !$continueOnErrors) {
                return $returnCode;
            }
        }

        if ($alternateNames) {
            $returnCode = $this->updateAlternatenames($io, $downloadFiles);
            if (0 !== $returnCode && !$continueOnErrors) {
                return $returnCode;
            }
        }

        gc_enable();

        return $returnCode;
    }

    protected function updateCountries(SymfonyStyle $io, bool $download): int
    {
        $io->note('Updating the countries list');

        $filename = $this->getFile(
            $io,
            'countryInfo.txt',
            $download
        );

        if (null === $filename) {
            return -1;
        }

        /** @var ObjectRepository $countryRepository */
        $countryRepository = $this->entityManager->getRepository(Country::class);

        $lines = $this->getLines($filename);

        $progressbar = $io->createProgressBar();

        $progressbar->start($lines);

        $handle = fopen($filename, 'r');

        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            $progressbar->advance();
            if ($row[0][0] != '#' && !empty($row[4])) {
                // Check if country already exists, if so update
                $country = $countryRepository->find(['country' => $row[0]]);
                if (null === $country) {
                    $country = new Country();
                }

                $country->setCountry($row[0]);
                $country->setName($row[4]);
                $continent = $row[8];
                if ('EU' == $continent || 'AS' == $continent) {
                    // Use Eurasia instead of Europe and Asia
                    $continent = 'EA';
                }
                $country->setContinent($continent);
                $country->setGeonameId($row[16]);
                $this->entityManager->persist($country);
            }
        }
        $this->entityManager->flush();
        $this->entityManager->clear();

        return 0;
    }

    private function updateAdminUnits(SymfonyStyle $io, bool $download)
    {
        $io->note('Updating the admin units');

        $filename = $this->getFile(
            $io,
            'admin1CodesASCII.txt',
            $download
        );

        if (null === $filename) {
            return -1;
        }

        $adminCodeRepository = $this->entityManager->getRepository(AdminCode::class);

        $lines = $this->getLines($filename);

        $progressbar = $io->createProgressBar();

        $progressbar->start($lines);

        $handle = fopen($filename, 'r');

        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            $progressbar->advance();
            if ($row[0][0] != '#') {
                // Split admin unit into country and identifier
                $countryAndAdmin1 = explode('.', $row[0]);
                $country = $countryAndAdmin1[0];
                $admin1 = $countryAndAdmin1[1];
                    // Check if admin unit already exists if so update.
                $adminUnit = $adminCodeRepository->findOneBy([
                    'country' => $country,
                    'admin1' => $admin1
                ]);
                if (null === $adminUnit) {
                    $adminUnit = new AdminCode();
                    $adminUnit->setCountry($country);
                    $adminUnit->setAdmin1($admin1);
                }
                $adminUnit->setName($row[1]);
                $adminUnit->setGeonameId($row[3]);
                $this->entityManager->persist($adminUnit);
            }
        }
        $this->entityManager->flush();
        $this->entityManager->clear();

        return 0;
    }

    protected function updateGeonames(SymfonyStyle $io, bool $download): int
    {
        $io->note('Updating the geonames database');

        $filename = $this->getFile(
            $io,
            'allCountries.zip',
            $download
        );

        if (null === $filename) {
            return -1;
        }

        $zip = new ZipArchive();
        $dir = sys_get_temp_dir() . '/allcountries';
        if (true === $zip->open($filename)) {
            $zip->extractTo($dir);
            $zip->close();
        } else {
            $io->error('Couldn\'t extract geoname database extract.');

            return -1;
        }

        $query = $this->entityManager
            ->getRepository(Country::class)
            ->createQueryBuilder('c')
            ->indexBy('c', 'c.country')
            ->getQuery();
        $countries = $query->getResult();

        $lines = $this->getLines($dir . '/allCountries.txt');

        $progressbar = $io->createProgressBar();
        $progressbar->start($lines);

        $handle = fopen($dir . '/allCountries.txt', 'r');
        $rows = [];

        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            if (is_numeric($row[0]) && ('A' === $row[6] || 'P' === $row[6])) {
                $rows[] = $row;

                if (5000 === \count($rows)) {
                    $this->updateGeonamesInDatabase($io, $rows, $countries);

                    unset($rows);
                    $rows = [];

                    gc_collect_cycles();
                }
            }
            $progressbar->advance();
        }

        // Also write the remaining entries to the database
        $this->updateGeonamesInDatabase($io, $rows, $countries);

        fclose($handle);

        $progressbar->finish();

        $filesystem = new Filesystem();
        $filesystem->remove([
            $dir . '/allCountries.txt',
            $dir,
        ]);

        $io->success('Updated the geonames databases to current state.');

        return 0;
    }

    protected function updateAlternatenames(SymfonyStyle $io, bool $download): int
    {
        $io->title('Updating the alternate names database.');

        $filename = $this->getFile(
            $io,
            'alternateNamesV2.zip',
            $download
        );

        if (null === $filename) {
            return -1;
        }

        $io->writeln('Extracting downloaded file.');

        $zip = new ZipArchive();
        $dir = sys_get_temp_dir() . '/alternatenames';
        if (true === $zip->open($filename)) {
            $zip->extractTo($dir);
            $zip->close();
        } else {
            $io->error('Couldn\'t extract geoname alternate name database.');

            return -1;
        }

        $io->writeln('Import alternate names into database');

        $statement = $this->connection->executeQuery(
            'SELECT geonameId from geonames'
        );

        $geonameIds = [];

        while (false !== ($geonameId = $statement->fetchOne())) {
            $geonameIds[] = $geonameId;
        }

        $io->writeln('Getting number of rows to import');

        $lines = $this->getLines($dir . '/alternateNamesV2.txt');

        $io->newLine();

        $progressbar = $io->createProgressBar();
        $progressbar->minSecondsBetweenRedraws(1);
        $progressbar->start($lines);

        $i = 0;
        $handle = fopen($dir . '/alternateNamesV2.txt', 'r');
        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            if (\in_array($row[0], $geonameIds, true)) {
                $this->updateAlternatenamesRow($row);
            }
            $progressbar->advance();

            ++$i;
            if (0 === ($i % 100000)) {
                gc_collect_cycles();
            }
        }

        fclose($handle);

        $progressbar->finish();

        $io->writeln('Removing temporary files');

        $filesystem = new Filesystem();
        $filesystem->remove([
            $dir . '/alternateNamesV2.txt',
            $dir . '/isoLanguages.txt',
            $dir,
        ]);

        $io->success('Updated the alternate names database to current state.');

        return 0;
    }

    private function getFile(SymfonyStyle $io, string $filename, bool $download): ?string
    {
        $localFilename = getcwd() . '/' . $filename;

        if (!$download && file_exists($localFilename)) {
            $io->note('File already exists and no download was requested.');

            return $localFilename;
        }

        $progressbar = null;

        $response = $this->httpClient->request('GET', 'https://download.geonames.org/export/dump/' . $filename, [
            'on_progress' => function (int $dlNow, int $dlSize, array $info) use ($io, &$progressbar): void {
                // $dlNow is the number of bytes downloaded so far
                // $dlSize is the total size to be downloaded or -1 if it is unknown
                // $info is what $response->getInfo() would return at this very time
                if ($dlSize > 0 && null === $progressbar) {
                    $progressbar = $io->createProgressBar($dlSize);
                    $progressbar->start();
                }

                if (null !== $progressbar) {
                    if ($dlSize === $dlNow) {
                        $progressbar->finish();

                        return;
                    }
                    $progressbar->setProgress($dlNow);
                }
            },
        ]);

        if (200 !== $response->getStatusCode()) {
            $io->error('Couldn\'t download requested file ' . $filename . ' from genames.org');

            return null;
        }

        $fileHandler = fopen($localFilename, 'w');
        foreach ($this->httpClient->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }
        fclose($fileHandler);

        return $localFilename;
    }

    private function updateGeonamesInDatabase(SymfonyStyle $io, array $rows, array $countries): int
    {
        /*          geonameid         : integer id of record in geonames database
                    name              : name of geographical point (utf8) varchar(200)
                    asciiname         : name of geographical point in plain ascii characters, varchar(200)
                    alternatenames    : ignored
                    latitude          : latitude in decimal degrees (wgs84)
                    longitude         : longitude in decimal degrees (wgs84)
                    feature class     : see http://www.geonames.org/export/codes.html, char(1)
                    feature code      : see http://www.geonames.org/export/codes.html, varchar(10)
                    country code      : ISO-3166 2-letter country code, 2 characters
                    cc2               : ignored
                    admin1 code       : ignored
                    admin2 code       : ignored
                    admin3 code       : code for third level administrative division, varchar(20)
                    admin4 code       : code for fourth level administrative division, varchar(20)
                    population        : bigint (8 byte int)
                    elevation         : in meters, integer
                    dem               : ignored
                    timezone          : the iana timezone id (see file timeZone.txt) varchar(40)
                    modification date : date of last modification in yyyy-MM-dd format
        */
        $em = $this->entityManager;
        $connection = $em->getConnection();
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        $queries = [];
        $lastCountry = '';
        $adminCodes = [];
        foreach ($rows as $row) {
            if ($row[6] != 'A' && $row[6] != 'P') continue;

            try {
                $country = $countries[$row[8]] ?? null;
                if (null === $country) {
                    $io->note('Skipped ' . $row[1] . ' (' . $row[8] . ', ' . $row[10] . ' - ' . $row[0] . ') -- No country found');
                    continue;
                }
                if ($lastCountry != $country->getCountry()) {
                    $lastCountry = $country->getCountry();
                    $adminUnitsQuery = $this->entityManager
                        ->getRepository(AdminCode::class)
                        ->createQueryBuilder('a')
                        ->indexBy('a', 'a.admin1')
                        ->where('a.country = :country')
                        ->setParameter(':country', $lastCountry)
                        ->getQuery();
                    $adminCodes = $adminUnitsQuery->getResult();
                }
                $adminCode = $adminCodes[$row[10]] ?? null;
                if (null === $adminCode) {
                    $admin1 = 'null';
                } else {
                    $admin1 = $adminCode->getGeonameId();
                }
                $queries[] = sprintf(
                    'INSERT IGNORE INTO geonames (geonameId, name, country, admin1, latitude, longitude, fClass, fCode, moddate) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)',
                    $connection->quote($row[0]),
                    $connection->quote($row[1]),
                    $country->getGeonameId(),
                    $admin1,
                    $connection->quote($row[4]),
                    $connection->quote($row[5]),
                    $connection->quote($row[6]),
                    $connection->quote($row[7]),
                    $connection->quote($row[18])
                );
            }
            catch(Exception $e)
            {
                $io->note('Skipped ' . $row[1] . ' (' . $row[8] . ', ' . $row[10] . ' - ' . $row[0] . ') -- ' . $e->getMessage());
            }
        }
        $connection->executeQuery(implode("; ", $queries));
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
        return 0;
    }

    private function updateAlternatenamesRow(array $row): void
    {
        /*          alternateNameId   : the id of this alternate name, int
                    geonameid         : geonameId referring to id in table 'geoname', int
                    isolanguage       : iso 639 language code 2- or 3-characters (ignored otherwise)
                    alternate name    : alternate name or name variant, varchar(400)
                    isPreferredName   : '1', if this alternate name is an official/preferred name
                    isShortName       : '1', if this is a short name like 'California' for 'State of California'
                    isColloquial      : skipped if '1'
                    isHistoric        : skipped if '1'
                    from              : ignored
                    to                : ignored
        */

        if (is_numeric($row[0]) && \strlen($row[2]) <= 3 && '1' !== $row[6] && '1' !== $row[7]) {
            try {
                $statement = $this->connection->executeQuery(
                    '
                    REPLACE INTO
                        `geonamesalternatenames`
                    SET
                        alternateNameId = :alternateNameId,
                        geonameId = :geonameId,
                        isolanguage = :isoLanguage,
                        alternateName = :alternateName,
                        ispreferred = :isPreferred,
                        isshort = :isShort,
                        iscolloquial = :isColloquial,
                        isHistoric = :isHistoric
                ',
                    [
                        'alternateNameId' => $row[0],
                        'geonameId' => $row[1],
                        'isoLanguage' => $row[2],
                        'alternateName' => $row[3],
                        'isPreferred' => $row[4],
                        'isShort' => $row[5],
                        'isColloquial' => $row[6],
                        'isHistoric' => $row[7],
                    ]
                );
            } catch (Exception $e) {
                // do nothing likely a foreign key constraint.
            } finally {
                $statement = null;
                unset($statement);
            }

            // Check if statement was executed successfully
        }
    }

    private function getLines($file): int
    {
        $f = fopen($file, 'r');
        $lines = 0;

        while (!feof($f)) {
            $lines += substr_count(fread($f, 8192), "\n");
        }

        fclose($f);

        return $lines;
    }

    private function truncateTables(): void
    {
        $connection = $this->entityManager->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');

        $classMetadata = $this->entityManager->getClassMetadata(Country::class);
        $q = $dbPlatform->getTruncateTableSql($classMetadata->getTableName());
        $connection->executeQuery($q);

        $classMetadata = $this->entityManager->getClassMetadata(AdminCode::class);
        $q = $dbPlatform->getTruncateTableSql($classMetadata->getTableName());
        $connection->executeQuery($q);

        $classMetadata = $this->entityManager->getClassMetadata(AlternateLocation::class);
        $q = $dbPlatform->getTruncateTableSql($classMetadata->getTableName());
        $connection->executeQuery($q);

        $classMetadata = $this->entityManager->getClassMetadata(Location::class);
        $q = $dbPlatform->getTruncateTableSql($classMetadata->getTableName());
        $connection->executeQuery($q);

        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
    }
}
