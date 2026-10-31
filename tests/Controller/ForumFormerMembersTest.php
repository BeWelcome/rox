<?php

namespace App\Tests\Controller;

use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Member;
use App\Model\ForumModel;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Posts of members who aren't on BeWelcome any more have no author: the forum lists, the thread and the moderators'
 * pages show a former member with the empty avatar, and editing such a post notifies no one.
 */
#[Group('integration')]
final class ForumFormerMembersTest extends WebTestCase
{
    private const string HIKING_THREAD = 'Weekend hiking meetup - who is in?';

    private KernelBrowser $client;
    private Connection $connection;
    private ForumThread $thread;
    private string $formerMember;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->connection = static::getContainer()->get(Connection::class);
        $this->formerMember = static::getContainer()->get(TranslatorInterface::class)->trans('forum.former.member');
        $thread = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(ForumThread::class)->findOneBy(['title' => self::HIKING_THREAD]);
        self::assertInstanceOf(ForumThread::class, $thread);
        $this->thread = $thread;
        $this->connection->executeStatement('UPDATE forum_post SET IdWriter = NULL WHERE threadid = ?', [$thread->getId()]);
        $this->client->loginUser($this->member('member-2'));
    }

    public function testTheGroupsForumAndTheOverviewListTheThread(): void
    {
        foreach (['/group/' . $this->thread->getGroup()->getId() . '/forum', '/forums'] as $url) {
            $crawler = $this->request($url);

            self::assertResponseIsSuccessful();
            $item = $this->threadItem($crawler);
            self::assertCount(1, $item, $url);
            self::assertStringContainsString($this->formerMember, $item->filter('.c-home-item__author')->text());
            self::assertStringEndsWith('/images/empty_avatar_72_72.png', $item->filter('.c-home-item__avatar')->attr('src'));
        }
    }

    public function testTheThreadShowsItsPostsByAFormerMember(): void
    {
        $posts = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM forum_post WHERE threadid = ? AND PostDeleted = ?', [$this->thread->getId(), 'NotDeleted']);

        $crawler = $this->request('/group/' . $this->thread->getGroup()->getId() . '/forum/s' . $this->thread->getId());

        self::assertResponseIsSuccessful();
        self::assertCount($posts, $crawler->filter('.p-thread-post'));
        self::assertStringContainsString($this->formerMember, $crawler->filter('.p-thread-post--starter .p-thread-post__name')->text());
        self::assertCount(0, $crawler->filter('.p-thread-post__name[href]'));
    }

    public function testTheModeratorsPagesShowAFormerMember(): void
    {
        $post = $this->firstPost();
        // bwadmin has the ForumModerator right in the fixtures
        $this->client->loginUser($this->member('bwadmin'));

        foreach (['/forums/modeditpost/' . $post->getId(), '/forums/reporttomod/' . $post->getId()] as $url) {
            $crawler = $this->request($url);

            self::assertResponseIsSuccessful();
            self::assertStringContainsString($this->formerMember, $crawler->filter('main p.small')->text(), $url);
        }
    }

    public function testEditingAPostOfAFormerMemberNotifiesNoOne(): void
    {
        $post = $this->firstPost();
        $queued = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM posts_notificationqueue');

        static::getContainer()->get(ForumModel::class)->queueNotifications($post, ForumModel::NOTIFICATION_EDIT);
        static::getContainer()->get(EntityManagerInterface::class)->flush();

        self::assertSame($queued, (int) $this->connection->fetchOne('SELECT COUNT(*) FROM posts_notificationqueue'));
    }

    private function threadItem(Crawler $crawler): Crawler
    {
        return $crawler->filter('.c-home-item--thread')->reduce(
            static fn (Crawler $item): bool => str_contains($item->text(), self::HIKING_THREAD)
        );
    }

    private function firstPost(): ForumPost
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $post = $entityManager->getRepository(ForumPost::class)->findOneBy(['thread' => $this->thread->getId()], ['id' => 'ASC']);
        self::assertInstanceOf(ForumPost::class, $post);
        self::assertNull($post->getAuthor());

        return $post;
    }

    /**
     * Requests a page with a fresh entity manager: the test changes rows with SQL.
     */
    private function request(string $uri): Crawler
    {
        static::getContainer()->get(EntityManagerInterface::class)->clear();

        return $this->client->request('GET', $uri);
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
