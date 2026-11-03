<?php

namespace App\Tests\Controller;

use App\Entity\ForumThread;
use App\Entity\Group as GroupEntity;
use App\Entity\GroupCategory;
use App\Entity\Location;
use App\Entity\Member;
use App\Model\ForumModel;
use DateTime;
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
        $names = $crawler->filter('.p-group-card__name')->each(static fn ($link) => $link->text());
        self::assertContains('Hiking Enthusiasts', $names);
        self::assertContains('Language Exchange', $names);
        self::assertNotContains('Vegan Travelers', $names, 'groups waiting for approval are left out');
        // the agora is pinned above the list, not in it
        self::assertNotContains('BeWelcome Agora', $names);
        self::assertSelectorTextContains('.p-groups-agora__title', 'BeWelcome Agora');
    }

    public function testArchivedGroupsHaveTheirOwnTab(): void
    {
        $this->entityManager->getConnection()->executeStatement(
            "UPDATE `group` SET name = '[Archived] Vegan Travelers', approved = 1 WHERE name = 'Vegan Travelers'"
        );

        $all = $this->client->request('GET', '/groups/search');
        self::assertNotContains('Vegan Travelers', $all->filter('.p-group-card__name')->each(static fn ($link) => $link->text()));
        self::assertCount(1, $all->filter('.p-groups__tab--archived'));

        $archived = $this->client->request('GET', '/groups/search', ['archived' => 1]);
        self::assertResponseIsSuccessful();
        self::assertSame(['Vegan Travelers'], $archived->filter('.p-group-card__name')->each(static fn ($link) => $link->text()));
        self::assertCount(1, $archived->filter('.p-group-card__archived'));
        self::assertCount(1, $archived->filter('.p-groups__tab--archived.is-active'));
    }

    public function testTheSearchFiltersByName(): void
    {
        $crawler = $this->client->request('GET', '/groups/search', ['name' => 'hiking']);

        self::assertSame(['Hiking Enthusiasts'], $crawler->filter('.p-group-card__name')->each(static fn ($link) => $link->text()));
    }

    public function testTheSearchFiltersByCategory(): void
    {
        $category = new GroupCategory()->setName('en', 'Languages');
        $this->entityManager->persist($category);
        $this->getGroup('Language Exchange')->addCategory($category);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/groups/search', ['category' => $category->getId()]);

        self::assertSame(['Language Exchange'], $crawler->filter('.p-group-card__name')->each(static fn ($link) => $link->text()));
    }

    public function testTheLocationFilterFindsTheGroupsInPlacesWithinIt(): void
    {
        $this->getGroup('Hiking Enthusiasts')->setLocation($this->entityManager->find(Location::class, self::BERLIN));
        $this->entityManager->flush();

        $inGermany = $this->client->request('GET', '/groups/search', ['location' => self::GERMANY]);
        self::assertSame(['Hiking Enthusiasts'], $inGermany->filter('.p-group-card__name')->each(static fn ($link) => $link->text()));

        $inIndonesia = $this->client->request('GET', '/groups/search', ['location' => self::INDONESIA]);
        self::assertCount(0, $inIndonesia->filter('.p-group-card__name'));
    }

    public function testTheSearchOrdersByNumberOfMembers(): void
    {
        $crawler = $this->client->request('GET', '/groups/search', ['order' => 'membersdesc']);

        $names = $crawler->filter('.p-group-card__name')->each(static fn ($link) => $link->text());
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
        $names = $crawler->filter('.p-group-card__name')->each(static fn ($link) => $link->text());
        self::assertNotContains('BeWelcome Agora', $names, 'the agora is pinned above the list');
        self::assertContains('Hiking Enthusiasts', $names);
        // the count leaves the agora out too
        self::assertSame((string) \count($names), $crawler->filter('.p-groups__tab.is-active .p-groups__count')->text());
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

    public function testTheGroupsMenuIsAboveThePageAndJoinIsForThoseWhoMay(): void
    {
        $group = $this->getGroup('Hiking Enthusiasts');
        $groupUrl = '/group/' . $group->getId();

        // member-2 is in the group: the menu's tabs, the discussions next to the members, a button for a topic
        $crawler = $this->client->request('GET', $groupUrl);
        self::assertCount(0, $crawler->filter('#sidebar'));
        self::assertSame($groupUrl, $crawler->filter('.p-group__tab.is-active')->attr('href'));
        // no description in the fixtures: no description card, the discussions in the main column
        self::assertCount(0, $crawler->filter('.p-group-layout .p-group-description'));
        self::assertGreaterThan(0, $crawler->filter('.p-group-main .c-home-item')->count());
        self::assertCount(1, $crawler->filter('.p-group-side a[href="' . $groupUrl . '/members"]'));
        self::assertCount(1, $crawler->filter('.p-group__actions a[href="' . $groupUrl . '/new"]'));
        self::assertCount(0, $crawler->filter('.p-group__actions a[href$="/join"]'));
        // the threads in the group's page don't name the group again
        self::assertGreaterThan(0, $crawler->filter('.c-home-item')->count());
        self::assertCount(0, $crawler->filter('.c-home-item a[href="' . $groupUrl . '"]'));

        $connection = $this->entityManager->getConnection();
        $memberId = $this->getMember('member-2')->getId();
        $connection->executeStatement(
            'DELETE FROM group_membership WHERE group_id = ? AND member_id = ?',
            [$group->getId(), $memberId],
        );
        $crawler = $this->client->request('GET', $groupUrl);
        self::assertCount(1, $crawler->filter('.p-group__actions a[href="' . $groupUrl . '/join"]'));

        // waiting to be let in: no Join
        $connection->executeStatement(
            "INSERT INTO group_membership (group_id, member_id, status, created, updated)
             VALUES (?, ?, 'WantToBeIn', NOW(), NOW())",
            [$group->getId(), $memberId],
        );
        $this->entityManager->clear();
        $crawler = $this->client->request('GET', $groupUrl);
        self::assertCount(0, $crawler->filter('.p-group__actions a'));
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

    public function testASearchFindsTheAgoraLikeAnyGroup(): void
    {
        $crawler = $this->client->request('GET', '/groups/search', ['name' => 'Agora']);

        self::assertSame(['BeWelcome Agora'], $crawler->filter('.p-group-card__name')->each(static fn ($link) => $link->text()));
        self::assertSelectorNotExists('.p-groups-agora', 'a search shows no pinned agora');
    }

    public function testTheGroupsNearTheMembersPlaceAreShown(): void
    {
        // member-2 lives in Berlin (fixtures/address.yml)
        $this->getGroup('Hiking Enthusiasts')->setLocation($this->entityManager->find(Location::class, self::BERLIN));
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/groups/search');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#groups-near-title', 'Berlin');
        self::assertSame(['Hiking Enthusiasts'], $crawler->filter('.p-groups-near__name')->each(static fn ($name) => $name->text()));
    }

    public function testNoGroupsNearAreShownWithoutAny(): void
    {
        $this->client->request('GET', '/groups/search');

        self::assertSelectorNotExists('.p-groups-near');
    }

    public function testTheCategoriesAreTilesAndChips(): void
    {
        $category = new GroupCategory()->setName('en', 'Outdoors')->setIcon('person-hiking');
        $this->entityManager->persist($category);
        $hiking = $this->getGroup('Hiking Enthusiasts');
        $hiking->addCategory($category);
        $hiking->setLocation($this->entityManager->find(Location::class, self::BERLIN));
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/groups/search');

        $tile = $crawler->filter('.p-groups-categories__tile')->reduce(static fn ($node) => str_contains($node->text(), 'Outdoors'));
        self::assertCount(1, $tile);
        self::assertSame('/groups/search?category=' . $category->getId(), $tile->attr('href'));
        self::assertCount(1, $tile->filter('.fa-person-hiking'));
        self::assertSame('1', $tile->filter('.p-groups-categories__count')->text());
        $card = $crawler->filter('.p-group-card')->reduce(static fn ($node) => str_contains($node->text(), 'Hiking Enthusiasts'));
        self::assertSame(['Berlin', 'Outdoors'], $card->filter('.c-group-chip')->each(static fn ($chip) => trim($chip->text())));
    }

    public function testTheSubscriptionsTabListsTheFollowedTopicsUnreadFirst(): void
    {
        $member = $this->getMember('member-2');
        $forumModel = static::getContainer()->get(ForumModel::class);
        $thread = $this->entityManager->getRepository(ForumThread::class)->findOneBy(['deleted' => 'NotDeleted'], ['id' => 'ASC']);
        self::assertInstanceOf(ForumThread::class, $thread);
        $forumModel->subscribe($thread, $member)->setSubscribed(new DateTime('-10 years'));
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/groups/subscriptions');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.p-groups__tab.is-active .fa-bell');
        $row = $crawler->filter('.p-groups-subs__row')->first();
        self::assertStringContainsString('is-unread', (string) $row->attr('class'), 'a post newer than the subscription is unread');
        self::assertSelectorTextContains('.p-groups__count--new', '1 new');

        // opening the thread reads it
        $this->client->request('GET', $row->filter('a.c-home-item__title')->attr('href'));
        $crawler = $this->client->request('GET', '/groups/subscriptions');
        self::assertStringNotContainsString('is-unread', (string) $crawler->filter('.p-groups-subs__row')->first()->attr('class'));
        self::assertSelectorNotExists('.p-groups__count--new');
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
