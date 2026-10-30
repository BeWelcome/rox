<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Legacy member URLs whose pages are in src now.
 */
#[Group('integration')]
final class LegacyMemberUrlsTest extends WebTestCase
{
    public function testMyNotesGoToTheNotesOfTheMember(): void
    {
        $client = static::createClient();
        $client->loginUser($this->member('member-2'));

        $client->request('GET', '/mynotes');

        self::assertResponseRedirects('/members/member-2/notes', 301);
    }

    public function testTheProfileOfATripLegGoesToTheProfile(): void
    {
        $client = static::createClient();
        $client->loginUser($this->member('member-2'));

        $client->request('GET', '/members/member-4/leg/5');

        self::assertResponseRedirects('/members/member-4', 301);
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
