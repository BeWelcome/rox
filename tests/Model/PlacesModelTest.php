<?php

namespace App\Tests\Model;

use App\Model\PlacesModel;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Countries are places of location (feature code PCL*); the continent follows from the country code.
 */
#[Group('integration')]
class PlacesModelTest extends KernelTestCase
{
    private PlacesModel $placesModel;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->placesModel = static::getContainer()->get(PlacesModel::class);
    }

    public function testCountryHasItsContinent(): void
    {
        $germany = $this->placesModel->getCountry('de', 'en');

        self::assertNotNull($germany);
        self::assertSame('DE', $germany['code']);
        self::assertSame('Germany', $germany['slug']);
        self::assertSame('EA', $germany['continent']);
        self::assertSame(2921044, $germany['geonameId']);
    }

    public function testCountriesWithMembersAreGroupedByContinent(): void
    {
        $continents = $this->placesModel->getCountriesByContinent('en');

        self::assertArrayHasKey('EA', $continents);
        self::assertContains('DE', array_column($continents['EA'], 'code'));
        foreach ($continents as $continent => $countries) {
            self::assertContains($continent, PlacesModel::CONTINENTS);
            foreach ($countries as $country) {
                self::assertGreaterThan(0, $country['count']);
            }
        }
    }
}
