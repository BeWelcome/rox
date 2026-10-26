<?php

namespace App\Tests\Command;

use App\Command\GeonamesUpdateDailyCommand;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Applies a day of geonames changes, served by a mock HTTP client, to location and location_translation.
 * The test places use ids far above the fixtures' (99000001 and up) and the made-up country code XX.
 */
#[Group('integration')]
class GeonamesUpdateDailyCommandTest extends KernelTestCase
{
    private const string DATE = '2026-10-20';
    private const int COUNTRY = 99000001;
    private const int REGION = 99000002;
    private const int TOWN = 99000003;
    private const int VILLAGE = 99000004;
    private const int DUPLICATE = 99000005;
    private const int USED = 99000006;

    private Connection $connection;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->connection = static::getContainer()->get(Connection::class);

        $this->addPlace(self::COUNTRY, 'Testland', 'A', 'PCLI', '00');
        $this->addPlace(self::REGION, 'Test Region', 'A', 'ADM1', '01');
        $this->addPlace(self::VILLAGE, 'Old Village Name', 'P', 'PPL', '01');
        $this->addPlace(self::DUPLICATE, 'Village (duplicate)', 'P', 'PPL', '01');
        $this->addPlace(self::USED, 'Used Place', 'P', 'PPL', '01');
        $this->connection->executeStatement('INSERT INTO address (location) VALUES (?), (?)', [self::DUPLICATE, self::USED]);
        $this->connection->executeStatement(
            "INSERT INTO location_translation (locale, field, content, object_id) VALUES ('de', 'name', 'Altes Dorf', ?), ('fr', 'name', 'Vieux village', ?)",
            [self::VILLAGE, self::VILLAGE]
        );
    }

    public function testAddsAndChangesPlaces(): void
    {
        $this->applyChanges(modifications: [
            $this->place(self::TOWN, 'New Town', 'P', 'PPL', '01', 52.5, 13.4),
            $this->place(self::VILLAGE, 'New Village Name', 'P', 'PPL', '01', 48.1, 11.6),
            // Only administrative areas (A) and populated places (P) are kept
            $this->place(99000007, 'Some Mountain', 'T', 'MT', '01', 47.0, 10.0),
        ]);

        $town = $this->location(self::TOWN);
        self::assertSame('New Town', $town['name']);
        self::assertEqualsWithDelta(13.4, (float) $town['x'], 0.0001);
        self::assertEqualsWithDelta(52.5, (float) $town['y'], 0.0001);
        self::assertSame(self::COUNTRY, (int) $town['country']);
        self::assertSame(self::REGION, (int) $town['admin1']);
        self::assertSame('New Village Name', $this->location(self::VILLAGE)['name']);
        self::assertFalse($this->location(99000007));
    }

    public function testDeletesPlacesAndMovesWhatUsesADuplicate(): void
    {
        $tester = $this->applyChanges(deletes: [
            [(string) self::DUPLICATE, 'Village (duplicate)', 'duplicate of ' . self::VILLAGE],
            [(string) self::USED, 'Used Place', 'deleted'],
        ]);

        self::assertFalse($this->location(self::DUPLICATE));
        self::assertSame(1, (int) $this->connection->fetchOne('SELECT COUNT(*) FROM address WHERE location = ?', [self::VILLAGE]));
        // Still used by an address, so it stays
        self::assertNotFalse($this->location(self::USED));
        self::assertStringContainsString('Place ' . self::USED . ' is deleted by geonames but still used here', $tester->getDisplay());
    }

    public function testAddsChangesAndDeletesNames(): void
    {
        $this->applyChanges(
            names: [
                ['1', (string) self::VILLAGE, 'it', 'Villaggio', '', ''],
                // Not preferred: the existing German name stays
                ['2', (string) self::VILLAGE, 'de', 'Anderes Dorf', '', ''],
                ['3', (string) self::VILLAGE, 'es', 'Pueblo', '1', ''],
                // A preferred name replaces the existing one
                ['4', (string) self::VILLAGE, 'es', 'Aldea', '1', ''],
                // Not a language, and an unknown place
                ['5', (string) self::VILLAGE, 'link', 'https://example.org', '', ''],
                ['6', '99000099', 'it', 'Nessuno', '', ''],
            ],
            nameDeletes: [['7', (string) self::VILLAGE, 'Vieux village', '']],
        );

        self::assertSame(
            ['de' => 'Altes Dorf', 'es' => 'Aldea', 'it' => 'Villaggio'],
            $this->connection->fetchAllKeyValue(
                "SELECT locale, content FROM location_translation WHERE object_id = ? AND field = 'name' ORDER BY locale",
                [self::VILLAGE]
            )
        );
        self::assertSame(0, (int) $this->connection->fetchOne('SELECT COUNT(*) FROM location_translation WHERE object_id = 99000099'));
    }

    public function testRejectsAnInvalidDate(): void
    {
        $tester = new CommandTester(new GeonamesUpdateDailyCommand(new MockHttpClient([]), $this->connection));

        self::assertSame(2, $tester->execute(['--date' => ['yesterday']]));
    }

    /**
     * @param list<list<string>> $modifications
     * @param list<list<string>> $deletes
     * @param list<list<string>> $names
     * @param list<list<string>> $nameDeletes
     */
    private function applyChanges(array $modifications = [], array $deletes = [], array $names = [], array $nameDeletes = []): CommandTester
    {
        $files = [
            'modifications-' . self::DATE . '.txt' => $modifications,
            'deletes-' . self::DATE . '.txt' => $deletes,
            'alternateNamesModifications-' . self::DATE . '.txt' => $names,
            'alternateNamesDeletes-' . self::DATE . '.txt' => $nameDeletes,
        ];
        $httpClient = new MockHttpClient(static function (string $method, string $url) use ($files): MockResponse {
            $rows = $files[basename($url)] ?? null;
            if (null === $rows) {
                return new MockResponse('', ['http_code' => 404]);
            }

            return new MockResponse(implode("\n", array_map(static fn (array $row): string => implode("\t", $row), $rows)) . "\n");
        });

        $tester = new CommandTester(new GeonamesUpdateDailyCommand($httpClient, $this->connection));
        self::assertSame(0, $tester->execute(['--date' => [self::DATE]]));

        return $tester;
    }

    /**
     * A row of modifications-<date>.txt (the format of allCountries.txt).
     *
     * @return list<string>
     */
    private function place(int $id, string $name, string $class, string $code, string $admin1, float $latitude, float $longitude): array
    {
        return [(string) $id, $name, $name, '', (string) $latitude, (string) $longitude, $class, $code, 'XX', '', $admin1, '', '', '', '1000', '', '', 'Europe/Berlin', self::DATE];
    }

    private function addPlace(int $id, string $name, string $class, string $code, string $admin1): void
    {
        $this->connection->executeStatement(
            "INSERT INTO location (geoname_id, name, coordinates, feature_class, feature_code, country_id, admin_1_id)
             VALUES (?, ?, Point(0, 0), ?, ?, 'XX', ?)",
            [$id, $name, $class, $code, $admin1]
        );
    }

    /**
     * @return array<string, mixed>|false
     */
    private function location(int $id): array|false
    {
        return $this->connection->fetchAssociative(
            'SELECT name, ST_X(coordinates) AS x, ST_Y(coordinates) AS y, country, admin1 FROM location WHERE geoname_id = ?',
            [$id]
        );
    }
}
