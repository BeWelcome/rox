<?php

namespace App\Command;

use App\Entity\Country;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
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
    private const ROWS_IN_A_BATCH = 10000;

    private HttpClientInterface $httpClient;
    private EntityManagerInterface $entityManager;
    private OutputInterface $output;
    private array $allowedLocales;

    public function __construct(
        HttpClientInterface $httpClient,
        EntityManagerInterface $entityManager,
        array $locales
    ) {
        parent::__construct('geonames:update');

        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;

        // turn zh_hant into zh-TW, zh_hans into zh-CN
        $locales = array_replace($locales, ['zh_hant' => 'zh-TW', 'zh_hans' => 'zh-CN']);

        $this->allowedLocales = $locales;
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
                '(except --continue-on-errors, --country, and --update). Does download the files if not explicitly forbidden.'
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
            ->addOption(
                'update',
                null,
                InputOption::VALUE_NONE,
                'Update all rows instead of inserting new ones. If used with \'full\' will not truncate the tables.'
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
        $adminUnits = $input->getOption('admin-units');

        // Assumption is that the tables exist (thanks to doctrine create:schema).
        if ($input->getOption('full')) {
            $geonames = true;
            $alternateNames = true;
            $adminUnits = true;
        }
        $update = $input->getOption('update');

        $this->output = $output;

        if ($geonames) {
            $returnCode = $this->updateGeonames($io, $downloadFiles);
            if (0 !== $returnCode && !$continueOnErrors) {
                return $returnCode;
            }
        }

        if ($adminUnits) {
            $returnCode = $this->updateAdmin1Units($io, $downloadFiles);
            if (0 !== $returnCode && !$continueOnErrors) {
                return $returnCode;
            }

            $returnCode = $this->updateAdmin2Units($io, $downloadFiles);
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

        $lines = $this->getLines($dir . '/allCountries.txt');

        $progressBar = new ProgressBar($this->output, $lines);
        $progressBar->setFormat(
            "<fg=white;bg=cyan> %status:-45s%</>\n%current%/%max% [%bar%] %percent:3s%%\n?  %estimated:-20s%  %memory:20s%"
        );
        $progressBar->setRedrawFrequency(10000);
        $progressBar->start();

        $handle = fopen($dir . '/allCountries.txt', 'r');
        $rows = [];

        $progressBar->setMessage('Loading data...', 'status');
        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            if (is_numeric($row[0]) && ('A' === $row[6] || 'P' === $row[6])) {
                $rows[] = $row;

                if (self::ROWS_IN_A_BATCH === \count($rows)) {
                    $this->updateGeonamesInDatabase($io, $rows, $progressBar);

                    unset($rows);
                    $rows = [];

                    gc_collect_cycles();
                    $progressBar->setMessage('Loading data...', 'status');
                }
            }
            $progressBar->advance();
        }

        // Also write the remaining entries to the database
        if (!empty($rows)) {
            $this->updateGeonamesInDatabase($io, $rows, $progressBar);
            unset($rows);
        }

        fclose($handle);

        $progressBar->finish();

        $em = $this->entityManager;
        $connection = $em->getConnection();

        // Set the countryId of database entries (except for historical countries)
        $connection->executeQuery("
            UPDATE geo__names AS g, (
                SELECT geonameid,country_id FROM geo__names WHERE feature_class = 'A' AND feature_code LIKE 'PCL%' AND feature_code <> 'PCLH') AS c
            SET g.country = c.geonameid WHERE g.country_id = c.country_id;
        ");

        $filesystem = new Filesystem();
        $filesystem->remove([
            $dir . '/allCountries.txt',
            $dir,
        ]);

        $io->success('Updated the geonames databases to current state.');

        return 0;
    }

    /**
     * Import alternate names into geonamesalternatenames.
     *
     * Then run query to fill up the geo__names_translations table filtering for short and preferred.
     *
     * So that in the end Germany shows up as translation for the Federal Republic of Germany.
     */
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

        $io->writeln('Getting number of rows to import');

        $lines = $this->getLines($dir . '/alternateNamesV2.txt');

        $io->newLine();

        $progressBar = new ProgressBar($this->output, $lines);
        $progressBar->setFormat(
            "<fg=white;bg=cyan> %status:-45s%</>\n%current%/%max% [%bar%] %percent:3s%%\n?  %estimated:-20s%  %memory:20s%"
        );
        $progressBar->setRedrawFrequency(10000);
        $progressBar->setMessage('Reading geoname ids...', 'status');
        $progressBar->start();

        $query = $this->entityManager->createQuery('SELECT l.geonameId FROM App\Entity\NewLocation l');
        $geonameIds = $query->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);
        $geonameIds = array_flip($geonameIds);

        $handle = fopen($dir . '/alternateNamesV2.txt', 'r');
        $rows = [];

        $progressBar->setMessage('Loading data...', 'status');
        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            if (
                is_numeric($row[0])
                && isset($geonameIds[$row[0]])
//                && in_array(strtolower($row[2]), $this->allowedLocales)
            ) {
                $rows[] = $row;

                if (self::ROWS_IN_A_BATCH === \count($rows)) {
                    $this->updateAlternatenamesInDatabase($io, $rows, $progressBar);

                    unset($rows);
                    $rows = [];

                    gc_collect_cycles();
                    $progressBar->setMessage('Loading data...', 'status');
                }
            }
            $progressBar->advance();
        }

        // Also write the remaining entries to the database
        if (!empty($rows)) {
            $this->updateAlternatenamesInDatabase($io, $rows, $progressBar);
            unset($rows);
        }
        fclose($handle);

        $connection = $this->entityManager->getConnection();
        $io->note('Setting translations (preferred, short)');

        $connection->executeQuery("
            INSERT INTO geo__names_translations (locale, object_class, field, foreign_key, content)
	            SELECT isolanguage as locale, 'App\\\\Entity\\\\NewLocation', 'name', geonameid, alternatename
	                FROM geonamesalternatenames
	                WHERE ispreferred = 1 AND isshort = 1 AND ishistoric = 0 AND isolanguage <> '' and length(isolanguage) <> 4;
        ");

        $io->note('Setting translations (preferred)');
        $connection->executeQuery("
            INSERT INTO geo__names_translations (locale, object_class, field, foreign_key, content)
	            SELECT isolanguage as locale, 'App\\\\Entity\\\\NewLocation', 'name', geonameid, alternatename
	                FROM geonamesalternatenames
	                WHERE ispreferred = 1 AND isshort = 0 AND ishistoric = 0 AND isolanguage <> '' and length(isolanguage) <> 4;
        ");

        $io->note('Setting translations (short)');
        $connection->executeQuery("
            INSERT INTO geo__names_translations (locale, object_class, field, foreign_key, content)
	            SELECT isolanguage as locale, 'App\\\\Entity\\\\NewLocation', 'name', geonameid, alternatename
	                FROM geonamesalternatenames
	                WHERE ispreferred = 0 AND isshort = 1 AND ishistoric = 0 AND isolanguage <> '' and length(isolanguage) <> 4;
        ");

        $io->note('Setting translations (any)');
        $connection->executeQuery("
            INSERT INTO geo__names_translations (locale, object_class, field, foreign_key, content)
	            SELECT isolanguage as locale, 'App\\\\Entity\\\\NewLocation', 'name', geonameid, alternatename
	                FROM geonamesalternatenames
	                WHERE ispreferred = 0 AND isshort = 0 AND ishistoric = 0 AND isolanguage <> '' and length(isolanguage) <> 4;
        ");

        $progressBar->finish();

        $io->note('Removing temporary files');

        $filesystem = new Filesystem();
        $filesystem->remove([
            $dir . '/alternateNamesV2.txt',
            $dir . '/isoLanguages.txt',
            $dir,
        ]);

        $io->success('Updated the alternate names database to current state.');

        return 0;
    }

    private function updateAdmin1Units(SymfonyStyle $io, bool $download)
    {
        $io->note('Setting admin units');

        $filename = $this->getFile(
            $io,
            'admin1CodesASCII.txt',
            $download
        );

        if (null === $filename) {
            return -1;
        }

        $lines = $this->getLines($filename);

        $progressBar = new ProgressBar($this->output, $lines);
        $progressBar->setFormat(
            "<fg=white;bg=cyan> %status:-45s%</>\n%current%/%max% [%bar%] %percent:3s%%\n?  %estimated:-20s%  %memory:20s%"
        );
        $progressBar->setRedrawFrequency(10000);
        $progressBar->start();

        $handle = fopen($filename, 'r');

        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            $progressBar->advance();
            if ('#' !== $row[0][0]) {
                $progressBar->setMessage('Executing query', 'status');
                // Split admin unit into country and identifier
                $countryAndAdmin1 = explode('.', $row[0]);
                $country = $countryAndAdmin1[0];
                $admin1 = $countryAndAdmin1[1];
                // Check if admin unit already exists if so update.
                $connection = $this->entityManager->getConnection();
                $connection->executeQuery(
                    'UPDATE geo__names SET admin1 = :geonameid WHERE country_id = :country AND admin_1_id = :admin1',
                    [
                        ':geonameid' => $row[3],
                        ':country' => $country,
                        ':admin1' => $admin1,
                    ],
                    ['int', 'string', 'string'],
                );
                $progressBar->setMessage('finished', 'status');
            }
        }
        $progressBar->finish();

        return 0;
    }

    private function updateAdmin2Units(SymfonyStyle $io, bool $download)
    {
        $io->note('Setting admin units (second level)');

        $filename = $this->getFile(
            $io,
            'admin2Codes.txt',
            $download
        );

        if (null === $filename) {
            return -1;
        }

        $lines = $this->getLines($filename);

        $progressBar = new ProgressBar($this->output, $lines);
        $progressBar->setFormat(
            "<fg=white;bg=cyan> %status:-45s%</>\n%current%/%max% [%bar%] %percent:3s%%\n?  %estimated:-20s%  %memory:20s%"
        );
        $progressBar->setRedrawFrequency(1000);
        $progressBar->start();

        $handle = fopen($filename, 'r');

        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            $progressBar->advance();
            if ('#' !== $row[0][0]) {
                $progressBar->setMessage('Executing query', 'status');
                // Split admin unit into country and identifier
                $countryAndAdmin1AndAdmin2 = explode('.', $row[0]);
                $country = $countryAndAdmin1AndAdmin2[0];
                $admin1 = $countryAndAdmin1AndAdmin2[1];
                $admin2 = $countryAndAdmin1AndAdmin2[2];
                // Check if admin unit already exists if so update.
                $connection = $this->entityManager->getConnection();
                $connection->executeQuery(
                    'UPDATE geo__names SET admin2 = :geonameid WHERE country_id = :country AND admin_1_id = :admin1 AND admin_2_id = :admin2',
                    [
                        ':geonameid' => $row[3],
                        ':country' => $country,
                        ':admin1' => $admin1,
                        ':admin2' => $admin2,
                    ],
                    ['int', 'string', 'string', 'string'],
                );
                $progressBar->setMessage('finished', 'status');
            }
        }
        $progressBar->finish();

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

    private function updateGeonamesInDatabase(SymfonyStyle $io, array $rows, ProgressBar $progressbar): void
    {
        /*          geonameid         :  0 - integer id of record in geonames database
                    name              :  1 - name of geographical point (utf8) varchar(200)
                    asciiname         :  2 - ignored name of geographical point in plain ascii characters, varchar(200)
                    alternatenames    :  3 - ignored
                    latitude          :  4 - latitude in decimal degrees (wgs84)
                    longitude         :  5 - longitude in decimal degrees (wgs84)
                    feature class     :  6 - see http://www.geonames.org/export/codes.html, char(1)
                    feature code      :  7 - see http://www.geonames.org/export/codes.html, varchar(10)
                    country code      :  8 - ISO-3166 2-letter country code, 2 characters
                    cc2               :  9 - ignored
                    admin1 code       : 10 - code for first level administrative division, varchar(20)
                    admin2 code       : 11 - code for second level administrative division, varchar(20)
                    admin3 code       : 12 - code for third level administrative division, varchar(20)
                    admin4 code       : 13 - code for fourth level administrative division, varchar(20)
                    population        : 14 - bigint (8 byte int)
                    elevation         : 15 - ignored in meters, integer
                    dem               : 16 - ignored
                    timezone          : 17 - ignored, the iana timezone id (see file timeZone.txt) varchar(40)
                    modification date : 18 -  date of last modification in yyyy-MM-dd format
        */
        $em = $this->entityManager;
        $connection = $em->getConnection();
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        // Build the query from scratch
        $query =
            'INSERT INTO geo__names (`geonameId`, `name`, `latitude`, `longitude`, `feature_class`, `feature_code`,'
            . '`country_id`, `admin_1_id`, `admin_2_id`, `admin_3_id`, `admin_4_id`, `population`, `moddate`) '
            . 'VALUES '
        ;
        foreach ($rows as $row) {
            if ('A' !== $row[6] && 'P' !== $row[6]) {
                continue;
            }

            try {
                $query .= sprintf(
                    '(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s), ',
                    $connection->quote($row[0]),
                    $connection->quote($row[1]),
                    $connection->quote($row[4]),
                    $connection->quote($row[5]),
                    $connection->quote($row[6]),
                    $connection->quote($row[7]),
                    $connection->quote($row[8]),
                    $connection->quote($row[10]),
                    $connection->quote($row[11]),
                    $connection->quote($row[12]),
                    $connection->quote($row[13]),
                    $connection->quote($row[14]),
                    $connection->quote($row[18])
                );
            } catch (Exception $e) {
                $io->note(
                    'Skipped ' . $row[1] . ' (' . $row[8] . ', ' . $row[10] . ' - ' . $row[0]
                    . ') -- ' . $e->getMessage()
                );
            }
        }
        $query = substr($query, 0, -2);
        $query .= " ON DUPLICATE KEY UPDATE";
        $progressbar->setMessage('Executing query...', 'status');
        $connection->executeQuery($query);
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
    }

    private function updateAlternatenamesInDatabase(SymfonyStyle $io, array $rows, ProgressBar $progressbar): void
    {
        /*          alternateNameId   : 0 - the id of this alternate name, int
                    geonameid         : 1 - geonameId referring to id in table 'geoname', int
                    isolanguage       : 2 - iso 639 language code 2- or 3-characters; 4-characters 'post' for postal codes and 'iata','icao' and faac for airport codes, fr_1793 for French Revolution names,  abbr for abbreviation, link to a website (mostly to wikipedia), wkdt for the wikidataid, varchar(7)
                    alternate name    : 3 - alternate name or name variant, varchar(400)
                    isPreferredName   : 4 - '1', if this alternate name is an official/preferred name
                    isShortName       : 5 - '1', if this is a short name like 'California' for 'State of California'
                    isColloquial      : 6 - '1', if this alternate name is a colloquial or slang term. Example: 'Big Apple' for 'New York'.
                    isHistoric        : 7 - '1', if this alternate name is historic and was used in the past. Example 'Bombay' for 'Mumbai'.
                    from              : 8 - ignored - from period when the name was used
                    to                : 9 - ignored - to period when the name was used
        */
        $em = $this->entityManager;
        $connection = $em->getConnection();
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        // Build the query from scratch

        $query = 'INSERT IGNORE INTO geonamesalternatenames (`alternatenameId`, `geonameId`, `isolanguage`, `alternatename`, `ispreferred`, `isshort`, `iscolloquial`, `ishistoric`)
 '
            . 'VALUES ';
        foreach ($rows as $row) {
            try {
                // use Rox locales for the chinese scripts
                // always use lower case for locale
                switch ($row[2]) {
                    case 'zh-TW':
                        $row[2] = 'zh-hant';
                        break;
                    case 'zh-CN':
                        $row[2] = 'zh-hans';
                        break;
                }

                $query .= sprintf(
                    '(%s, %s, %s, %s, %s, %s, %s, %s), ',
                    $connection->quote($row[0]),
                    $connection->quote($row[1]),
                    $connection->quote($row[2]),
                    $connection->quote($row[3]),
                    $connection->quote($row[4]),
                    $connection->quote($row[5]),
                    $connection->quote($row[6]),
                    $connection->quote($row[7])
                );
            } catch (Exception $e) {
                $io->note(
                    'Skipped ' . $row[1] . ' (' . $row[8] . ', ' . $row[10] . ' - ' . $row[0] . ') -- ' . $e->getMessage()
                );
            }
        }
        $query = substr($query, 0, -2);

        $progressbar->setMessage('Executing query...', 'status');
        $connection->executeQuery($query);
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
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
}
