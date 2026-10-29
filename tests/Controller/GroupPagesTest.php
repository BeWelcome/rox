<?php

namespace App\Tests\Controller;

use App\Entity\Group as GroupEntity;
use App\Entity\GroupCategory;
use App\Entity\Location;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The group pages that moved from the legacy code: search, my groups, a group's page and members, the pictures.
 */
#[Group('integration')]
final class GroupPagesTest extends WebTestCase
{
    private const int GERMANY = 2921044;
    private const int BERLIN = 2950159;
    private const int INDONESIA = 1643084;

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->client->loginUser($this->getMember('member-2'));
    }

    public function testTheSearchListsTheApprovedGroups(): void
    {
        $this->entityManager->getConnection()->executeStatement(
            "UPDATE `group` SET approved = 0 WHERE name = 'Vegan Travelers'"
        );

        $crawler = $this->client->request('GET', '/groups/search');

        self::assertResponseIsSuccessful();
        $names = $crawler->filter('h5 a')->each(static fn ($link) => $link->text());
        self::assertContains('Hiking Enthusiasts', $names);
        self::assertContains('Language Exchange', $names);
        self::assertContains('BeWelcome', $names);
        self::assertNotContains('Vegan Travelers', $names, 'groups waiting for approval are left out');
    }

    public function testTheSearchFiltersByName(): void
    {
        $crawler = $this->client->request('GET', '/groups/search', ['name' => 'hiking']);

        self::assertSame(['Hiking Enthusiasts'], $crawler->filter('h5 a')->each(static fn ($link) => $link->text()));
    }

    public function testTheSearchFiltersByCategory(): void
    {
        $category = new GroupCategory()->setName('en', 'Languages');
        $this->entityManager->persist($category);
        $this->getGroup('Language Exchange')->addCategory($category);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/groups/search', ['category' => $category->getId()]);

        self::assertSame(['Language Exchange'], $crawler->filter('h5 a')->each(static fn ($link) => $link->text()));
    }

    public function testTheLocationFilterFindsTheGroupsInPlacesWithinIt(): void
    {
        $this->getGroup('Hiking Enthusiasts')->setLocation($this->entityManager->find(Location::class, self::BERLIN));
        $this->entityManager->flush();

        $inGermany = $this->client->request('GET', '/groups/search', ['location' => self::GERMANY]);
        self::assertSame(['Hiking Enthusiasts'], $inGermany->filter('h5 a')->each(static fn ($link) => $link->text()));

        $inIndonesia = $this->client->request('GET', '/groups/search', ['location' => self::INDONESIA]);
        self::assertCount(0, $inIndonesia->filter('h5 a'));
    }

    public function testTheSearchOrdersByNumberOfMembers(): void
    {
        $crawler = $this->client->request('GET', '/groups/search', ['order' => 'membersdesc']);

        $names = $crawler->filter('h5 a')->each(static fn ($link) => $link->text());
        $counts = $crawler->filter('[data-member-count]')->each(static fn ($count) => (int) $count->attr('data-member-count'));
        self::assertNotEmpty($names);
        $sorted = $counts;
        rsort($sorted);
        self::assertSame($sorted, $counts);
    }

    public function testAnUnknownOrderIsABadRequest(): void
    {
        $this->client->request('GET', '/groups/search', ['order' => 'random']);

        self::assertResponseStatusCodeSame(400);
    }

    public function testFeaturedGroupsGoToTheSearch(): void
    {
        $this->client->request('GET', '/groups/featured');

        self::assertResponseRedirects('/groups/search', 301);
    }

    public function testMyGroupsListsTheGroupsTheMemberIsIn(): void
    {
        $member = $this->getMember('member-2');
        $this->join($member, GroupEntity::BEWELCOME);
        $this->join($member, $this->getGroup('Hiking Enthusiasts')->getId());

        $crawler = $this->client->request('GET', '/groups/mygroups');

        self::assertResponseIsSuccessful();
        $names = $crawler->filter('h5 a')->each(static fn ($link) => $link->text());
        self::assertContains('BeWelcome', $names);
        self::assertContains('Hiking Enthusiasts', $names);
    }

    public function testTheGroupPageShowsTheGroupAndItsLatestThreads(): void
    {
        $group = $this->getGroup('Hiking Enthusiasts');
        $group->setLocation($this->entityManager->find(Location::class, self::BERLIN));
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/group/' . $group->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Hiking Enthusiasts');
        self::assertStringContainsString('Weekend hiking meetup - who is in?', $crawler->text());
        self::assertStringContainsString('Germany › Land Berlin › Berlin', $crawler->text());
    }

    public function testMembersOnlyGroupsHideTheirThreadsFromOtherMembers(): void
    {
        $group = $this->getGroup('Hiking Enthusiasts');
        $group->setMembersOnly(true);
        $this->entityManager->flush();
        $this->entityManager->getConnection()->executeStatement(
            'DELETE FROM group_membership WHERE group_id = ? AND member_id = ?',
            [$group->getId(), $this->getMember('member-2')->getId()],
        );

        $crawler = $this->client->request('GET', '/group/' . $group->getId());

        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('Weekend hiking meetup - who is in?', $crawler->text());
    }

    public function testAGroupWaitingForApprovalSendsOtherMembersBackToTheSearch(): void
    {
        $group = $this->getGroup('Vegan Travelers');
        $group->setApproved(GroupEntity::NOT_APPROVED);
        $this->entityManager->flush();

        $this->client->request('GET', '/group/' . $group->getId());

        self::assertResponseRedirects('/groups/search');
    }

    public function testTheMembersPageListsTheCurrentMembers(): void
    {
        $group = $this->getGroup('Hiking Enthusiasts');
        $usernames = $this->entityManager->getConnection()->fetchFirstColumn(
            "SELECT m.Username FROM group_membership gm JOIN member m ON m.id = gm.member_id
             WHERE gm.group_id = ? AND gm.status = 'In'",
            [$group->getId()],
        );
        self::assertNotEmpty($usernames);

        $crawler = $this->client->request('GET', '/group/' . $group->getId() . '/members');

        self::assertResponseIsSuccessful();
        $listed = $crawler->filter('a.username')->each(static fn ($link) => $link->text());
        foreach ($usernames as $username) {
            self::assertContains($username, $listed);
        }
    }

    public function testThePicturesAreServed(): void
    {
        $group = $this->getGroup('Hiking Enthusiasts');

        $this->client->request('GET', '/group/realimg/' . $group->getId());
        self::assertResponseIsSuccessful();
        $this->client->request('GET', '/group/thumbimg/' . $group->getId());
        self::assertResponseIsSuccessful();

        // the BeWelcome group has no picture
        $this->client->request('GET', '/group/thumbimg/' . GroupEntity::BEWELCOME);
        self::assertResponseStatusCodeSame(404);
    }

    private function join(Member $member, int $groupId): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'INSERT IGNORE INTO group_membership (member_id, group_id, status, accept_mass_mail, notifications_enabled, created, updated)'
            . " VALUES (?, ?, 'In', 0, 1, NOW(), NOW())",
            [$member->getId(), $groupId],
        );
    }

    private function getGroup(string $name): GroupEntity
    {
        $group = $this->entityManager->getRepository(GroupEntity::class)->findOneBy(['name' => $name]);
        self::assertInstanceOf(GroupEntity::class, $group);

        return $group;
    }

    private function getMember(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
