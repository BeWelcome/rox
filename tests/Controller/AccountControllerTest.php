<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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

        $this->assertResponseRedirects('/members/bwadmin');
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
        $form = $crawler->filter('form')->form();
        $client->submit($form);

        $this->assertResponseRedirects('/members/bwadmin');
        $member = $this->reloadMember('bwadmin');
        $this->assertSame($newEmail, $member->getEmail());
        $this->assertNull($member->getNewEmail());
        $this->assertNull($member->getRegistrationKey());
    }

    public function testEmailChangeRejectsUnconfirmedCurrentEmail(): void
    {
        $client = static::createClient();
        $member = $this->loginMember($client, 'bwadmin');
        $oldEmail = $member->getEmail();
        $registrationKey = 'unconfirmed-current-email';
        $member->setRegistrationKey($registrationKey);
        $this->getEntityManager()->flush();

        $this->submitAccountEmailChange($client, $member, 'blocked-' . uniqid() . '@example.test');

        $this->assertResponseStatusCodeSame(422);
        self::assertEmailCount(0);
        $member = $this->reloadMember('bwadmin');
        $this->assertSame($oldEmail, $member->getEmail());
        $this->assertNull($member->getNewEmail());
        $this->assertSame($registrationKey, $member->getRegistrationKey());
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

    private function submitAccountEmailChange(KernelBrowser $client, Member $member, string $email): void
    {
        $crawler = $client->request('GET', '/members/' . $member->getUsername() . '/account');
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('input[type="submit"]')->form();
        $form['account_edit_form[name]'] = $member->getName() ?: 'Test Member';
        $form['account_edit_form[short_name]'] = $member->getShortName() ?: $member->getUsername();
        $form['account_edit_form[gender]'] = $member->getGender();
        $form['account_edit_form[birthdate]'] = $member->getBirthdate()?->format('Y-m-d') ?: '1980-01-01';
        $form['account_edit_form[email]'] = $email;

        $client->submit($form);
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
