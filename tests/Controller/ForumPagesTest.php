<?php

namespace App\Tests\Controller;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ForumVisibilityType;
use App\Entity\ForumThread;
use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Reading the forums, now in src: the overview, a group's forum (the agora is the BeWelcome group's) and a thread.
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

    public function testTheOverviewListsTheLatestThreadsOfAllGroups(): void
    {
        $crawler = $this->request('GET', '/forums');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString(self::AGORA_THREAD, $crawler->text());
        self::assertStringContainsString(self::HIKING_THREAD, $crawler->text());
    }

    public function testTheOverviewLeavesOutMembersOnlyGroupsOfOthers(): void
    {
        $this->connection->executeStatement("UPDATE `group` SET members_only = 1 WHERE name = 'Hiking Enthusiasts'");
        $this->leaveHiking();

        $crawler = $this->request('GET', '/forums');

        self::assertStringNotContainsString(self::HIKING_THREAD, $crawler->text());
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
        self::assertCount($posts, $crawler->filter('.l-forum-single-post'));
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

        $crawler = $this->request('GET', '/forums');
        self::assertStringNotContainsString(self::HIKING_THREAD, $crawler->text());
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

        $crawler = $this->request('GET', '/forums');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString(self::AGORA_THREAD, $crawler->text());
        self::assertStringNotContainsString(self::HIKING_THREAD, $crawler->text());

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
        $shown = static fn ($crawler): array => $crawler->filter('.c-single-post-user_info a[id^="post"]')
            ->each(static fn ($anchor) => (int) substr((string) $anchor->attr('id'), 4));

        // the member's preferred order, then the other one
        $normal = $shown($this->request('GET', '/forums/s' . $thread->getId()));
        $reversed = $shown($this->request('GET', '/forums/s' . $thread->getId() . '//reverse'));

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(1, \count($normal));
        self::assertSame(array_reverse($normal), $reversed);
    }

    public function testLegacyOverviewUrlsGoToTheOverview(): void
    {
        $this->request('GET', '/groups/forums');
        self::assertResponseRedirects('/forums', 301);

        $this->request('GET', '/forums/page2/page1/');
        self::assertResponseRedirects('/forums?page=2', 301);
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
