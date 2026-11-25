<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\PasswordReset;
use App\Model\PasswordModel;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The password reset as a visitor uses it: both forms are submitted as the page renders them, CSRF token
 * included.
 */
#[Group('integration')]
class PasswordResetControllerTest extends WebTestCase
{
    public function testRequestingAResetSendsTheLink(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/resetpassword');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('#reset-password-form')->form([
            'reset_password_request_form[username]' => 'bwadmin',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/login');
        self::assertEmailCount(1);
        $member = $this->member('bwadmin');
        self::assertNotNull(
            $this->entityManager()->getRepository(PasswordReset::class)->findOneBy(['member' => $member])
        );
    }

    public function testAnUnknownUsernameGetsTheFormBackWithItsMessage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/resetpassword');
        $form = $crawler->filter('#reset-password-form')->form([
            'reset_password_request_form[username]' => 'zztest-nobody-by-this-name',
        ]);

        $crawler = $client->submit($form);

        self::assertContains($client->getResponse()->getStatusCode(), [200, 422]);
        self::assertCount(1, $crawler->filter('#reset-password-form'));
        self::assertSame(
            [static::getContainer()->get(TranslatorInterface::class)->trans('reset.password.error')],
            $crawler->filter('.p-login__error-title')->each(static fn ($message) => $message->text()),
        );
        self::assertEmailCount(0);
    }

    public function testAskingAgainSendsANewLinkAndEndsTheOneBefore(): void
    {
        $client = static::createClient();
        $first = static::getContainer()->get(PasswordModel::class)->generatePasswordResetToken($this->member('bwadmin'));
        $crawler = $client->request('GET', '/resetpassword');
        $form = $crawler->filter('#reset-password-form')->form([
            'reset_password_request_form[username]' => 'bwadmin',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/login');
        self::assertEmailCount(1);
        $resets = $this->entityManager()->getRepository(PasswordReset::class)->findBy(['member' => $this->member('bwadmin')]);
        self::assertCount(1, $resets);
        $second = $resets[0]->getToken();
        self::assertNotSame($first, $second);

        // the link of the first mail no longer opens the form, the new one does
        $client->request('GET', '/resetpassword/bwadmin/' . $first);
        $this->assertResponseRedirects('/resetpassword');
        $client->request('GET', '/resetpassword/bwadmin/' . $second);
        $this->assertResponseIsSuccessful();
    }

    public function testASecondMailWithinTheHourIsNotSent(): void
    {
        $client = static::createClient();
        // the limit counts as long as the kernel lives, and so do the mails sent
        $client->disableReboot();
        $ask = static function () use ($client) {
            $crawler = $client->request('GET', '/resetpassword');

            return $client->submit($crawler->filter('#reset-password-form')->form([
                'reset_password_request_form[username]' => 'bwadmin',
            ]));
        };

        $ask();
        $this->assertResponseRedirects('/login');
        self::assertCount(1, self::getMailerMessages());
        $token = $this->entityManager()->getRepository(PasswordReset::class)->findOneBy(['member' => $this->member('bwadmin')])->getToken();

        $crawler = $ask();
        self::assertContains($client->getResponse()->getStatusCode(), [200, 422]);
        self::assertSame(
            [static::getContainer()->get(TranslatorInterface::class)->trans('reset.password.too.many')],
            $crawler->filter('.p-login__error-title')->each(static fn ($message) => $message->text()),
        );
        self::assertCount(1, self::getMailerMessages(), 'no second mail');
        // the link of the first mail is still the member's
        $resets = $this->entityManager()->getRepository(PasswordReset::class)->findBy(['member' => $this->member('bwadmin')]);
        self::assertCount(1, $resets);
        self::assertSame($token, $resets[0]->getToken());

        // another member's mail is not held back by it
        $crawler = $client->request('GET', '/resetpassword');
        $client->submit($crawler->filter('#reset-password-form')->form(['reset_password_request_form[username]' => 'member-2']));
        $this->assertResponseRedirects('/login');
        self::assertCount(2, self::getMailerMessages());
    }

    public function testTheLinkSetsANewPassword(): void
    {
        $client = static::createClient();
        $member = $this->member('bwadmin');
        $token = static::getContainer()->get(PasswordModel::class)->generatePasswordResetToken($member);

        $crawler = $client->request('GET', '/resetpassword/bwadmin/' . $token);
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('#reset-password-form')->form([
            'reset_password_form[password][first]' => 'a-new-password-1',
            'reset_password_form[password][second]' => 'a-new-password-1',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/login');
        $member = $this->member('bwadmin');
        self::assertTrue(
            static::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($member, 'a-new-password-1')
        );
    }

    public function testAnExpiredLinkShowsTheTranslatedMessage(): void
    {
        $client = static::createClient();
        $member = $this->member('bwadmin');
        $token = static::getContainer()->get(PasswordModel::class)->generatePasswordResetToken($member);
        $this->entityManager()->getConnection()->executeStatement(
            'UPDATE passwordreset SET `generated` = ? WHERE token = ?',
            [new DateTime('-3 days')->format('Y-m-d H:i:s'), $token],
        );
        // the request reads the reset again: not the one still held with the date it was made
        $this->entityManager()->clear();

        $client->request('GET', '/resetpassword/bwadmin/' . $token);

        $this->assertResponseRedirects('/resetpassword');
        $session = $client->getRequest()->getSession();
        self::assertInstanceOf(FlashBagAwareSessionInterface::class, $session);
        self::assertSame(
            [static::getContainer()->get(TranslatorInterface::class)->trans('flash.reset.password.invalid')],
            $session->getFlashBag()->peek('error'),
        );
    }

    private function member(string $username): Member
    {
        $this->entityManager()->clear();
        $member = $this->entityManager()->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
