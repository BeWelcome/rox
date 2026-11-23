<?php

namespace App\Tests\Controller;

use App\EventListener\LocaleListener;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

/**
 * The language of a visitor: the first page of a visit is in the browser's language already, and a language picked
 * with the switcher, or the member's own at login, is remembered beyond the session: the next visit and the page
 * after logout are in it (rox #206: both were English).
 */
#[Group('integration')]
final class VisitorLanguageTest extends WebTestCase
{
    private const array ITALIAN_BROWSER = ['HTTP_ACCEPT_LANGUAGE' => 'it-IT,it;q=0.9,en;q=0.7'];

    public function testTheFirstPageOfAVisitIsInTheBrowsersLanguage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/about', server: self::ITALIAN_BROWSER);

        $this->assertResponseIsSuccessful();
        self::assertSame('it', $this->pageLanguage($client));
    }

    public function testThePickedLanguageOutlivesTheSession(): void
    {
        $client = static::createClient();
        $client->request('GET', '/rox/in/de', server: self::ITALIAN_BROWSER);

        $cookie = $client->getCookieJar()->get(LocaleListener::COOKIE);
        self::assertInstanceOf(Cookie::class, $cookie);
        self::assertSame('de', $cookie->getValue());
        self::assertGreaterThan(strtotime('+360 days'), (int) $cookie->getExpiresTime());

        // the next visit: the session is gone, the cookie is there
        $client->getCookieJar()->clear();
        $client->getCookieJar()->set(new Cookie(LocaleListener::COOKIE, 'de'));
        $client->request('GET', '/about', server: self::ITALIAN_BROWSER);

        self::assertSame('de', $this->pageLanguage($client));
    }

    public function testACookieWithALanguageTheSiteHasNotIsLeftAside(): void
    {
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie(LocaleListener::COOKIE, 'xx'));

        $client->request('GET', '/about', server: self::ITALIAN_BROWSER);

        self::assertSame('it', $this->pageLanguage($client));
    }

    public function testAfterLogoutThePagesAreInTheMembersLanguage(): void
    {
        $client = static::createClient();
        static::getContainer()->get(Connection::class)
            ->executeStatement("UPDATE member SET Locale = 'fr' WHERE Username = 'member-2'");

        $crawler = $client->request('GET', '/login', server: self::ITALIAN_BROWSER);
        $client->submit(
            $crawler->filter('#main-login-form')->form(),
            ['_username' => 'member-2', '_password' => 'password'],
            self::ITALIAN_BROWSER,
        );
        self::assertSame('fr', $client->getCookieJar()->get(LocaleListener::COOKIE)?->getValue());

        $client->request('GET', '/logout', server: self::ITALIAN_BROWSER);
        $client->followRedirects();
        $client->request('GET', '/about', server: self::ITALIAN_BROWSER);

        self::assertSame('fr', $this->pageLanguage($client));
    }

    private function pageLanguage(KernelBrowser $client): ?string
    {
        return $client->getCrawler()->filter('html')->attr('lang');
    }
}
