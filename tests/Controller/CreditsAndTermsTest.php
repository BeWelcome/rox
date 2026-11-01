<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The credits name Znuny (support's ticketing system) and show HTML Purifier's badge from the site itself; the terms
 * give BeVolunteer's registration number as the register has it.
 */
#[Group('integration')]
class CreditsAndTermsTest extends WebTestCase
{
    public function testTheCredits(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/about/credits');

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('a[href="https://www.znuny.org/"]'));
        self::assertCount(0, $crawler->filter('img[src^="https://htmlpurifier.org"]'));
    }

    public function testTheTermsGiveTheRegistrationNumber(): void
    {
        $client = static::createClient();
        foreach (['en', 'fr'] as $locale) {
            $client->request('GET', '/rox/in/' . $locale);
            $client->request('GET', '/terms');

            $this->assertResponseIsSuccessful();
            self::assertStringContainsString('W353003546', (string) $client->getResponse()->getContent(), $locale);
        }
    }
}
