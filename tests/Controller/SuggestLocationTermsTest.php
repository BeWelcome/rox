<?php

namespace App\Tests\Controller;

use App\Model\SuggestLocationModel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The model searches the place in the first term: the controller hands it a list starting at index 0 and doesn't
 * call it without a term.
 */
#[Group('integration')]
class SuggestLocationTermsTest extends WebTestCase
{
    public static function urls(): array
    {
        return [
            'exact' => ['/suggest/locations/places/exact', 'getSuggestionsForPlacesExact'],
            'places' => ['/suggest/locations/places', 'getSuggestionsForPlaces'],
            'all' => ['/suggest/locations/all', 'getSuggestionsForLocations'],
        ];
    }

    #[DataProvider('urls')]
    public function testAnEmptyTermGivesNoLocations(string $url, string $method): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $model = $this->createMock(SuggestLocationModel::class);
        $model->expects($this->never())->method($method);
        static::getContainer()->set(SuggestLocationModel::class, $model);

        foreach (['', ' , ,'] as $term) {
            $client->request('GET', $url, ['term' => $term]);

            $this->assertResponseIsSuccessful();
            self::assertSame(['locations' => []], json_decode($client->getResponse()->getContent(), true));
        }
        $client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }

    #[DataProvider('urls')]
    public function testTermsStartAtTheFirstIndex(string $url, string $method): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $model = $this->createMock(SuggestLocationModel::class);
        $model->expects($this->once())->method($method)->with(['Berlin', 'Germany'])->willReturn(['locations' => []]);
        static::getContainer()->set(SuggestLocationModel::class, $model);

        $client->request('GET', $url, ['term' => ', Berlin, , Germany']);

        $this->assertResponseIsSuccessful();
    }
}
