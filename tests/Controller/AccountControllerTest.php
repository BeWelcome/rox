<?php

namespace App\Tests\Controller;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Group('integration')]
/**
 * @infection-ignore-all
 */
class AccountControllerTest extends WebTestCase
{
    public function testEmailChangeStoresPendingEmailWithoutChangingCurrentEmail(): void
    {
        $client = static::createClient();
        $member = $this->loginMember($client, 'bwadmin');
        $oldEmail = $member->getEmail();
        $newEmail = 'new-' . uniqid() . '@example.test';

        $this->submitAccountEmailChange($client, $member, $newEmail);

        $this->assertResponseRedirects('/members/bwadmin/account');
        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        $this->assertNotNull($email);
        self::assertEmailAddressContains($email, 'To', $newEmail);
        self::assertEmailAddressNotContains($email, 'To', $oldEmail);

        $member = $this->reloadMember('bwadmin');
        $this->assertSame($oldEmail, $member->getEmail());
        $this->assertSame($newEmail, $member->getNewEmail());
        $this->assertNotNull($member->getRegistrationKey());
    }

    public function testAWrongPasswordChangesNoEmailAddress(): void
    {
        $client = static::createClient();
        $member = $this->loginMember($client, 'bwadmin');

        $this->submitAccountEmailChange($client, $member, 'wrong-' . uniqid() . '@example.test', 'not the password');

        $this->assertResponseStatusCodeSame(422);
        self::assertEmailCount(0);
        $this->assertNull($this->reloadMember('bwadmin')->getNewEmail());
    }

    public function testTheAccountPageLinksToTheEmailChange(): void
    {
        // the address isn't a field of the account form any more
        $client = static::createClient();
        $member = $this->loginMember($client, 'bwadmin');

        $crawler = $client->request('GET', '/members/bwadmin/account');

        $this->assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('input[name="account_edit_form[email]"]'));
        self::assertSame($member->getEmail(), trim($crawler->filter('.p-account-email')->text()));
        self::assertSame('/members/bwadmin/account/email', $crawler->filter('a.p-account-email__change')->attr('href'));

        // in the edit page's side panel the link keeps embed=1: other pages may not be framed
        $crawler = $client->request('GET', '/members/bwadmin/account?embed=1');
        $link = (string) $crawler->filter('a.p-account-email__change')->attr('href');
        self::assertSame('/members/bwadmin/account/email?embed=1', $link);
        $client->request('GET', $link);
        $this->assertResponseIsSuccessful();
        self::assertNotSame('DENY', $client->getResponse()->headers->get('X-Frame-Options'));
    }

    public function testTheEditPagesLockOpensTheEmailChange(): void
    {
        // the email address of the edit page's account card: its lock opens the email change in the side panel
        $client = static::createClient();
        $this->loginMember($client, 'bwadmin');

        $crawler = $client->request('GET', '/members/bwadmin/edit');

        $this->assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filter('.p-pe-account a.p-pe-account__seen.is-locked.js-contact-open[href="/members/bwadmin/account/email"]')->count());
    }

    public function testChangeEmailConfirmationPromotesPendingEmail(): void
    {
        $client = static::createClient();
        $member = $this->loginMember($client, 'bwadmin');
        $newEmail = 'confirmed-' . uniqid() . '@example.test';

        $this->submitAccountEmailChange($client, $member, $newEmail);
        $member = $this->reloadMember('bwadmin');
        $registrationKey = $member->getRegistrationKey();
        $this->assertNotNull($registrationKey);

        $crawler = $client->request('GET', '/members/bwadmin/change/email/' . $registrationKey);
        $this->assertResponseIsSuccessful();
        // the page has other forms too (the layout's), so take the confirmation form by its name
        // not the box that sends the signup's confirmation mail again
        self::assertCount(0, $crawler->filter('#resend-confirmation, .signup-resend'));
        $form = $crawler->filter('form[name="confirm_email_address_form"]')->form();
        $client->submit($form);

        $this->assertResponseRedirects('/members/bwadmin');
        $member = $this->reloadMember('bwadmin');
        $this->assertSame($newEmail, $member->getEmail());
        $this->assertNull($member->getNewEmail());
        $this->assertNull($member->getRegistrationKey());
    }

    public function testEmailChangeRejectsUnconfirmedCurrentEmail(): void
    {
        // the sign-up's address still waits for its confirmation (status Activated: details filled in, mail not)
        $client = static::createClient();
        $member = $this->loginMember($client, 'bwadmin');
        $oldEmail = $member->getEmail();
        $member->setStatus(MemberStatusType::ACCOUNT_ACTIVATED);
        $this->getEntityManager()->flush();

        $this->submitAccountEmailChange($client, $member, 'blocked-' . uniqid() . '@example.test');

        $this->assertResponseStatusCodeSame(422);
        self::assertEmailCount(0);
        self::assertStringContainsString(
            static::getContainer()->get(TranslatorInterface::class)->trans('member.mail.not.confirmed'),
            (string) $client->getResponse()->getContent(),
        );
        $member = $this->reloadMember('bwadmin');
        $this->assertSame($oldEmail, $member->getEmail());
        $this->assertNull($member->getNewEmail());
    }

    public function testAMemberWithAnOldRegistrationKeyChangesTheirAddress(): void
    {
        // for almost every member of the old site the column holds '0' or an empty string: not an unconfirmed address
        $client = static::createClient();
        $member = $this->loginMember($client, 'bwadmin');
        $member->setRegistrationKey('0');
        $this->getEntityManager()->flush();
        $newEmail = 'old-key-' . uniqid() . '@example.test';

        $this->submitAccountEmailChange($client, $member, $newEmail);

        $this->assertResponseRedirects('/members/bwadmin/account');
        self::assertEmailCount(1);
        $this->assertSame($newEmail, $this->reloadMember('bwadmin')->getNewEmail());
    }

    public function testAWrongKeyDoesntChangeTheAddress(): void
    {
        $client = static::createClient();
        $member = $this->loginMember($client, 'bwadmin');
        $oldEmail = $member->getEmail();
        $this->submitAccountEmailChange($client, $member, 'wrong-key-' . uniqid() . '@example.test');

        $crawler = $client->request('GET', '/members/bwadmin/change/email');
        $client->submit($crawler->filter('form[name="confirm_email_address_form"]')->form(), [
            'confirm_email_address_form[registration_key]' => 'not-the-key',
        ]);

        // the form again with the error, the address unchanged
        $this->assertResponseStatusCodeSame(422);
        $this->assertSame($oldEmail, $this->reloadMember('bwadmin')->getEmail());
        $this->assertNotNull($this->reloadMember('bwadmin')->getNewEmail());
    }

    public function testChangeEmailConfirmationRequiresPendingEmail(): void
    {
        $client = static::createClient();
        $member = $this->loginMember($client, 'bwadmin');
        $oldEmail = $member->getEmail();
        $registrationKey = 'missing-new-email-key';
        $member->setRegistrationKey($registrationKey);
        $member->setNewEmail(null);
        $this->getEntityManager()->flush();

        $client->request('GET', '/members/bwadmin/change/email/' . $registrationKey);

        $this->assertResponseRedirects('/members/bwadmin');
        $member = $this->reloadMember('bwadmin');
        $this->assertSame($oldEmail, $member->getEmail());
        $this->assertNull($member->getNewEmail());
        $this->assertSame($registrationKey, $member->getRegistrationKey());
    }

    public function testEmailChangeRejectsEmailAlreadyInUse(): void
    {
        $client = static::createClient();
        $member = $this->loginMember($client, 'bwadmin');
        $oldEmail = $member->getEmail();
        $duplicateEmail = $this->reloadMember('member-6')->getEmail();

        $this->submitAccountEmailChange($client, $member, $duplicateEmail);

        $this->assertResponseStatusCodeSame(422);
        $member = $this->reloadMember('bwadmin');
        $this->assertSame($oldEmail, $member->getEmail());
        $this->assertNull($member->getNewEmail());
        $this->assertNull($member->getRegistrationKey());
    }

    public function testEmailChangeRejectsPendingEmailAlreadyInUse(): void
    {
        $client = static::createClient();
        $member = $this->loginMember($client, 'bwadmin');
        $oldEmail = $member->getEmail();
        $duplicateEmail = 'pending-' . uniqid() . '@example.test';
        $otherMember = $this->reloadMember('member-6');
        $otherMember->setNewEmail($duplicateEmail);
        $this->getEntityManager()->flush();

        $this->submitAccountEmailChange($client, $member, $duplicateEmail);

        $this->assertResponseStatusCodeSame(422);
        $member = $this->reloadMember('bwadmin');
        $this->assertSame($oldEmail, $member->getEmail());
        $this->assertNull($member->getNewEmail());
        $this->assertNull($member->getRegistrationKey());
    }

    /**
     * The short name field shows the member's short name, so saving the page as it is keeps it.
     */
    public function testSavingTheAccountPageKeepsTheShortName(): void
    {
        $client = static::createClient();
        $member = $this->reloadMember('bwadmin');
        $member->setShortName('Zezi');
        $this->getEntityManager()->flush();
        $client->loginUser($member);

        $crawler = $client->request('GET', '/members/bwadmin/account');
        $this->assertResponseIsSuccessful();
        $this->assertSame('Zezi', $crawler->filter('#account_edit_form_short_name')->attr('value'));

        $client->submit($crawler->filter('input[type="submit"]')->form());

        $this->assertResponseRedirects('/members/bwadmin');
        $this->assertSame('Zezi', $this->reloadMember('bwadmin')->getShortName());
    }

    /**
     * A member without a short name gets none (not their username) by saving the account page.
     */
    public function testSavingTheAccountPageAddsNoShortName(): void
    {
        $client = static::createClient();
        $member = $this->reloadMember('bwadmin');
        $member->setShortName(null);
        $this->getEntityManager()->flush();
        $client->loginUser($member);

        $crawler = $client->request('GET', '/members/bwadmin/account');
        $this->assertResponseIsSuccessful();
        $this->assertSame('', (string) $crawler->filter('#account_edit_form_short_name')->attr('value'));

        $client->submit($crawler->filter('input[type="submit"]')->form());

        $this->assertResponseRedirects('/members/bwadmin');
        $this->assertNull($this->reloadMember('bwadmin')->getShortName());
    }

    public function testAnotherMembersAccountPageLeadsToTheOwnOne(): void
    {
        $client = static::createClient();
        $this->loginMember($client, 'member-2');

        $client->request('GET', '/members/bwadmin/account');

        $this->assertResponseRedirects('/members/member-2/account');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testLegacyEditProfileRedirectsToModernProfileEdit(): void
    {
        $client = static::createClient();
        $this->loginMember($client, 'bwadmin');

        $client->request('GET', '/editmyprofile');

        $this->assertResponseRedirects('/members/bwadmin/edit');
    }

    public function testLegacyEditProfilePostDoesNotChangeEmail(): void
    {
        $client = static::createClient();
        $member = $this->loginMember($client, 'bwadmin');
        $oldEmail = $member->getEmail();

        $client->request('POST', '/editmyprofile/finish', [
            'Email' => 'attacker-' . uniqid() . '@example.test',
        ]);

        $this->assertResponseRedirects('/members/bwadmin/edit');
        $member = $this->reloadMember('bwadmin');
        $this->assertSame($oldEmail, $member->getEmail());
    }

    private function loginMember(KernelBrowser $client, string $username): Member
    {
        $member = $this->reloadMember($username);
        $client->loginUser($member);

        return $member;
    }

    /**
     * The email change page (account_email): the new address and the member's password (fixtures: 'password').
     */
    private function submitAccountEmailChange(KernelBrowser $client, Member $member, string $email, string $password = 'password'): void
    {
        $crawler = $client->request('GET', '/members/' . $member->getUsername() . '/account/email');
        $this->assertResponseIsSuccessful();
        $client->submit($crawler->filter('form.p-account-form')->form(), [
            'change_email_form[email]' => $email,
            'change_email_form[password]' => $password,
        ]);
    }

    private function reloadMember(string $username): Member
    {
        $entityManager = $this->getEntityManager();
        $entityManager->clear();
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        $this->assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
