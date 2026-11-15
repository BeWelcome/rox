<?php

namespace App\Tests\Controller;

use App\Doctrine\MemberStatusType;
use App\Entity\Location;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Mime\Email;

/**
 * Every way from the sign-up to an active account: the email address confirmed (the link of the mail, or its key on
 * the confirm page or the finish setup page) and the setup done, in either order, also with the resent mail. In each
 * the member ends Active and the registration key is gone.
 */
#[Group('integration')]
class SignupActivationTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testTheSetupWithTheKeyActivatesTheAccount(): void
    {
        $member = $this->signUp();

        $this->finishSetup($member, $this->keyFromMail());

        $this->assertActiveWithoutKey($member);
    }

    public function testTheLinkFirstThenTheSetup(): void
    {
        $member = $this->signUp();

        $this->confirmWithTheLink($member);
        self::assertSame(MemberStatusType::MAIL_CONFIRMED, $this->reload($member)->getStatus());
        self::assertNull($this->reload($member)->getRegistrationKey());
        $this->finishSetup($member, null);

        $this->assertActiveWithoutKey($member);
    }

    public function testTheSetupWithoutTheKeyThenTheLink(): void
    {
        $member = $this->signUp();

        $this->finishSetup($member, '');
        self::assertSame(MemberStatusType::ACCOUNT_ACTIVATED, $this->reload($member)->getStatus());
        $this->confirmWithTheLink($member);

        $this->assertActiveWithoutKey($member);
    }

    public function testTheSetupWithoutTheKeyThenTheKeyOnTheConfirmPage(): void
    {
        $member = $this->signUp();
        $key = $this->keyFromMail();

        $this->finishSetup($member, '');
        $crawler = $this->client->request('GET', '/members/' . $member->getUsername() . '/confirm');
        self::assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('form[name=confirm_email_address_form]')->form(), [
            'confirm_email_address_form[registration_key]' => $key,
        ]);

        $this->assertActiveWithoutKey($member);
    }

    public function testTheResentMailsLinkThenTheSetup(): void
    {
        $member = $this->signUp();
        $crawler = $this->client->request('GET', '/members/' . $member->getUsername() . '/finalize');
        $this->client->submit($crawler->filter('form#resend-confirmation')->form());

        $this->confirmWithTheLink($member);
        $this->finishSetup($member, null);

        $this->assertActiveWithoutKey($member);
    }

    public function testAWrongKeyOnTheConfirmPageIsRefused(): void
    {
        $member = $this->signUp();
        $this->finishSetup($member, '');

        $crawler = $this->client->request('GET', '/members/' . $member->getUsername() . '/confirm');
        $crawler = $this->client->submit($crawler->filter('form[name=confirm_email_address_form]')->form(), [
            'confirm_email_address_form[registration_key]' => 'not-the-key',
        ]);

        // the form again with the error, nothing confirmed
        self::assertResponseStatusCodeSame(422);
        self::assertCount(1, $crawler->filter('form[name=confirm_email_address_form]'));
        $member = $this->reload($member);
        self::assertSame(MemberStatusType::ACCOUNT_ACTIVATED, $member->getStatus());
        self::assertNotNull($member->getRegistrationKey());
    }

    private function signUp(): Member
    {
        $crawler = $this->client->request('GET', '/signup');
        $form = $crawler->filter('input[type="submit"]')->form();
        $username = 'active' . substr(uniqid(), -8);
        $form['signup_form[username]'] = $username;
        $form['signup_form[email]'] = $username . '@example.com';
        $form['signup_form[password]'] = 'StrongPassword123!';
        $form['signup_form[terms_privacy]'] = '1';
        $this->client->submit($form);
        self::assertResponseRedirects();

        return $this->reload($username);
    }

    /**
     * The newest confirmation mail's link, opened and its form sent, as members do.
     */
    private function confirmWithTheLink(Member $member): void
    {
        $link = $this->linkFromMail($member);
        $crawler = $this->client->request('GET', $link);
        self::assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('form[name=confirm_email_address_form]')->form());
        self::assertResponseRedirects();
    }

    /**
     * @param string|null $key null: the setup page has no key field (the address is confirmed already)
     */
    private function finishSetup(Member $member, ?string $key): Crawler
    {
        $crawler = $this->client->request('GET', '/members/' . $member->getUsername() . '/finalize');
        self::assertResponseIsSuccessful();
        $location = static::getContainer()->get(EntityManagerInterface::class)->getRepository(Location::class)->findOneBy([]);
        $form = $crawler->filter('form[name=signup_form_finalize]')->form();
        if (null !== $key) {
            $form['signup_form_finalize[registration_key]'] = $key;
        }
        $form['signup_form_finalize[name]'] = 'Activation Test';
        $form['signup_form_finalize[birthdate]'] = '1990-05-17';
        $form['signup_form_finalize[gender]'] = 'other';
        $form['signup_form_finalize[mother_tongue]'] = ['en'];
        $form['signup_form_finalize[location][fullname]'] = $location->getName();
        $form['signup_form_finalize[location][geoname_id]'] = (string) $location->getGeonameId();
        $form['signup_form_finalize[location][latitude]'] = (string) $location->getLatitude();
        $form['signup_form_finalize[location][longitude]'] = (string) $location->getLongitude();
        $form['signup_form_finalize[accommodation]'] = 'no';
        $crawler = $this->client->submit($form);
        self::assertResponseRedirects('/members/' . $member->getUsername() . '/edit');

        return $crawler;
    }

    private function linkFromMail(Member $member): string
    {
        $messages = self::getMailerMessages();
        self::assertNotEmpty($messages);
        $mail = end($messages);
        self::assertInstanceOf(Email::class, $mail);
        self::assertSame(1, preg_match('~https?://[^/"\s]+(/members/' . preg_quote($member->getUsername(), '~') . '/confirm/[^"\s<]+)~', (string) $mail->getHtmlBody(), $match));

        return html_entity_decode($match[1]);
    }

    private function keyFromMail(): string
    {
        $messages = self::getMailerMessages();
        $mail = end($messages);
        self::assertInstanceOf(Email::class, $mail);
        self::assertSame(1, preg_match('~/confirm/([A-Za-z0-9_-]+)~', (string) $mail->getHtmlBody(), $match));

        return $match[1];
    }

    private function assertActiveWithoutKey(Member $member): void
    {
        $member = $this->reload($member);
        self::assertSame(MemberStatusType::ACTIVE, $member->getStatus());
        self::assertNull($member->getRegistrationKey());
    }

    private function reload(Member|string $member): Member
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        return $entityManager->getRepository(Member::class)
            ->findOneBy(['username' => $member instanceof Member ? $member->getUsername() : $member]);
    }
}
