<?php

namespace App\Command;

use App\Entity\NewLocation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Exception;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\Entity\Translation;
use Manticoresearch\Client;
use Manticoresearch\Index;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;

class ManticoreIndicesCreateCommand extends Command
{
    private const GEONAMES_INDEX = 'geonames_rt';
    private int $chunkSize = 250000;

    protected static $defaultName = 'manticore:indices:create';
    protected static $defaultDescription = 'Creates and updates the manticore search indices';
    private EntityManagerInterface $entityManager;
    private ResultSetMapping $rsm;
    private SymfonyStyle $io;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct(self::$defaultName);

        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->note('Creating manticore indices.');
        $this->io->newLine();

        $index = $this->createGeonamesIndex();
        if (null !== $index) {
            $this->addGeonamesDocuments($index, $output);

             $this->addAlternateNamesDocuments($index, $output);

            $this->io->newLine();
            $this->io->note('Created ' . self::GEONAMES_INDEX . '.');
        } else {
            $this->io->note(
                'Skipped creation of ' . self::GEONAMES_INDEX . ' index. ' .
                'Try using manticore:indices:update --geonames instead'
            );
        }

        $this->io->success('Created Manticore indices.');

        return Command::SUCCESS;
    }

    private function createGeonamesIndex(): ?Index
    {
        $client = new Client(['host' => '127.0.0.1','port' => 9412]);
        $index = $client->index('geonames_rt');

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
        } catch (Exception $e) {
            // $index = null;

            $this->io->error($e->getMessage());
            $this->io->error('Index ' . self::GEONAMES_INDEX . ' geonames_rt\' already exists or another problem occureed.');
        }

        return $index;
    }

    private function addGeonamesDocuments(Index $index, OutputInterface $output)
    {
        $this->io->note('Adding documents to geonames_rt from geo__names table.');
        $this->io->newLine();

        $stmt = $this->entityManager
            ->getConnection()
            ->executeQuery(<<<___SQL
            SELECT
                count(*) as cnt
            FROM
                geo__names g
        ___SQL);

        $count = ($stmt->fetchNumeric())[0];

        $progressBar = $this->getProgressBar($output, $count);

        $firstResult = 0;
        do {
            $query = $this->entityManager->createNativeQuery(<<<___SQL
                SELECT
                    g.geonameid AS geonameid,
                    g.`name` AS name,
                    g.feature_class,
                    g.feature_code,
                    g.country_id,
                    g.admin_1_id,
                    g.admin_2_id,
                    g.admin_3_id,
                    g.admin_4_id,
                    '_geo' AS locale,
                    g.population,
                    IFNULL(membercounts.total, 0) AS member_count
                FROM
                    geo__names g
                LEFT JOIN (
                    SELECT
                        m.IdCity,
                        COUNT(m.IdCity) total
                    FROM
                        members m
                    WHERE m.status IN ('Active', 'OutOfRemind')
                    GROUP BY
                        m.IdCity
                ) membercounts
                ON (g.geonameid = membercounts.IdCity)
                LIMIT {$firstResult}, {$this->chunkSize}
            ___SQL
                , $this->getResultSetMappingForGeonamesIndex());

            $addDocumentsCount = $this->addGeonamesDocumentsToIndex($index, $query, $progressBar);

            $firstResult += $this->chunkSize;
        } while ($addDocumentsCount > 0);

        $progressBar->finish();
        $this->io->newLine();
    }

    private function addAlternateNamesDocuments(Index $index, OutputInterface $output)
    {
        $this->io->note('Adding documents to geonames_rt from geo__names_translations table.');
        $this->io->newLine();

        $stmt = $this->entityManager
            ->getConnection()
            ->executeQuery(<<<___SQL
            SELECT
                count(*) as cnt
            FROM
                geo__names_translations gt
        ___SQL);

        $count = ($stmt->fetchNumeric())[0];

        $progressBar = $this->getProgressBar($output, $count);
        $progressBar->start();

        $firstResult = 0;
        do {
            $query = $this->entityManager->createNativeQuery(<<<___SQL
                SELECT
                    g.geonameid,
                    gt.`content` AS name,
                    g.feature_class,
                    g.feature_code,
                    g.country_id,
                    g.admin_1_id,
                    g.admin_2_id,
                    g.admin_3_id,
                    g.admin_4_id,
                    gt.`locale` AS locale,
                    g.population,
                    IFNULL(membercounts.total, 0) AS member_count
                FROM
                    geo__names g
                JOIN
                    geo__names_translations gt ON g.geonameId = gt.foreign_key
                LEFT JOIN (
                    SELECT
                        m.IdCity,
                        COUNT(m.IdCity) total
                    FROM
                        members m
                    WHERE m.status IN ('Active', 'OutOfRemind')
                    GROUP BY
                        m.IdCity
                ) membercounts
                ON (g.geonameid = membercounts.IdCity)
                LIMIT {$firstResult}, {$this->chunkSize}
            ___SQL
                , $this->getResultSetMappingForGeonamesIndex());

            $addDocumentsCount = $this->addGeonamesDocumentsToIndex($index, $query, $progressBar);

            $firstResult += $this->chunkSize;
        } while ($addDocumentsCount > 0);

        $progressBar->finish();
        $this->io->newLine();
    }

    private function addGeonamesDocumentsToIndex(Index $index, NativeQuery $query, ProgressBar $progress): int
    {
        $locations = $query->getResult();
        $documents = [];

        /** @var NewLocation $location */
        foreach ($locations as $location) {
            $isPlace = $location['feature_class'] === 'P' && substr($location['feature_code'], 0, 3) === 'PPL'
                && $location['feature_code'] !== 'PPLH' && $location['feature_code'] !== 'PPLCH'
                && $location['feature_code'] !== 'PPLX' && $location['feature_code'] !== 'PPLQ';
            $isCountry =
                ($location['feature_class'] === 'A' && substr($location['feature_code'], 0, 3) === 'PCL'
                    && $location['feature_code'] !== 'PRSH' && $location['feature_code'] !== 'PCLH')
                || ($location['feature_code'] === 'TERR');
            $isAdmin = $location['feature_class'] === 'A' && !$isCountry;

            $documents[] = [
                'geoname_id' => $location['geonameid'],
                'name' => $location['name'],
                'country' => $location['country'],
                'isPlace' => $isPlace,
                'isAdmin' => $isAdmin,
                'isCountry' => $isCountry,
                'locale' => $this->adaptLocale($location['locale']),
                'admin1' => $location['admin1'],
                'admin2' => $location['admin2'],
                'admin3' => $location['admin3'],
                'admin4' => $location['admin4'],
                'population' => $location['population'],
                'member_count' => $location['member_count'],
            ];
            $progress->advance();
        }
        $count = \count($locations);
        unset($locations);
        $index->addDocuments($documents);
        $index->flush();

        gc_collect_cycles();

        return $count;
    }

    private function getResultSetMappingForGeonamesIndex(): ResultSetMapping
    {
        $rsm = new ResultSetMapping();
        $rsm
            ->addScalarResult('geonameid', 'geonameid')
            ->addScalarResult('name', 'name')
            ->addScalarResult('country_id', 'country')
            ->addScalarResult('admin_1_id', 'admin1')
            ->addScalarResult('admin_2_id', 'admin2')
            ->addScalarResult('admin_3_id', 'admin3')
            ->addScalarResult('admin_4_id', 'admin4')
            ->addScalarResult('feature_class', 'feature_class')
            ->addScalarResult('feature_code', 'feature_code')
            ->addScalarResult('locale', 'locale')
            ->addScalarResult('population', 'population')
            ->addScalarResult('member_count', 'member_count')
        ;

        return $rsm;
    }
    private function getProgressBar(OutputInterface $output, $count): ProgressBar
    {
        $progressBar = new ProgressBar($output, $count);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');
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
