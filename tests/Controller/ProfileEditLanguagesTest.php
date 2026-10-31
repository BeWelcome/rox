<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A member who opens the languages edit page of another member is sent to their own, in the same language.
 */
#[Group('integration')]
class ProfileEditLanguagesTest extends WebTestCase
{
    public function testAnotherMembersLanguagesPageRedirectsToTheOwnOne(): void
    {
        $client = static::createClient();
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'member-2']);
        $client->loginUser($member);

        $client->request('GET', '/members/member-5/edit/de/languages');

        $this->assertResponseRedirects('/members/member-2/edit/de/languages');
    }
}
