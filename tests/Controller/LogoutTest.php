<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Opening /logout while logged out must not be remembered as the page to go to after the next login: the member
 * would be logged out again right away.
 */
#[Group('integration')]
class LogoutTest extends WebTestCase
{
    public function testLogoutWhileLoggedOutGoesToTheStartPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/logout');

        $this->assertResponseRedirects('/');
    }

    public function testTheNextLoginDoesntLogOutAgain(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        self::assertInstanceOf(Member::class, $member);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $member->setPassword($hasher->hashPassword($member, 'a-password-1'));
        $entityManager->flush();

        $client->request('GET', '/logout');
        $client->request('POST', '/login_check', ['_username' => 'member-2', '_password' => 'a-password-1']);

        $this->assertResponseRedirects();
        self::assertStringNotContainsString('/logout', (string) $client->getResponse()->headers->get('Location'));
        $client->followRedirect();
        self::assertNotNull(static::getContainer()->get('security.token_storage')->getToken()?->getUser());
    }
}
