<?php

namespace App\Tests\Controller;

use App\Entity\Group;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group as TestGroup;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The group routes find their group and member by the ids in the URL (groupId, memberId). The join page looked
 * the group up by a field Group doesn't have, and the routes for join requests and invitations didn't map the ids
 * at all, so all of them failed with a 500.
 */
#[TestGroup('integration')]
class GroupJoinTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testAMemberJoinsAPublicGroup(): void
    {
        // member-1 to member-5 are in the hiking group in the fixtures
        $member = $this->member('member-7');
        $group = $this->group('Hiking Enthusiasts');
        $this->client->loginUser($member);

        $crawler = $this->client->request('GET', '/group/' . $group->getId() . '/join');
        $this->assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('button[name=join]')->form([
            'reason' => 'I like walking',
            'notifications' => 'no',
        ]));

        $this->assertResponseRedirects();
        self::assertTrue($this->group('Hiking Enthusiasts')->isMember($this->member('member-7')));
    }

    public function testTheJoinRequestRoutesFindTheirGroupAndMember(): void
    {
        $group = $this->group('Hiking Enthusiasts');
        $member = $this->member('member-7');
        // not an admin of the group: refused, which needs the group and the member first
        $this->client->loginUser($this->member('member-2'));

        foreach (['acceptjoin', 'declinejoin', 'invite', 'accept', 'decline', 'withdraw'] as $action) {
            $this->client->request('GET', '/group/' . $group->getId() . '/' . $action . '/' . $member->getId());
            self::assertLessThan(500, $this->client->getResponse()->getStatusCode(), $action . ' failed');
        }
    }

    private function group(string $name): Group
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $group = $entityManager->getRepository(Group::class)->findOneBy(['name' => $name]);
        self::assertInstanceOf(Group::class, $group);

        return $group;
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
