<?php

namespace App\Command;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Applies the daily change files of geonames.org to location and location_translation: new and changed places
 * (feature classes A and P), deleted places, and new, changed and deleted names.
 *
 * @see https://download.geonames.org/export/dump/readme.txt
 */
#[AsCommand(
    name: 'geonames:update:daily',
    description: 'Applies the daily changes of geonames.org (places and their names) of the last two days.',
)]
class GeonamesUpdateDailyCommand extends Command
{
    private const string BASE_URL = 'https://download.geonames.org/export/dump/';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'date',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'The day(s) to apply (Y-m-d). Default: yesterday and the day before.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dates = $input->getOption('date') ?: [
            new DateTimeImmutable('-2 days')->format('Y-m-d'),
            new DateTimeImmutable('-1 day')->format('Y-m-d'),
        ];
        foreach ($dates as $date) {
            if (false === DateTimeImmutable::createFromFormat('!Y-m-d', $date)) {
                $io->error('Not a date (Y-m-d): ' . $date);

                return Command::INVALID;
            }
        }

        foreach ($dates as $date) {
            $io->section('Changes of ' . $date);
            $io->writeln(\sprintf('Places: %d added or changed', $this->updatePlaces($date)));
            [$deleted, $kept] = $this->deletePlaces($date);
            $io->writeln(\sprintf('Places: %d deleted', $deleted));
            foreach ($kept as $geonameId) {
                $io->warning(\sprintf('Place %d is deleted by geonames but still used here, so it is kept.', $geonameId));
            }
            $io->writeln(\sprintf('Names: %d added or changed', $this->updateNames($date)));
            $io->writeln(\sprintf('Names: %d deleted', $this->deleteNames($date)));
        }

        $io->success('Applied the geonames changes.');

        return Command::SUCCESS;
    }

    /**
     * modifications-<date>.txt has the format of allCountries.txt: 0 geonameid, 1 name, 4 latitude, 5 longitude,
     * 6 feature class, 7 feature code, 8 country code, 10-13 admin1-4 codes, 14 population, 18 modification date.
     */
    private function updatePlaces(string $date): int
    {
        $count = 0;
        foreach ($this->fetchRows('modifications-' . $date . '.txt') as $row) {
            if (\count($row) < 19 || !is_numeric($row[0]) || !\in_array($row[6], ['A', 'P'], true)) {
                continue;
            }
            $this->connection->executeStatement(
                'INSERT INTO location (geoname_id, name, coordinates, feature_class, feature_code, country_id,
                     admin_1_id, admin_2_id, admin_3_id, admin_4_id, population, moddate)
                 VALUES (:id, :name, Point(:longitude, :latitude), :class, :code, :country, :admin1, :admin2, :admin3,
                     :admin4, :population, :moddate)
                 ON DUPLICATE KEY UPDATE name = VALUES(name), coordinates = VALUES(coordinates),
                     feature_class = VALUES(feature_class), feature_code = VALUES(feature_code),
                     country_id = VALUES(country_id), admin_1_id = VALUES(admin_1_id), admin_2_id = VALUES(admin_2_id),
                     admin_3_id = VALUES(admin_3_id), admin_4_id = VALUES(admin_4_id), population = VALUES(population),
                     moddate = VALUES(moddate)',
                [
                    'id' => (int) $row[0],
                    'name' => $row[1],
                    'longitude' => (float) $row[5],
                    'latitude' => (float) $row[4],
                    'class' => $row[6],
                    'code' => $row[7],
                    'country' => $row[8],
                    'admin1' => $row[10],
                    'admin2' => $row[11],
                    'admin3' => $row[12],
                    'admin4' => $row[13],
                    'population' => '' === $row[14] ? null : (int) $row[14],
                    'moddate' => '' === $row[18] ? null : $row[18],
                ]
            );
            $this->linkPlace((int) $row[0]);
            ++$count;
        }

        return $count;
    }

    /**
     * Links a place to its country and first two admin levels, as the full import does for all places.
     */
    private function linkPlace(int $geonameId): void
    {
        $this->connection->executeStatement(
            "UPDATE location l SET
                 l.country = (SELECT c.geoname_id FROM (SELECT geoname_id, country_id FROM location
                     WHERE feature_class = 'A' AND feature_code LIKE 'PCL%' AND feature_code <> 'PCLH') c
                     WHERE c.country_id = l.country_id ORDER BY c.geoname_id LIMIT 1),
                 l.admin1 = (SELECT a.geoname_id FROM (SELECT geoname_id, country_id, admin_1_id FROM location
                     WHERE feature_code = 'ADM1') a
                     WHERE a.country_id = l.country_id AND a.admin_1_id = l.admin_1_id ORDER BY a.geoname_id LIMIT 1),
                 l.admin2 = (SELECT a.geoname_id FROM (SELECT geoname_id, country_id, admin_1_id, admin_2_id FROM location
                     WHERE feature_code = 'ADM2') a
                     WHERE a.country_id = l.country_id AND a.admin_1_id = l.admin_1_id AND a.admin_2_id = l.admin_2_id
                     ORDER BY a.geoname_id LIMIT 1)
             WHERE l.geoname_id = :id",
            ['id' => $geonameId]
        );
    }

    /**
     * deletes-<date>.txt: 0 geonameid, 1 name, 2 comment. A comment like "duplicate of 123" names the place that
     * stays; what refers to the deleted one moves there first.
     *
     * @return array{0: int, 1: list<int>} the number of deleted places, and the places kept because they're in use
     */
    private function deletePlaces(string $date): array
    {
        $deleted = 0;
        $kept = [];
        foreach ($this->fetchRows('deletes-' . $date . '.txt') as $row) {
            if (!is_numeric($row[0] ?? null)) {
                continue;
            }
            $geonameId = (int) $row[0];
            if (preg_match('/duplicate\D*(\d+)/i', $row[2] ?? '', $matches)) {
                $this->moveReferences($geonameId, (int) $matches[1]);
            }
            try {
                $deleted += $this->connection->executeStatement('DELETE FROM location WHERE geoname_id = ?', [$geonameId]);
            } catch (ForeignKeyConstraintViolationException) {
                $kept[] = $geonameId;
            }
        }

        return [$deleted, $kept];
    }

    private function moveReferences(int $from, int $to): void
    {
        if (false === $this->connection->fetchOne('SELECT 1 FROM location WHERE geoname_id = ?', [$to])) {
            return;
        }
        foreach (['address' => 'location', 'activities' => 'locationId', 'sub_trips' => 'location'] as $table => $column) {
            $this->connection->executeStatement(
                "UPDATE {$table} SET {$column} = :to WHERE {$column} = :from",
                ['to' => $to, 'from' => $from]
            );
        }
    }

    /**
     * alternateNamesModifications-<date>.txt: 0 alternateNameId, 1 geonameid, 2 isolanguage, 3 alternate name,
     * 4 isPreferredName. As in the full import, a place keeps one name per language: the first one, unless a
     * preferred name comes along.
     */
    private function updateNames(string $date): int
    {
        $count = 0;
        foreach ($this->fetchRows('alternateNamesModifications-' . $date . '.txt') as $row) {
            if (\count($row) < 5 || !is_numeric($row[0]) || !is_numeric($row[1])) {
                continue;
            }
            $locale = $this->locale($row[2]);
            if (null === $locale) {
                continue;
            }
            $count += min(1, $this->connection->executeStatement(
                "INSERT INTO location_translation (locale, field, content, object_id)
                 SELECT :locale, 'name', :name, geoname_id FROM location WHERE geoname_id = :id
                 ON DUPLICATE KEY UPDATE content = IF(:preferred, VALUES(content), content)",
                ['locale' => $locale, 'name' => $row[3], 'id' => (int) $row[1], 'preferred' => (int) ('1' === $row[4])]
            ));
        }

        return $count;
    }

    /**
     * alternateNamesDeletes-<date>.txt: 0 alternateNameId, 1 geonameid, 2 alternate name, 3 comment.
     */
    private function deleteNames(string $date): int
    {
        $count = 0;
        foreach ($this->fetchRows('alternateNamesDeletes-' . $date . '.txt') as $row) {
            if (!is_numeric($row[0] ?? null) || !is_numeric($row[1] ?? null)) {
                continue;
            }
            $count += $this->connection->executeStatement(
                "DELETE FROM location_translation WHERE object_id = ? AND field = 'name' AND content = ?",
                [(int) $row[1], $row[2] ?? '']
            );
        }

        return $count;
    }

    /**
     * The language codes the full import keeps (two or three letters), or null.
     */
    private function locale(string $language): ?string
    {
        return '' !== $language && \strlen($language) < 4 ? $language : null;
    }

    /**
     * The rows of a tab separated file of geonames.org; none if there's no such file (yet).
     *
     * @return iterable<list<string>>
     */
    private function fetchRows(string $filename): iterable
    {
        $response = $this->httpClient->request('GET', self::BASE_URL . $filename);
        if (200 !== $response->getStatusCode()) {
            return [];
        }

        $rows = [];
        foreach (preg_split('/\R/', $response->getContent()) as $line) {
            if ('' !== $line) {
                $rows[] = explode("\t", $line);
            }
        }

        return $rows;
    }
}
