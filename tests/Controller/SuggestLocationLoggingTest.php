<?php

namespace App\Tests\Controller;

use App\Model\SuggestLocationModel;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The location suggestions log their search terms at debug level: production writes a request's records once one
 * is an error or above, and at alert every search wrote them.
 */
#[Group('integration')]
class SuggestLocationLoggingTest extends WebTestCase
{
    public function testASearchIsLoggedAtDebugLevel(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $model = $this->createStub(SuggestLocationModel::class);
        $model->method('getSuggestionsForPlaces')->willReturn([]);
        static::getContainer()->set(SuggestLocationModel::class, $model);
        $records = new TestHandler();
        $logger = static::getContainer()->get('logger');
        self::assertInstanceOf(Logger::class, $logger);
        $logger->pushHandler($records);

        $client->request('GET', '/suggest/locations/places?term=Berlin');

        $this->assertResponseIsSuccessful();
        $searches = array_filter($records->getRecords(), static fn ($record): bool => str_contains($record->message, 'suggestPlaces'));
        self::assertNotEmpty($searches);
        foreach ($searches as $search) {
            self::assertSame(Level::Debug, $search->level);
        }
    }
}
