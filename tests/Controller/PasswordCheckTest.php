<?php

namespace App\Tests\Controller;

use App\Repository\MemberRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * The password strength check of the signup and change password forms: their script posts username, email address
 * and password. Other requests don't fail.
 */
#[Group('integration')]
class PasswordCheckTest extends WebTestCase
{
    public function testThePostedPasswordIsScored(): void
    {
        $client = static::createClient();

        $client->request('POST', '/password/check/', [
            'username' => 'member-2',
            'email' => 'member-2@example.test',
            'password' => 'correct horse battery staple',
        ]);

        $this->assertResponseIsSuccessful();
        self::assertSame(4, json_decode((string) $client->getResponse()->getContent(), true)['score']);
    }

    public function testAPostWithoutFieldsScoresAnEmptyPassword(): void
    {
        $client = static::createClient();

        $client->request('POST', '/password/check/');

        $this->assertResponseIsSuccessful();
        self::assertSame(0, json_decode((string) $client->getResponse()->getContent(), true)['score']);
    }

    /**
     * The sign-up form may still wait for the check when it is sent. The session the check came with is gone by then
     * (the sign-up logged the member in with a new one): a check that touched the session got a new, empty one from
     * PHP, and its cookie replaced the member's.
     */
    public function testTheCheckLeavesTheSessionAlone(): void
    {
        $client = static::createClient();
        $name = static::getContainer()->get('session.factory')->createSession()->getName();
        $client->getCookieJar()->set(new Cookie($name, 'zztest-a-session-that-is-gone'));

        $client->request('POST', '/password/check/', ['password' => 'correct horse battery staple']);

        $this->assertResponseIsSuccessful();
        $session = $client->getRequest()->getSession();
        self::assertInstanceOf(Session::class, $session);
        self::assertSame(0, $session->getUsageIndex(), 'the check read or wrote the session');
        self::assertSame([], array_filter(
            $client->getResponse()->headers->getCookies(),
            static fn ($cookie): bool => $cookie->getName() === $name,
        ));
    }

    public function testAMemberChangingTheirPasswordGetsTheScoreToo(): void
    {
        $client = static::createClient();
        $client->loginUser(static::getContainer()->get(MemberRepository::class)->loadUserByIdentifier('member-2'));

        $client->request('POST', '/password/check/', ['username' => 'member-2', 'password' => 'member-2']);

        $this->assertResponseIsSuccessful();
        self::assertSame(0, json_decode((string) $client->getResponse()->getContent(), true)['score']);
    }

    public function testOpeningTheUrlIsNotAllowed(): void
    {
        $client = static::createClient();

        $client->request('GET', '/password/check/');

        $this->assertResponseStatusCodeSame(405);
    }
}
