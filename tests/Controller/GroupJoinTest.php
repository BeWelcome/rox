<?php

namespace App\Tests\Controller;

use App\Entity\Group;
use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * @return iterable<string, array{int}>
     */
    public static function notApproved(): iterable
    {
        yield 'waiting for approval' => [Group::NOT_APPROVED];
        yield 'under discussion' => [Group::IN_DISCUSSION];
        yield 'dismissed' => [Group::DISMISSED];
    }

    /**
     * A group other members can't see (GroupVoter::GROUP_VIEW) takes no new members: its join page showed the
     * form and let them in.
     */
    #[DataProvider('notApproved')]
    public function testAGroupThatIsNotApprovedIsNotJoined(int $approved): void
    {
        $group = $this->group('Hiking Enthusiasts');
        $connection = static::getContainer()->get(Connection::class);
        // the form, from the join page while the group is open
        $this->client->loginUser($this->member('member-7'));
        $form = $this->client->request('GET', '/group/' . $group->getId() . '/join')->filter('button[name=join]')->form();
        $connection->executeStatement('UPDATE `group` SET approved = ? WHERE id = ?', [$approved, $group->getId()]);

        $this->client->request('GET', '/group/' . $group->getId() . '/join');
        $this->assertResponseRedirects('/groups/search');
        // the form sent all the same
        $this->client->submit($form, ['reason' => 'I like walking', 'notifications' => 'no']);
        $this->assertResponseRedirects('/groups/search');

        self::assertFalse($this->group('Hiking Enthusiasts')->isMember($this->member('member-7')));
        self::assertSame(0, (int) $connection->fetchOne(
            'SELECT COUNT(*) FROM group_membership WHERE group_id = ? AND member_id = ?',
            [$group->getId(), $this->member('member-7')->getId()],
        ));
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
