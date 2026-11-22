<?php

namespace App\Tests\Entity;

use App\Entity\Address;
use App\Entity\Location;
use PHPUnit\Framework\TestCase;

/**
 * An address keeps the country and the region of its place, which the places pages look members up by.
 */
final class AddressPlaceTest extends TestCase
{
    public function testAnAddressTakesCountryAndRegionFromItsLocation(): void
    {
        $address = new Address();
        self::assertNull($address->getCountryCode());

        $address->setLocation($this->location('FR', '11'));

        self::assertSame('FR', $address->getCountryCode());
        self::assertSame('11', $address->getRegionCode());
    }

    public function testAnotherLocationChangesThem(): void
    {
        $address = new Address()->setLocation($this->location('FR', '11'));

        $address->setLocation($this->location('DE', null));

        self::assertSame('DE', $address->getCountryCode());
        self::assertNull($address->getRegionCode());
    }

    private function location(string $country, ?string $region): Location
    {
        $location = $this->createStub(Location::class);
        $location->method('getCountryId')->willReturn($country);
        $location->method('getAdmin1Id')->willReturn($region);

        return $location;
    }
}
