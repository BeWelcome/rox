<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\PasswordReset;
use App\Model\PasswordModel;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
