<?php

namespace App\Tests\Controller;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ForumVisibilityType;
use App\Entity\ForumThread;
use App\Entity\Member;
use App\Model\ForumModerationModel;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Reading the forums, now in src: a group's forum (the agora is the BeWelcome group's) and a thread.
 */
#[Group('integration')]
final class ForumPagesTest extends WebTestCase
{
    private const string AGORA_THREAD = 'Tips for first-time hosts?';
    private const string HIKING_THREAD = 'Weekend hiking meetup - who is in?';

    private KernelBrowser $client;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->connection = static::getContainer()->get(EntityManagerInterface::class)->getConnection();
        $this->client->loginUser($this->member('member-2'));
    }

    public function testTheOverviewGoesToTheGroupsPage(): void
    {
        $this->request('GET', '/forums');
        self::assertResponseRedirects('/groups/', 301);

        // the home page's link to the posts of the member's groups
        $this->request('GET', '/forums?groups=mine');
        self::assertResponseRedirects('/groups/mygroups', 301);
    }

    public function testAGroupThreadHasTheGroupsTabsAndListsOpenItAtItsTop(): void
    {
        $thread = $this->thread(self::HIKING_THREAD);
        $uri = '/group/' . $thread->getGroup()->getId() . '/forum/s' . $thread->getId();

        $crawler = $this->request('GET', $uri);
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.p-group-head .p-hero__tab--active'));

        // the lists link to the thread itself, not to its last post
        $links = $this->request('GET', '/group/' . $thread->getGroup()->getId() . '/forum')->filter('a.c-home-item__row-link')->each(
            static fn ($link): string => (string) $link->attr('href'),
        );
        self::assertContains($uri, $links);
    }

    public function testTheAgoraIsTheBeWelcomeGroupsForum(): void
    {
        $this->request('GET', '/forums/bwforum');
        self::assertResponseRedirects('/group/0/forum', 301);

        $crawler = $this->request('GET', '/group/0/forum');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString(self::AGORA_THREAD, $crawler->text());
        self::assertStringNotContainsString(self::HIKING_THREAD, $crawler->text());
    }

    public function testAThreadShowsItsPostsAndCountsTheView(): void
    {
        $thread = $this->thread(self::AGORA_THREAD);
        $views = $thread->getViews();
        $posts = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM forum_post WHERE threadid = ?', [$thread->getId()]);

        $crawler = $this->request('GET', '/forums/s' . $thread->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', self::AGORA_THREAD);
        self::assertCount($posts, $crawler->filter('.p-thread-post'));
        self::assertCount(1, $crawler->filter('.p-thread-post--starter'), 'the first post is the big card');
        self::assertGreaterThan(0, $crawler->filter('.p-thread__side .p-thread__people img')->count(), 'who wrote in it');
        self::assertCount(1, $crawler->filter('.p-thread__side .p-thread__sort .is-active'));
        self::assertSame($views + 1, (int) $this->connection->fetchOne('SELECT views FROM forum_thread WHERE id = ?', [$thread->getId()]));
    }

    public function testThreadsAreServedAtTheirGroupsUrl(): void
    {
        $agora = $this->thread(self::AGORA_THREAD);
        $hiking = $this->thread(self::HIKING_THREAD);
        $hikingGroup = $hiking->getGroup()->getId();

        $this->request('GET', '/group/0/forum/s' . $agora->getId());
        self::assertResponseRedirects('/forums/s' . $agora->getId(), 301);

        $this->request('GET', '/forums/s' . $hiking->getId());
        self::assertResponseRedirects('/group/' . $hikingGroup . '/forum/s' . $hiking->getId(), 301);

        $this->request('GET', '/group/' . $hikingGroup . '/forum/s' . $hiking->getId() . '-some-title');
        self::assertResponseIsSuccessful();
    }

    public function testGroupOnlyThreadsAreForTheGroupsMembers(): void
    {
        $hiking = $this->thread(self::HIKING_THREAD);
        $this->connection->executeStatement(
            'UPDATE forum_thread SET ThreadVisibility = ? WHERE id = ?',
            [ForumVisibilityType::GROUP_ONLY, $hiking->getId()],
        );
        $this->leaveHiking();

        $this->request('GET', '/group/' . $hiking->getGroup()->getId() . '/forum/s' . $hiking->getId());
        self::assertResponseStatusCodeSame(403);
    }

    public function testModeratorOnlyThreadsAreForModerators(): void
    {
        $agora = $this->thread(self::AGORA_THREAD);
        $this->connection->executeStatement(
            'UPDATE forum_thread SET ThreadVisibility = ? WHERE id = ?',
            [ForumVisibilityType::MODERATOR_ONLY, $agora->getId()],
        );

        $this->request('GET', '/forums/s' . $agora->getId());
        self::assertResponseStatusCodeSame(403);

        $this->client->loginUser($this->member('member-3'));
        $this->request('GET', '/forums/s' . $agora->getId());
        self::assertResponseIsSuccessful();
    }

    public function testModeratorsSeeTheListsOfThreads(): void
    {
        $hiking = $this->thread(self::HIKING_THREAD);
        $this->connection->executeStatement(
            'UPDATE forum_thread SET ThreadDeleted = ? WHERE id = ?',
            [ForumDeleteStatusType::DELETED, $hiking->getId()],
        );
        $this->client->loginUser($this->member('member-3'));

        $crawler = $this->request('GET', '/group/0/forum');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString(self::AGORA_THREAD, $crawler->text());

        // a group's forum shows moderators its deleted threads too
        $crawler = $this->request('GET', '/group/' . $hiking->getGroup()->getId() . '/forum');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString(self::HIKING_THREAD, $crawler->text());
    }

    public function testDeletedPostsAreLeftOut(): void
    {
        $thread = $this->thread(self::AGORA_THREAD);
        $postId = (int) $this->connection->fetchOne('SELECT MIN(id) FROM forum_post WHERE threadid = ?', [$thread->getId()]);
        $this->connection->executeStatement(
            'UPDATE forum_post SET PostDeleted = ? WHERE id = ?',
            [ForumDeleteStatusType::DELETED, $postId],
        );

        $crawler = $this->request('GET', '/forums/s' . $thread->getId());

        self::assertCount(0, $crawler->filter('#post' . $postId));
    }

    public function testTheReverseOrderTurnsThePostsAround(): void
    {
        $thread = $this->thread(self::AGORA_THREAD);
        $shown = static fn ($crawler): array => $crawler->filter('.p-thread-post[id^="post"]')
            ->each(static fn ($anchor) => (int) substr((string) $anchor->attr('id'), 4));

        // the member's preferred order, then the other one
        $normal = $shown($this->request('GET', '/forums/s' . $thread->getId()));
        $reversed = $shown($this->request('GET', '/forums/s' . $thread->getId() . '//reverse'));

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(1, \count($normal));
        self::assertSame(array_reverse($normal), $reversed);
    }

    public function testTheListsShowAsManyThreadsAsTheMemberWantsPerPage(): void
    {
        $this->connection->executeStatement(
            "INSERT INTO memberspreferences (IdMember, IdPreference, Value, updated, created)
             SELECT ?, id, '1', NOW(), NOW() FROM preferences WHERE codename = 'ItemsPerPage'",
            [$this->member('member-2')->getId()],
        );

        $crawler = $this->request('GET', '/group/0/forum');
        self::assertCount(1, $crawler->filter('.p-forum__list .c-home-item'));
    }

    public function testTheAgoraHasTheGroupsHeadWithItsOwnTabs(): void
    {
        $tabs = static fn ($crawler): array => $crawler->filter('.p-group-head .p-hero__tab')->each(static fn ($tab) => $tab->attr('href'));
        $agoraTabs = ['/group/0', '/group/0/forum', '/group/0/rules'];

        // no wiki and no members tab (every member is in it); its threads have the head too
        foreach (['/group/0', '/group/0/forum', '/group/0/rules', '/forums/s' . $this->thread(self::AGORA_THREAD)->getId()] as $url) {
            $crawler = $this->request('GET', $url);
            self::assertResponseIsSuccessful();
            self::assertSame($agoraTabs, $tabs($crawler), $url);
            self::assertCount(0, $crawler->filter('#sidebar'), $url);
        }
        self::assertCount(1, $crawler->filter('.p-group-head .c-group-official, .p-group-head__eyebrow'));

        // a group's forum has the group's head with the Discussions tab active
        $crawler = $this->request('GET', '/group/' . $this->thread(self::HIKING_THREAD)->getGroup()->getId() . '/forum');
        self::assertStringEndsWith('/forum', (string) $crawler->filter('.p-group-head .p-hero__tab--active')->attr('href'));
    }

    public function testTheModeratorsReachTheReportsFromTheAgorasMenu(): void
    {
        $post = $this->thread(self::AGORA_THREAD)->getFirstPost();
        static::getContainer()->get(ForumModerationModel::class)->report($post, $this->member('member-2'), 'Please have a look.');

        $crawler = $this->request('GET', '/group/0');
        self::assertCount(0, $crawler->filter('.p-group-head__actions .p-group-head__menu a[href="/forums/reporttomod/AllActiveReports"]'));

        $this->client->loginUser($this->member('member-3'));
        $crawler = $this->request('GET', '/group/0');
        $reports = $crawler->filter('.p-group-head__actions .p-group-head__menu a[href="/forums/reporttomod/AllActiveReports"]');
        self::assertCount(1, $reports);
        self::assertSame('1', $reports->filter('.p-group-head__badge')->text());
        $this->client->click($reports->link());
        self::assertResponseIsSuccessful();
    }

    public function testThePermalinkPointsAtThePostOnItsPage(): void
    {
        $thread = $this->thread(self::AGORA_THREAD);
        $postId = (int) $this->connection->fetchOne('SELECT MIN(id) FROM forum_post WHERE threadid = ?', [$thread->getId()]);

        $crawler = $this->request('GET', '/forums/s' . $thread->getId());

        // the pages have a <base href> of the site, so a bare #post link would lead to the start page
        self::assertCount(1, $crawler->filter('a[href="/forums/s' . $thread->getId() . '#post' . $postId . '"]'));
    }

    public function testLegacyOverviewUrlsGoToTheGroupsPage(): void
    {
        $this->request('GET', '/groups/forums');
        self::assertResponseRedirects('/groups/', 301);

        $this->request('GET', '/forums/page2/page1/');
        self::assertResponseRedirects('/groups/', 301);
    }

    /**
     * Requests a page with a fresh entity manager: the tests change rows with SQL.
     */
    private function request(string $method, string $uri): \Symfony\Component\DomCrawler\Crawler
    {
        static::getContainer()->get(EntityManagerInterface::class)->clear();

        return $this->client->request($method, $uri);
    }

    private function leaveHiking(): void
    {
        $this->connection->executeStatement(
            "DELETE gm FROM group_membership gm JOIN `group` g ON g.id = gm.group_id WHERE g.name = 'Hiking Enthusiasts' AND gm.member_id = ?",
            [$this->member('member-2')->getId()],
        );
    }

    private function thread(string $title): ForumThread
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $thread = $entityManager->getRepository(ForumThread::class)->findOneBy(['title' => $title]);
        self::assertInstanceOf(ForumThread::class, $thread);

        return $thread;
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
