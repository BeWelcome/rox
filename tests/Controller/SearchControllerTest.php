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
}
