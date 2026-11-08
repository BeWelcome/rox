<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Logins are limited per member across all addresses and per address; signups per address; resent confirmation
 * mails per member. The limiter counts live in the kernel, so the client doesn't reboot it between requests.
 */
#[Group('integration')]
final class RateLimitsTest extends WebTestCase
{
    private const string PASSWORD = 'password';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
    }

    public function testWrongPasswordsFromManyAddressesLockTheMemberOut(): void
    {
        $email = $this->member('member-2')->getEmail();
        // ten guesses, each from another address, half by username and half by email address
        for ($i = 1; $i <= 10; ++$i) {
            $this->login(0 === $i % 2 ? 'member-2' : $email, 'wrong-' . $i, '10.0.1.' . $i);
            self::assertTrue($this->failed(), 'guess ' . $i . ' is a failed login');
        }

        $this->login('member-2', self::PASSWORD, '10.0.2.1');

        self::assertTrue($this->failed(), 'the right password from a new address is refused too');
        $crawler = $this->client->followRedirect();
        $error = $crawler->filter('.p-login__error')->text();
        $texts = array_map(fn (int $minutes): string => $this->trans('login.too.many.attempts', ['minutes' => $minutes]), range(1, 15));
        self::assertContains($error, $texts, 'the page asks to try again within the 15 minutes');
    }

    public function testASuccessfulLoginClearsOnlyTheMembersCount(): void
    {
        for ($i = 1; $i <= 9; ++$i) {
            $this->login('member-2', 'wrong-' . $i, '10.0.3.1');
        }
        $this->login('member-2', self::PASSWORD, '10.0.3.1');
        self::assertFalse($this->failed(), 'nine wrong guesses still allow the right password');

        // the member's count starts over: nine more wrong guesses and the member still gets in
        $this->client->getCookieJar()->clear();
        for ($i = 1; $i <= 9; ++$i) {
            $this->login('member-2', 'wrong-' . $i, '10.0.4.1');
        }
        $this->login('member-2', self::PASSWORD, '10.0.4.1');
        self::assertFalse($this->failed());

        // the first address kept its count (9 of 25, successful logins don't count), so 16 more failed logins use it up
        $this->client->getCookieJar()->clear();
        for ($i = 1; $i <= 16; ++$i) {
            $this->login('nobody-' . $i, 'wrong', '10.0.3.1');
        }
        $this->login('member-2', self::PASSWORD, '10.0.3.1');
        self::assertTrue($this->failed(), 'logging into one account did not reset the address');
    }

    public function testOneAddressCantTryManyAccounts(): void
    {
        for ($i = 1; $i <= 25; ++$i) {
            $this->login('nobody-' . $i, 'wrong', '10.0.5.1');
        }

        $this->login('member-2', self::PASSWORD, '10.0.5.1');
        self::assertTrue($this->failed());

        $this->client->getCookieJar()->clear();
        $this->login('member-2', self::PASSWORD, '10.0.5.2');
        self::assertFalse($this->failed(), 'other addresses still log in');
    }

    public function testTooManySignupsFromOneAddressAreRefused(): void
    {
        for ($i = 1; $i <= 10; ++$i) {
            $this->signup('limit' . $i, '10.0.6.1');
            self::assertResponseStatusCodeSame(200, 'submission ' . $i . ' shows the form with its errors');
        }

        $crawler = $this->signup('limit11', '10.0.6.1');

        self::assertResponseStatusCodeSame(429);
        self::assertStringContainsString($this->trans('signup.error.too.many'), $crawler->filter('form')->text());

        $this->signup('limit12', '10.0.6.2');
        self::assertResponseStatusCodeSame(200, 'other addresses can still sign up');
    }

    public function testTheConfirmationMailIsResentThreeTimesAnHour(): void
    {
        // the page is for logged-in members whose last login name is their own
        $this->login('member-2', self::PASSWORD, '10.0.7.1');
        self::assertFalse($this->failed());

        for ($i = 1; $i <= 3; ++$i) {
            $this->client->request('GET', '/members/member-2/resend');
            self::assertResponseIsSuccessful('mail ' . $i . ' is sent');
        }

        $this->client->request('GET', '/members/member-2/resend');

        self::assertResponseRedirects('/members/member-2');
        $crawler = $this->client->followRedirect();
        self::assertStringContainsString($this->trans('flash.signup.resend.too.many'), $crawler->filter('body')->text());
    }

    private function login(string $username, string $password, string $address): void
    {
        $this->client->request(
            'POST',
            '/login_check',
            ['_username' => $username, '_password' => $password, '_csrf_token' => 'csrf-token'],
            [],
            ['REMOTE_ADDR' => $address, 'HTTP_SEC_FETCH_SITE' => 'same-origin'],
        );
    }

    /**
     * A failed login goes back to the login page, a successful one to the start page.
     */
    private function failed(): bool
    {
        self::assertResponseRedirects();

        return str_ends_with((string) $this->client->getResponse()->headers->get('Location'), '/login');
    }

    /**
     * Submits the signup form with a too short password, so no account is created.
     */
    private function signup(string $username, string $address): Crawler
    {
        $crawler = $this->client->request('GET', '/signup', [], [], ['REMOTE_ADDR' => $address]);
        $form = $crawler->filter('input[type="submit"]')->form();
        $form['signup_form[username]'] = $username . substr(uniqid(), -6);
        $form['signup_form[email]'] = $username . substr(uniqid(), -6) . '@example.com';
        $form['signup_form[password]'] = '123';
        $form['signup_form[terms_privacy]'] = '1';

        return $this->client->submit($form, [], ['REMOTE_ADDR' => $address]);
    }

    private function member(string $username): Member
    {
        return static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)
            ->findOneBy(['username' => $username]);
    }

    /**
     * A text as the page shows it: the test database may have no translations (CI), then the key.
     *
     * @param array<string, int|string> $parameters
     */
    private function trans(string $id, array $parameters = []): string
    {
        return static::getContainer()->get(TranslatorInterface::class)->trans($id, $parameters);
    }
}
