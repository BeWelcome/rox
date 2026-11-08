<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The login forms carry the stateless "authenticate" token: a login is only accepted from the site itself.
 */
#[Group('integration')]
final class LoginCsrfTest extends WebTestCase
{
    public function testALoginFromTheSiteIsAccepted(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $token = $crawler->filter('#main-login-form input[name="_csrf_token"]')->attr('value');

        $client->request(
            'POST',
            '/login_check',
            ['_username' => 'member-2', '_password' => 'password', '_csrf_token' => $token],
            [],
            ['HTTP_SEC_FETCH_SITE' => 'same-origin'],
        );

        self::assertResponseRedirects();
        self::assertStringEndsNotWith('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    /**
     * @return iterable<string, array{array<string, string>, array<string, string>}>
     */
    public static function refusedLogins(): iterable
    {
        yield 'from another site' => [['_csrf_token' => 'csrf-token'], ['HTTP_SEC_FETCH_SITE' => 'cross-site']];
        yield 'from another origin' => [['_csrf_token' => 'csrf-token'], ['HTTP_ORIGIN' => 'https://evil.example']];
        yield 'without a token' => [[], ['HTTP_SEC_FETCH_SITE' => 'same-origin']];
        yield 'without origin info' => [['_csrf_token' => 'csrf-token'], []];
    }

    /**
     * @param array<string, string> $token
     * @param array<string, string> $headers
     */
    #[DataProvider('refusedLogins')]
    public function testOtherLoginsAreRefused(array $token, array $headers): void
    {
        $client = static::createClient();

        $client->request('POST', '/login_check', ['_username' => 'member-2', '_password' => 'password'] + $token, [], $headers);

        self::assertResponseRedirects();
        self::assertStringEndsWith('/login', (string) $client->getResponse()->headers->get('Location'));
        $client->followRedirect();
        self::assertSelectorExists('.p-login__error');
    }
}
