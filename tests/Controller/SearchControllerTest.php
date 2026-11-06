<?php

namespace App\Tests\Controller;

use App\Repository\MemberRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('integration')]
class SearchControllerTest extends WebTestCase
{
    public function testLoggedInSearchByLocationPageTitleNamesPageBeforeBrand(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2');

        self::assertNotNull($member);

        $client->loginUser($member);
        $crawler = $client->request('GET', '/search/locations');

        $this->assertResponseIsSuccessful();
        self::assertSame('search.locations | BeWelcome', trim($crawler->filter('title')->text()));
    }

    public function testSearchByLocationPageShowsSearchBarQuickFiltersAndOptionsPanel(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2');
        $client->loginUser($member);

        $crawler = $client->request('GET', '/search/locations');

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('form.p-search input#search_location'));
        self::assertCount(1, $crawler->filter('.p-search__bar select#search_distance'));
        self::assertCount(4, $crawler->filter('.p-search__pills .js-search-pill'));
        self::assertCount(1, $crawler->filter('.p-search-options input#search_accommodation_yes'));
        self::assertCount(1, $crawler->filter('.p-search-options select#search_can_host'));
    }

    public function testARadiusSearchFindsTheMembersOfThePlace(): void
    {
        $client = static::createClient();
        $connection = static::getContainer()->get('doctrine.dbal.default_connection');
        $found = $connection->fetchAssociative(<<<'SQL'
                SELECT m.Username AS username, l.geoname_id, l.name, ST_Y(l.coordinates) AS latitude, ST_X(l.coordinates) AS longitude
                FROM member m
                JOIN address a ON a.member_id = m.id AND a.active = 1
                JOIN location l ON l.geoname_id = a.location
                WHERE m.Status = 'Active' AND m.Username <> 'member-2'
                ORDER BY m.id LIMIT 1
            SQL);
        self::assertNotFalse($found, 'the test fixtures contain an active member with an address');

        $member = static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2');
        $client->loginUser($member);
        $crawler = $client->request('GET', '/search/locations');
        $form = $crawler->filter('.p-search__bar .js-search-find')->form([
            'search[location]' => $found['name'],
            'search[location_fullname]' => $found['name'],
            'search[location_name]' => $found['name'],
            'search[location_geoname_id]' => (string) $found['geoname_id'],
            'search[location_latitude]' => (string) $found['latitude'],
            'search[location_longitude]' => (string) $found['longitude'],
            'search[distance]' => '20',
            'search[items]' => '100',
            'search[last_active]' => '2400',
        ]);
        $crawler = $client->submit($form);

        $this->assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filter('main a[href*="/members/' . $found['username'] . '"]')->count(), 'the member of the place is found');
    }

    public function testTheHomePageMemberSearchOpensProfiles(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2');
        $client->loginUser($member);

        // the member field goes to the profile itself (landing.js); there's no search page for members
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        self::assertSame('/members/__username__', $crawler->filter('form.l-hello__member-form')->attr('data-profile-url'));

        $client->request('GET', '/search/members');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testPlaceSearchFromTheHomePageOpensTheSearchForThatPlace(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2');
        $client->loginUser($member);

        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('#tiny_updateMap')->form([
            'tiny[location]' => 'Berlin, Land Berlin, Germany',
            'tiny[location_fullname]' => 'Berlin, Land Berlin, Germany',
            'tiny[location_name]' => 'Berlin',
            'tiny[location_geoname_id]' => '2950159',
            'tiny[location_latitude]' => '52.52437',
            'tiny[location_longitude]' => '13.41053',
        ]);
        $crawler = $client->submit($form);

        $this->assertResponseIsSuccessful();
        self::assertSame('Berlin, Land Berlin, Germany', $crawler->filter('form.p-search input#search_location')->attr('value'));
    }
}
