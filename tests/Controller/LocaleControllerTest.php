<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Choosing the site's language goes back to the page it was chosen on, or to the home page without a referrer
 * (an address typed or bookmarked). A member keeps the language (member.Locale): the next login shows the site in it.
 */
#[Group('integration')]
class LocaleControllerTest extends WebTestCase
{
    public function testWithoutAReferrerTheHomePageFollows(): void
    {
        $client = static::createClient();
        $client->request('GET', '/rox/in/de');

        $this->assertResponseRedirects('http://localhost/');
        self::assertSame('de', $client->getRequest()->getSession()->get('_locale'));
    }

    public function testTheReferrerFollows(): void
    {
        $client = static::createClient();
        $client->request('GET', '/rox/in/fr', server: ['HTTP_REFERER' => 'http://localhost/about']);

        $this->assertResponseRedirects('http://localhost/about');
    }

    public function testAMemberKeepsTheLanguageForTheNextLogin(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        $client->loginUser($member);

        $client->request('GET', '/rox/in/de', server: ['HTTP_REFERER' => 'http://localhost/about']);

        self::assertSame('de', static::getContainer()->get(Connection::class)
            ->fetchOne("SELECT Locale FROM member WHERE Username = 'member-2'"));

        // logged out, in English; logging in again switches to German
        $client->request('GET', '/logout');
        $client->restart();
        $client->request('GET', '/rox/in/en');
        $crawler = $client->request('GET', '/login');
        $client->submit($crawler->filter('#main-login-form')->form(), ['_username' => 'member-2', '_password' => 'password']);

        self::assertSame('de', $client->getRequest()->getSession()->get('_locale'));
        $client->followRedirects();
        $page = $client->request('GET', '/about');
        self::assertSame('de', $page->filter('html')->attr('lang'));
    }
}
