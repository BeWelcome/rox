<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Choosing the site's language goes back to the page it was chosen on, or to the home page without a referrer
 * (an address typed or bookmarked) and when the referrer is not a page of the site (rox #550). A member keeps the language (member.Locale): the next login shows the site in it.
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

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function trickyReferrersOfThisSite(): iterable
    {
        // a browser would go to evil.example: the backslash is a slash to it
        yield 'a backslash' => ['http://evil.example\\@localhost/about', 'http://localhost/about'];
        // //evil.example/x on its own is an address without a scheme
        yield 'a double slash' => ['http://localhost//evil.example/x?page=2', 'http://localhost/evil.example/x?page=2'];
    }

    #[DataProvider('trickyReferrersOfThisSite')]
    public function testTheRedirectStaysOnThisSite(string $referrer, string $redirect): void
    {
        $client = static::createClient();
        $client->request('GET', '/rox/in/fr', server: ['HTTP_REFERER' => $referrer]);

        $this->assertResponseRedirects($redirect);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function foreignReferrers(): iterable
    {
        yield 'another site' => ['https://evil.example/x'];
        yield 'a host that starts like ours' => ['http://localhost.evil.example/about'];
        yield 'our host as user name' => ['http://localhost@evil.example/about'];
        yield 'without a scheme' => ['//evil.example/x'];
        yield 'not a web address' => ['javascript:alert(1)'];
    }

    #[DataProvider('foreignReferrers')]
    public function testAReferrerFromElsewhereLeadsToTheHomePage(string $referrer): void
    {
        $client = static::createClient();
        $client->request('GET', '/rox/in/fr', server: ['HTTP_REFERER' => $referrer]);

        $this->assertResponseRedirects('http://localhost/');
        self::assertSame('fr', $client->getRequest()->getSession()->get('_locale'));
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
