<?php

namespace App\Command;

use App\Entity\Location;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use Symfony\Component\Console\Command\Command;
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
    private Connection $connection;
    private ObjectRepository $repository;
    private EntityManagerInterface $entityManager;
    private OutputInterface $output;
    private InputInterface $input;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $entityManager)
    {
        parent::__construct('geonames:update:full');

        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->connection = $entityManager->getConnection();

        $this->repository = $entityManager->getRepository(Location::class);
    }

    protected function configure()
    {
        $this
            ->setDescription('Downloads geonames and/or alternatenames data dump and imports it')
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
                'Will drop the database if one already exist. Needs to be used with --force.'
            )
            ->addOption(
                'no-downloads',
                null,
                InputOption::VALUE_NONE,
                'Will not download the latest files but use the ones already there.'
            )
            ->setHelp('Downloads geonames and/or alternatenames data dump and imports it')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $io = new SymfonyStyle($input, $output);

        $returnCode = 0;

        $noDownloads = $input->getOption('no-downloads');
        $downloadFiles = (null === $noDownloads) || !(true === $noDownloads);
        if ($input->getOption('geonames')) {
            $returnCode = $this->updateGeonames($io, $downloadFiles);
        }

        if ($input->getOption('alternate')) {
            if (0 === $returnCode || $input->getOption('continue-on-errors')) {
                $returnCode = $this->updateAlternatenames($io, $downloadFiles);
            }
        }

        gc_disable();

        return $returnCode;
    }

    protected function updateGeonames(SymfonyStyle $io, bool $download): int
    {
        $io->note('Updating the geonames database');

        $dir = sys_get_temp_dir() . '/allcountries';
        if ($download) {
            $filename = $this->downloadFile(
                $io,
                'https://download.geonames.org/export/dump/allCountries.zip'
            );

            if (null === $filename) {
                return -1;
            }

            $zip = new ZipArchive();
            if (true === $zip->open($filename)) {
                $zip->extractTo($dir);
                $zip->close();
            } else {
                $io->error('Couldn\'t extract geoname database.');

                return -1;
            }
        }

        $lines = $this->getLines($dir . '/allCountries.txt');

        $progressbar = $io->createProgressBar();
        $progressbar->start($lines);

        $handle = fopen($dir . '/allCountries.txt', 'r');
        $rows = [];

        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            if (is_numeric($row[0]) && ('A' === $row[6] || 'P' === $row[6])) {
                $rows[] = $row;

                if (10000 === \count($rows)) {
                    $this->updateGeonamesInDatabase($rows);

                    unset($rows);
                    $rows = [];

                    gc_collect_cycles();
                }
            }
            $progressbar->advance();
        }

        // Also write the remaining entries to the database
        $this->updateGeonamesInDatabase($rows);

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

        $dir = sys_get_temp_dir() . '/alternatenames';
        if ($download) {
            $io->writeln('Downloading necessary file.');

            $filename = $this->downloadFile(
                $io,
                'https://download.geonames.org/export/dump/alternateNamesV2.zip'
            );

            if (null === $filename) {
                return -1;
            }

            $io->writeln('Extracting downlaoded file.');

            $zip = new ZipArchive();

            if (true === $zip->open($filename)) {
                $zip->extractTo($dir);
                $zip->close();
            } else {
                $io->error('Couldn\'t extract geoname alternate name database.');

                return -1;
            }

            $io->writeln('Import alternate names into database');
        }

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

    private function downloadFile(SymfonyStyle $io, string $url): ?string
    {
        $filesystem = new Filesystem();
        $filename = $filesystem->tempnam('.', 'rox-geonames');

        $progressbar = null;

        $response = $this->httpClient->request('GET', $url, [
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
            $io->error('Couldn\'t download and extract geoname database.');

            return null;
        }

        $fileHandler = fopen($filename, 'w');
        foreach($this->httpClient->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }
        fclose($fileHandler);

        return $filename;
    }

    private function updateGeonamesInDatabase(array $rows): int
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

        // Write rows into a file and call external command to import
        // Otherwise we hit memory limites
        $handle = fopen('geonames_rows.csv', 'w');
        foreach($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        $io = new SymfonyStyle($this->input, $this->output);

        $io->block('Running external command');

        /** @var Command $command */
        $command = $this->getApplication()->find('geonames:import:file');

        $arguments = [
            'file' => 'geonames_rows.csv',
        ];

        $import = new ArrayInput($arguments);
        $returnCode = $command->run($import, $this->output);

        return $returnCode;
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
}
