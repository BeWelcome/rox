<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The front page for visitors: Log in opens a panel with the login form, and the two corners of the hero photo
 * hold the cards that turn.
 */
#[Group('integration')]
class FrontPageTest extends WebTestCase
{
    public function testVisitorsSeeTheFrontPageWithTheLoginPanel(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.fp-hero'));
        self::assertCount(1, $crawler->filter('.fp-top .js-home-login-open[aria-controls="home-login"]'));
        self::assertCount(1, $crawler->filter('#home-login form#home-login-form input[name="_username"]'));
        self::assertCount(1, $crawler->filter('#home-login input[name="_password"]'));
        self::assertCount(2, $crawler->filter('.fp-hero .js-fp-slot'));
        self::assertCount(1, $crawler->filter('.fp-hero__facts .js-fp-count[data-count]'));
        self::assertCount(1, $crawler->filter('.js-fp-slot .fp-float--request.is-active'));
        self::assertCount(1, $crawler->filter('.js-fp-slot .fp-float--host.is-active'));
        self::assertGreaterThan(5, $crawler->filter('.js-fp-slot .fp-note')->count());
        self::assertCount(1, $crawler->filter('.fp-community a[href$="/about/getactive"]'));
        self::assertCount(5, $crawler->filter('.js-fp-how .fp-step'));
        self::assertCount(2, $crawler->filter('.js-fp-how .fp-chat .fp-bubble'));
    }
}
