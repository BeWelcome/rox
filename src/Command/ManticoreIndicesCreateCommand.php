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
    private int $chunk_size = 250000;
    protected static $defaultName = 'manticore:indices:create';
    protected static $defaultDescription = 'Creates and updates the manticore search indices';
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct(self::$defaultName);

        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->note('Creating manticore indices.');

        $config = ['host' => '127.0.0.1','port' => 9412];
        $client = new Client($config);
        $index = $client->index('geonames_manticore');

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
                    'expand_keywords' => '1',
                    'min_prefix_len' => '1',
                    'prefix_fields' => 'name',
                ]
            );
        }
        catch (Exception $e) {
            $io->error($e->getMessage());
            $io->error('Index \'geonames\' already exists. Please use manticore:indices:update instead.');
            // exit(-1);
        }

        $entityManager = $this->entityManager;
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

        $stmt = $entityManager
            ->getConnection()
            ->executeQuery(<<<___SQL
            SELECT
                count(*) as cnt
            FROM
                geo__names g
        ___SQL);

        $count = ($stmt->fetchNumeric())[0];

        $progressBar = new ProgressBar($output, $count);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');
        $progressBar->start();
        $progressBar->setRedrawFrequency(1000);
        $progressBar->minSecondsBetweenRedraws(5);

        $firstResult = 0;
        do {
            $query = $entityManager->createNativeQuery(<<<___SQL
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
                LIMIT {$firstResult}, {$this->chunk_size}
            ___SQL
                , $rsm);

            $addDocumentsCount = $this->addDocuments($index, $query, $progressBar);

            $firstResult += $this->chunk_size;
        } while ($addDocumentsCount > 0);
        $progressBar->finish();

        $stmt = $entityManager
            ->getConnection()
            ->executeQuery(<<<___SQL
            SELECT
                count(*) as cnt
            FROM
                geo__names_translations gt
        ___SQL);

        $count = ($stmt->fetchNumeric())[0];

        $progressBar = new ProgressBar($output, $count);
        $progressBar->start();

        $firstResult = 0;
        do {
            $query = $entityManager->createNativeQuery(<<<___SQL
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
                LIMIT {$firstResult}, {$this->chunk_size}
            ___SQL
                , $rsm);

            $addDocumentsCount = $this->addDocuments($index, $query, $progressBar);

            $firstResult += $this->chunk_size;
        } while ($addDocumentsCount > 0);
        $progressBar->finish();

        $io->success('Created Manticore indices.');

        return Command::SUCCESS;
    }

    private function addDocuments(Index $index, NativeQuery $query, ProgressBar $progress): int
    {
        $locations = $query->getResult();
        $documents = [];

        /** @var NewLocation $location */
        foreach ($locations as $location) {
            $isPlace = $location['feature_class'] === 'P';
            $isAdmin = $location['feature_class'] === 'A';
            $isCountry = $location['feature_code'] !== 'PCLH' &&
                substr($location['feature_code'], 0, 3) === 'PCL';

            $documents[] = [
                'geoname_id' => $location['geonameid'],
                'name' => $location['name'],
                'country' => $location['country'],
                'isPlace' => $isPlace,
                'isAdmin' => $isAdmin,
                'isCountry' => $isCountry,
                'locale' => $location['locale'],
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
}
