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
}
