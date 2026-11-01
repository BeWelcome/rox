<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Search engines get one address per page: its canonical tag without query string (built from the route, so
 * defaults don't make a second address), and the old FAQ address moved permanently to /faq, which the site links to.
 */
#[Group('integration')]
class CanonicalAndFaqTest extends WebTestCase
{
    public function testAPageNamesItsAddressWithoutQueryString(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/faq/1?gclid=zztest&utm_source=zztest');

        $this->assertResponseIsSuccessful();
        self::assertSame(['http://localhost/faq'], $crawler->filter('head link[rel=canonical]')->each(
            static fn ($link): string => (string) $link->attr('href')
        ));
    }

    public function testTheOldFaqAddressMovedPermanently(): void
    {
        $client = static::createClient();
        $client->request('GET', '/about/faq');

        $this->assertResponseRedirects('/faq', 301);
    }

    public function testThePagesLinkToTheFaq(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/about');

        $this->assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('a[href$="/about/faq"]'));
        self::assertGreaterThan(0, $crawler->filter('footer a[href$="/faq"]')->count());
    }
}
