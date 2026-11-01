<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Choosing the site's language goes back to the page it was chosen on, or to the home page without a referrer
 * (an address typed or bookmarked).
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
}
