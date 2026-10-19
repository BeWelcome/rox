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

    public function testSearchMembersByExactUsernameRedirectsToTheProfile(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2');
        $client->loginUser($member);

        $client->request('GET', '/search/members', ['username' => 'member-5']);

        $this->assertResponseRedirects('/members/member-5');
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
