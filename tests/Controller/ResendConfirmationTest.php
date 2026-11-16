<?php

namespace App\Tests\Controller;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * A member whose confirmation mail got lost asks for it again on the confirm page (the bell's "Confirm your email"),
 * as the logged-in member only, however they logged in.
 */
#[Group('integration')]
class ResendConfirmationTest extends WebTestCase
{
    private const string PASSWORD = 'StrongPassword123!';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testANewMemberGetsTheMailAgainFromTheConfirmPage(): void
    {
        $member = $this->signUp();

        $this->resendFromTheConfirmPage($member);
    }

    public function testAMemberWhoLoggedInWithTheEmailAddressGetsTheMailAgain(): void
    {
        $member = $this->signUp();
        $this->client->request('GET', '/logout');
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('#main-login-form')->form([
            '_username' => $member->getEmail(),
            '_password' => self::PASSWORD,
        ]);
        $this->client->submit($form);
        self::assertResponseRedirects();

        $this->resendFromTheConfirmPage($member);
    }

    public function testANewMemberSeesTheBellWithTheSetup(): void
    {
        $member = $this->signUp();

        foreach (['/', '/members/' . $member->getUsername()] as $url) {
            $crawler = $this->client->request('GET', $url);
            self::assertResponseIsSuccessful();
            self::assertCount(1, $crawler->filter('#notices_menu_toggle'), 'no bell on ' . $url);
            self::assertCount(1, $crawler->filter('#notices_menu a[href$="/members/' . $member->getUsername() . '/finalize"]'));
        }
    }

    public function testANewMemberGetsTheMailAgainFromTheSetupPage(): void
    {
        // the bell leads a new member to the setup, which also asks for the registration key
        $member = $this->signUp();
        $crawler = $this->client->request('GET', '/members/' . $member->getUsername() . '/finalize');
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('form#resend-confirmation button[type=submit]'));
        // a form of its own, outside the setup's: that keeps its one submit button
        self::assertCount(1, $crawler->filter('form[name=signup_form_finalize] [type=submit]'));
        $sent = $this->sentMails();

        $this->client->submit($crawler->filter('form#resend-confirmation')->form());

        self::assertResponseRedirects('/members/' . $member->getUsername() . '/finalize');
        self::assertSame($sent + 1, $this->sentMails());
        self::assertEmailAddressContains(self::getMailerMessage($sent), 'To', $member->getEmail());
        $crawler = $this->client->followRedirect();
        $translator = static::getContainer()->get(TranslatorInterface::class);
        self::assertStringContainsString(
            $translator->trans('flash.signup.resent', ['email' => $member->getEmail()]),
            $crawler->text(),
        );
    }

    public function testOnlyTheMemberCanAskForTheirMail(): void
    {
        $member = $this->signUp();
        $crawler = $this->client->request('GET', '/members/' . $member->getUsername() . '/confirm');
        $token = $crawler->filter('form#resend-confirmation input[name=_token]')->attr('value');

        $this->client->loginUser($this->member('member-2'));
        $sent = $this->sentMails();
        $this->client->request('POST', '/members/' . $member->getUsername() . '/resend', ['_token' => $token]);

        self::assertResponseStatusCodeSame(403);
        self::assertSame($sent, $this->sentMails());
    }

    public function testTheMailIsOnlySentOnAPostWithTheToken(): void
    {
        $member = $this->signUp();
        $url = '/members/' . $member->getUsername() . '/resend';
        $sent = $this->sentMails();

        // an old link leads to the confirm page
        $this->client->request('GET', $url);
        self::assertResponseRedirects('/members/' . $member->getUsername() . '/confirm');

        $this->client->request('POST', $url, ['_token' => 'wrong']);
        self::assertResponseStatusCodeSame(403);
        self::assertSame($sent, $this->sentMails());
    }

    public function testAConfirmedAddressGetsNoMail(): void
    {
        $member = $this->signUp();
        $crawler = $this->client->request('GET', '/members/' . $member->getUsername() . '/confirm');
        $token = $crawler->filter('form#resend-confirmation input[name=_token]')->attr('value');
        $this->reload($member)->setRegistrationKey(null);
        $this->entityManager()->flush();
        $sent = $this->sentMails();

        $this->client->request('POST', '/members/' . $member->getUsername() . '/resend', ['_token' => $token]);

        self::assertResponseRedirects('/members/' . $member->getUsername());
        self::assertSame($sent, $this->sentMails());
    }

    public function testTheSetupPageLeadsAMemberWithAnUnconfirmedAddressToTheConfirmPage(): void
    {
        // the setup is done, the address isn't confirmed yet: no mail on the way, the confirm page with its button
        $member = $this->signUp();
        $this->reload($member)->setStatus(MemberStatusType::ACCOUNT_ACTIVATED);
        $this->entityManager()->flush();
        $sent = $this->sentMails();

        $this->client->request('GET', '/members/' . $member->getUsername() . '/finalize');

        self::assertResponseRedirects('/members/' . $member->getUsername() . '/confirm');
        self::assertSame($sent, $this->sentMails());
    }

    public function testTheLoginPageHasNoResendLink(): void
    {
        $crawler = $this->client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('a[href$="/resend"]'));
    }

    private function resendFromTheConfirmPage(Member $member): void
    {
        // the setup is done, the address waits for its confirmation: the bell leads to the confirm page
        $this->reload($member)->setStatus(MemberStatusType::ACCOUNT_ACTIVATED);
        $this->entityManager()->flush();
        $crawler = $this->client->request('GET', '/members/' . $member->getUsername() . '/confirm');
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('form#resend-confirmation button[type=submit]'));
        // a form of its own, outside the confirmation's: that keeps its one submit button
        self::assertCount(1, $crawler->filter('form[name=confirm_email_address_form] [type=submit]'));

        $sent = $this->sentMails();
        $this->client->submit($crawler->filter('form#resend-confirmation')->form());

        self::assertResponseRedirects('/members/' . $member->getUsername() . '/confirm');
        self::assertSame($sent + 1, $this->sentMails());
        $mail = self::getMailerMessage($sent);
        self::assertInstanceOf(Email::class, $mail);
        self::assertEmailAddressContains($mail, 'To', $member->getEmail());
        $key = (string) $this->reload($member)->getRegistrationKey();
        self::assertEmailTextBodyContains($mail, $key);
        // the key also for the confirmation page, as in the first mail: in the link and on its own
        self::assertGreaterThanOrEqual(2, substr_count((string) $mail->getHtmlBody(), $key));
        // no hyphen that mail programs put into the address or the key
        self::assertEmailHtmlBodyNotContains($mail, 'hyphens: auto');

        $crawler = $this->client->followRedirect();
        $translator = static::getContainer()->get(TranslatorInterface::class);
        self::assertStringContainsString(
            $translator->trans('flash.signup.resent', ['email' => $member->getEmail()]),
            $crawler->text(),
        );
    }

    /**
     * The mails sent so far in the test (the sign-up's included).
     */
    private function sentMails(): int
    {
        return \count(self::getMailerMessages());
    }

    private function signUp(): Member
    {
        $crawler = $this->client->request('GET', '/signup');
        $form = $crawler->filter('input[type="submit"]')->form();
        $username = 'resend' . substr(uniqid(), -8);
        $form['signup_form[username]'] = $username;
        $form['signup_form[email]'] = $username . '@example.com';
        $form['signup_form[password]'] = self::PASSWORD;
        $form['signup_form[terms_privacy]'] = '1';
        $this->client->submit($form);
        self::assertResponseRedirects();

        return $this->member($username);
    }

    private function member(string $username): Member
    {
        return $this->entityManager()->getRepository(Member::class)->findOneBy(['username' => $username]);
    }

    private function reload(Member $member): Member
    {
        $this->entityManager()->clear();

        return $this->member($member->getUsername());
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
