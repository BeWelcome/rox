<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The tabs under the profile's head: on the own profile they include My notes (with the number of notes), which
 * nobody else sees.
 */
#[Group('integration')]
final class ProfileTabsTest extends WebTestCase
{
    public function testMyNotesIsATabOfTheOwnProfileOnly(): void
    {
        $client = static::createClient();
        $member = $this->getMember('member-2');

        $client->loginUser($member);
        $crawler = $client->request('GET', '/members/member-2');
        self::assertResponseIsSuccessful();
        $tab = $crawler->filter('.p-hero__tabs a[href$="/members/member-2/notes"]');
        self::assertCount(1, $tab);
        self::assertCount(1, $tab->filter('i.fa-sticky-note'));

        $client->loginUser($this->getMember('member-4'));
        $crawler = $client->request('GET', '/members/member-2');
        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('.p-hero__tabs a[href*="/notes"]'));
    }

    private function getMember(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
