<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Manticoresearch\Client;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Index;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;

class ManticoreIndicesGeonamesCommand extends Command
{
    private const GEONAMES_INDEX = 'geonames_rt';

    /** Number of rows read from the database per query. */
    private const FETCH_SIZE = 20000;

    /** Number of documents sent to Manticore in a single bulk request. */
    private const BULK_SIZE = 2000;

    protected static $defaultName = 'manticore:indices:geonames';
    protected static $defaultDescription = 'Creates the manticore indices for the location search';
    private EntityManagerInterface $entityManager;
    private SymfonyStyle $io;
    private string $manticoreHost;
    private int $manticorePort;

    /** @var array<int, int> geoname id => number of active members in that city */
    private array $memberCounts = [];

    /** @var array<int, array<string, mixed>> documents waiting to be sent to Manticore */
    private array $documents = [];

    public function __construct(EntityManagerInterface $entityManager, string $manticoreHost, int $manticorePort)
    {
        parent::__construct(self::$defaultName);

        $this->entityManager = $entityManager;
        $this->manticoreHost = $manticoreHost;
        $this->manticorePort = $manticorePort;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '-1');

        $this->io = new SymfonyStyle($input, $output);
        $this->io->note('Creating manticore geonames real-time index.');
        $this->io->newLine();

        $index = $this->createGeonamesIndex();
        if (null !== $index) {
            $this->memberCounts = $this->getMemberCounts();

            $this->addGeonamesDocuments($index, $output);

            $this->optimizeGeonamesIndex();

            $this->addAlternateNamesDocuments($index, $output);

            $index->flush();

            $this->io->newLine();
            $this->io->note('Created ' . self::GEONAMES_INDEX . '.');
        } else {
            $this->io->note(
                'Skipped creation of ' . self::GEONAMES_INDEX . ' index. ' .
                'Try using manticore:indices:update --geonames instead'
            );
            return Command::INVALID;
        }

        $this->io->success('Created Manticore index.');

        return Command::SUCCESS;
    }

    private function createGeonamesIndex(): ?Index
    {
        $client = new Client(['host' => $this->manticoreHost,'port' => $this->manticorePort]);

        $index = $client->index(self::GEONAMES_INDEX);
        // If the index doesn't exist, drop() fails with an error message. So we run it silenced.
        $index->drop(true);

        try {
            $index->create(
                [
                    'geoname_id' => ['type' => 'integer'],
                    'name' => ['type' => 'text'],
                    'isPlace' => ['type' => 'bool'],
                    'isAdmin' => ['type' => 'bool'],
                    'isCountry' => ['type' => 'bool'],
                    'locale' => ['type' => 'string'],
                    'admin1' => ['type' => 'string'],
                    'admin2' => ['type' => 'string'],
                    'admin3' => ['type' => 'string'],
                    'admin4' => ['type' => 'string'],
                    'country' => ['type' => 'string'],
                    'population' => ['type' => 'integer'],
                    'member_count' => ['type' => 'integer'],
                ],
                [
                    'index_exact_words' => '1',
                    'expand_keywords' => '0', // don't support wildcards upfront only in searches
                    'charset_table' => 'non_cjk,U+0020->_', // Ignore spaces (turns e.g. Los Angeles into Los_Angeles)
                    'min_prefix_len' => '1',
                    'prefix_fields' => 'name',
                    'ngram_chars' => 'cjk',
                    'ngram_len' => '1',
                ]
            );

            return $index;
        } catch(Exception $e) {
            $this->io->error($e->getMessage());
            $this->io->error('Index ' . self::GEONAMES_INDEX . ' couldn\'t be created.');

            return null;
        }
    }

    private function optimizeGeonamesIndex(): void
    {
        $this->io->note('Optimizing ' . self::GEONAMES_INDEX . ' index (merging disk chunks before translations phase).');
        $client = new Client(['host' => $this->manticoreHost, 'port' => $this->manticorePort]);
        try {
            // raw=true is required for non-SELECT DDL commands via the /sql endpoint.
            // The response for OPTIMIZE cannot be parsed by the PHP client, so suppress empty exceptions.
            $client->sql('OPTIMIZE TABLE ' . self::GEONAMES_INDEX . ' OPTION cutoff=1, sync=1', true);
        } catch (\Exception $e) {
            if ($e->getMessage() !== '') {
                throw $e;
            }
        }
        $this->io->note('Optimization complete.');
    }

    /**
     * The member counts are needed for every single document. Fetching them once keeps them out of the (paginated)
     * geonames queries which would otherwise re-run the aggregation for every single chunk.
     *
     * @return array<int, int>
     */
    private function getMemberCounts(): array
    {
        $counts = $this->getConnection()
            ->executeQuery(<<<___SQL
                SELECT
                    m.IdCity,
                    COUNT(m.IdCity) AS total
                FROM
                    members m
                WHERE m.status IN ('Active', 'OutOfRemind')
                GROUP BY
                    m.IdCity
            ___SQL)
            ->fetchAllKeyValue();

        $memberCounts = [];
        foreach ($counts as $cityId => $total) {
            $memberCounts[(int) $cityId] = (int) $total;
        }

        return $memberCounts;
    }

    private function addGeonamesDocuments(Index $index, OutputInterface $output): void
    {
        $this->io->note('Adding documents to geonames_rt from geo__names table.');
        $this->io->newLine();

        $connection = $this->getConnection();
        $count = (int) $connection->executeQuery('SELECT COUNT(*) FROM geo__names')->fetchOne();
        $progressBar = $this->getProgressBar($output, $count);

        // Keyset pagination. LIMIT <offset>, <chunk size> makes the database skip an ever growing number of rows
        // which gets painfully slow towards the end of a table with several million rows.
        $fetchSize = self::FETCH_SIZE;
        $lastId = 0;
        do {
            $result = $connection->executeQuery(<<<___SQL
                SELECT
                    g.geonameId AS geonameid,
                    g.`name` AS name,
                    g.feature_class AS feature_class,
                    g.feature_code AS feature_code,
                    g.country_id AS country,
                    g.admin_1_id AS admin1,
                    g.admin_2_id AS admin2,
                    g.admin_3_id AS admin3,
                    g.admin_4_id AS admin4,
                    '_geo' AS locale,
                    g.population AS population
                FROM
                    geo__names g
                WHERE
                    g.geonameId > :lastId
                ORDER BY
                    g.geonameId
                LIMIT {$fetchSize}
            ___SQL, ['lastId' => $lastId]);

            $rowCount = 0;
            foreach ($result->iterateAssociative() as $row) {
                ++$rowCount;
                $lastId = (int) $row['geonameid'];
                $this->addDocument($index, $row, $progressBar);
            }
            $result->free();
        } while ($rowCount > 0);

        $this->sendPendingDocuments($index, $progressBar);

        $progressBar->finish();
        $this->io->newLine();
    }

    private function addAlternateNamesDocuments(Index $index, OutputInterface $output): void
    {
        $this->io->note('Adding documents to geonames_rt from geo__names_translations table.');
        $this->io->newLine();

        $connection = $this->getConnection();
        $count = (int) $connection->executeQuery('SELECT COUNT(*) FROM geo__names_translations')->fetchOne();
        if (0 === $count) {
            return;
        }

        $progressBar = $this->getProgressBar($output, $count);

        $fetchSize = self::FETCH_SIZE;
        $lastId = 0;
        do {
            $result = $connection->executeQuery(<<<___SQL
                SELECT
                    gt.id AS translation_id,
                    g.geonameId AS geonameid,
                    gt.`content` AS name,
                    g.feature_class AS feature_class,
                    g.feature_code AS feature_code,
                    g.country_id AS country,
                    g.admin_1_id AS admin1,
                    g.admin_2_id AS admin2,
                    g.admin_3_id AS admin3,
                    g.admin_4_id AS admin4,
                    gt.`locale` AS locale,
                    g.population AS population
                FROM
                    geo__names_translations gt
                JOIN
                    geo__names g ON g.geonameId = gt.foreign_key
                WHERE
                    gt.id > :lastId
                ORDER BY
                    gt.id
                LIMIT {$fetchSize}
            ___SQL, ['lastId' => $lastId]);

            $rowCount = 0;
            foreach ($result->iterateAssociative() as $row) {
                ++$rowCount;
                $lastId = (int) $row['translation_id'];
                $this->addDocument($index, $row, $progressBar);
            }
            $result->free();
        } while ($rowCount > 0);

        $this->sendPendingDocuments($index, $progressBar);

        $progressBar->finish();
        $this->io->newLine();
    }

    /**
     * Collects documents and sends them off in small batches. Manticore refuses bulk requests larger than
     * max_packet_size and building a single request for hundreds of thousands of documents holds several copies
     * of the whole data set (rows, documents, bulk payload) in memory at the same time.
     *
     * @param array<string, mixed> $row
     */
    private function addDocument(Index $index, array $row, ProgressBar $progress): void
    {
        $featureClass = (string) $row['feature_class'];
        $featureCode = (string) $row['feature_code'];

        $isPlace = 'P' === $featureClass && 'PPL' === substr($featureCode, 0, 3)
            && 'PPLH' !== $featureCode && 'PPLCH' !== $featureCode
            && 'PPLX' !== $featureCode && 'PPLQ' !== $featureCode;
        $isCountry =
            ('A' === $featureClass && 'PCL' === substr($featureCode, 0, 3)
                && 'PRSH' !== $featureCode && 'PCLH' !== $featureCode)
            || ('TERR' === $featureCode);
        $isAdmin = 'A' === $featureClass && !$isCountry;

        $geonameId = (int) $row['geonameid'];

        $this->documents[] = [
            'geoname_id' => $geonameId,
            'name' => (string) $row['name'],
            'country' => (string) $row['country'],
            'isPlace' => $isPlace,
            'isAdmin' => $isAdmin,
            'isCountry' => $isCountry,
            'locale' => $this->adaptLocale((string) $row['locale']),
            'admin1' => (string) $row['admin1'],
            'admin2' => (string) $row['admin2'],
            'admin3' => (string) $row['admin3'],
            'admin4' => (string) $row['admin4'],
            'population' => (int) $row['population'],
            'member_count' => $this->memberCounts[$geonameId] ?? 0,
        ];

        if (count($this->documents) >= self::BULK_SIZE) {
            $this->sendPendingDocuments($index, $progress);
        }
    }

    private function sendPendingDocuments(Index $index, ProgressBar $progress): void
    {
        if ([] === $this->documents) {
            return;
        }

        $documents = $this->documents;
        $this->documents = [];

        try {
            $index->addDocuments($documents);
        } catch (ResponseException $e) {
            $this->reportBulkError($e);
        }

        $progress->advance(count($documents));
    }

    /**
     * Manticore's getError() runs the error through json_encode() which returns false - and therefore an empty
     * exception message - as soon as the reported document contains invalid UTF-8. Dig the actual errors out of
     * the response instead of showing an empty message.
     */
    private function reportBulkError(ResponseException $e): void
    {
        $message = $e->getMessage();
        if ('' === $message) {
            $response = $e->getResponse()->getResponse();
            $errors = [];
            foreach ($response['items'] ?? [] as $item) {
                foreach ((array) $item as $action) {
                    if (!empty($action['error'])) {
                        $errors[] = $action['error'];
                    }
                }
            }
            $message = json_encode(
                [] === $errors ? $response : array_slice($errors, 0, 5),
                JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE
            );
            $message = substr((string) $message, 0, 2000);
        }

        $this->io->newLine();
        $this->io->error('Manticore rejected a bulk request: ' . $message);
    }

    private function getConnection(): Connection
    {
        return $this->entityManager->getConnection();
    }

    private function getProgressBar(OutputInterface $output, $count): ProgressBar
    {
        $progressBar = new ProgressBar($output, $count);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');
        $progressBar->start();
        $progressBar->setRedrawFrequency(1000);
        $progressBar->minSecondsBetweenRedraws(5);

        return $progressBar;
    }

    private function adaptLocale(string $locale)
    {
        switch ($locale) {
            case "zh-TW":
                $locale = "zh-hant";
                break;
            case "zh-CN":
                $locale = "zh-hans";
                break;
            case "pt-BR":
                $locale = "pt-br";
                break;
        }

        return $locale;
    }
}
