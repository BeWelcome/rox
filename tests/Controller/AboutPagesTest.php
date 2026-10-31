<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The About pages: the page in a card, its menu next to it with the page marked.
 */
#[Group('integration')]
final class AboutPagesTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function pages(): iterable
    {
        yield 'about' => ['/about'];
        yield 'the people' => ['/about/thepeople'];
        yield 'get active' => ['/about/getactive'];
        yield 'comment guidelines' => ['/about/commentguidelines'];
        yield 'credits' => ['/about/credits'];
    }

    #[DataProvider('pages')]
    public function testThePageHasTheAboutLayout(string $uri): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $uri);

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('main.p-about .p-about__body h1'));
        self::assertCount(1, $crawler->filter('.p-about__nav .p-about__link.is-active[aria-current="page"]'));
        // a visitor gets the front page's top bar, not the login form
        self::assertCount(1, $crawler->filter('header.bw-visitor-top .fp-top__join'));
        self::assertCount(0, $crawler->filter('input[name="_password"]'));
    }
}
