<?php

namespace App\Tests\Controller;

use App\Doctrine\ForumVisibilityType;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group as GroupEntity;
use App\Entity\Member;
use DateInterval;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Writing in the forums, now in src: new threads, replies, edits, translations, subscriptions and the notifications
 * they lead to.
 */
#[Group('integration')]
final class ForumWritingTest extends WebTestCase
{
    private const string AGORA_THREAD = 'Tips for first-time hosts?';
    private const string HIKING_THREAD = 'Weekend hiking meetup - who is in?';

    private KernelBrowser $client;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->connection = $this->entityManager()->getConnection();
    }

    public function testAMemberStartsAThreadInTheAgora(): void
    {
        $author = $this->member('member-2');
        $reader = $this->member('member-4');
        $this->joinGroup($reader, GroupEntity::BEWELCOME);
        $this->client->loginUser($author);

        $crawler = $this->request('GET', '/forums/new');
        self::assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('form[name="forum_post"]')->form([
            'forum_post[title]' => 'Where to go in spring?',
            'forum_post[text]' => '<p>Any ideas?</p>',
        ]));

        $thread = $this->thread('Where to go in spring?');
        self::assertResponseRedirects('/forums/s' . $thread->getId());
        self::assertTrue($thread->isAgora());
        self::assertSame('<p>Any ideas?</p>', $thread->getFirstPost()->getMessage());
        // the author subscribed (the box is ticked); the BeWelcome group's member gets a notification, the author not
        self::assertSame(1, $this->countRows('members_threads_subscribed', 'IdThread = ? AND IdSubscriber = ?', [$thread->getId(), $author->getId()]));
        self::assertSame(1, $this->notifications($thread->getFirstPost(), $reader));
        self::assertSame(0, $this->notifications($thread->getFirstPost(), $author));
    }

    public function testOnlyMembersStartThreadsInAGroup(): void
    {
        $hiking = $this->group('Hiking Enthusiasts');
        $member = $this->member('member-5');
        $this->leaveGroup($member, $hiking);
        $this->client->loginUser($member);

        $this->request('GET', '/group/' . $hiking->getId() . '/new');
        self::assertResponseStatusCodeSame(403);

        $this->joinGroup($member, $hiking->getId());
        $crawler = $this->request('GET', '/group/' . $hiking->getId() . '/new');
        self::assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('form[name="forum_post"]')->form([
            'forum_post[title]' => 'Hike on Sunday',
            'forum_post[text]' => 'Who joins?',
            'forum_post[visibility]' => ForumVisibilityType::GROUP_ONLY,
        ]));

        $thread = $this->thread('Hike on Sunday');
        self::assertResponseRedirects('/group/' . $hiking->getId() . '/forum/s' . $thread->getId());
        self::assertSame(ForumVisibilityType::GROUP_ONLY, $thread->getVisibility());
    }

    public function testAReplyGoesToTheThreadsSubscribers(): void
    {
        $thread = $this->thread(self::AGORA_THREAD);
        $replies = (int) $thread->getReplies();
        $subscriber = $this->member('member-4');
        $this->connection->executeStatement(
            "INSERT INTO members_threads_subscribed (IdSubscriber, IdThread, ActionToWatch, UnSubscribeKey, notificationsEnabled, created)
             VALUES (?, ?, 'replies', 'key', 1, NOW())",
            [$subscriber->getId(), $thread->getId()],
        );
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->request('GET', '/forums/s' . $thread->getId() . '/reply');
        self::assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('form[name="forum_post"]')->form(['forum_post[text]' => 'Thanks a lot!']));

        $thread = $this->thread(self::AGORA_THREAD);
        $reply = $thread->getLastPost();
        self::assertResponseRedirects('/forums/s' . $thread->getId() . '#post' . $reply->getId());
        self::assertSame('Thanks a lot!', $reply->getMessage());
        self::assertSame($replies + 1, (int) $thread->getReplies());
        self::assertSame(1, $this->notifications($reply, $subscriber));
    }

    public function testSubscribersWhoCantReadAGroupOnlyPostGetNoNotification(): void
    {
        $thread = $this->thread(self::HIKING_THREAD);
        $outsider = $this->member('member-banned');
        $this->connection->executeStatement(
            "INSERT INTO members_threads_subscribed (IdSubscriber, IdThread, ActionToWatch, UnSubscribeKey, notificationsEnabled, created)
             VALUES (?, ?, 'replies', 'key', 1, NOW())",
            [$outsider->getId(), $thread->getId()],
        );
        $author = $this->member('member-2');
        $this->joinGroup($author, $thread->getGroup()->getId());
        $this->client->loginUser($author);

        $crawler = $this->request('GET', '/group/' . $thread->getGroup()->getId() . '/forum/s' . $thread->getId() . '/reply');
        $this->client->submit($crawler->filter('form[name="forum_post"]')->form([
            'forum_post[text]' => 'Only for us',
            'forum_post[visibility]' => ForumVisibilityType::GROUP_ONLY,
        ]));

        self::assertSame(0, $this->notifications($this->thread(self::HIKING_THREAD)->getLastPost(), $outsider));
    }

    public function testTheAuthorEditsAndTranslatesTheirPost(): void
    {
        $thread = $this->thread(self::AGORA_THREAD);
        $post = $thread->getFirstPost();
        $this->client->loginUser($post->getAuthor());

        $crawler = $this->request('GET', '/forums/edit/m' . $post->getId());
        self::assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('form[name="forum_post"]')->form([
            'forum_post[text]' => 'Edited text',
            'forum_post[title]' => 'Edited title',
        ]));
        self::assertResponseRedirects();

        $crawler = $this->request('GET', '/forums/translate/m' . $post->getId() . '?locale=de');
        self::assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('form[name="forum_post"]')->form([
            'forum_post[locale]' => 'de',
            'forum_post[text]' => 'Bearbeiteter Text',
            'forum_post[title]' => 'Bearbeiteter Titel',
        ]));

        $post = $this->entityManager()->find(ForumPost::class, $post->getId());
        self::assertSame('Edited text', $post->getMessage());
        self::assertSame('Bearbeiteter Text', $post->getMessageTranslations()['de']['message']);
        self::assertSame('Edited title', $post->getThread()->getTitle());
        self::assertSame('Bearbeiteter Titel', $post->getThread()->getTitles()['de']);
        self::assertGreaterThan(0, (int) $post->getEditCount());
    }

    public function testOthersCantEditAPost(): void
    {
        $post = $this->thread(self::AGORA_THREAD)->getFirstPost();
        $other = 'member-2' === $post->getAuthor()->getUsername() ? 'member-4' : 'member-2';
        $this->client->loginUser($this->member($other));

        $this->request('GET', '/forums/edit/m' . $post->getId());

        self::assertResponseStatusCodeSame(403);
    }

    public function testMembersWhoMayNotPostCantReply(): void
    {
        $member = $this->member('member-2');
        $this->connection->executeStatement(
            "INSERT INTO flags (Name, Description, Relevance, created) VALUES ('NotAllowedToPostInForum', 'test', 1, NOW())"
        );
        $flagId = (int) $this->connection->lastInsertId();
        $this->connection->executeStatement(
            "INSERT INTO flagsmembers (IdMember, IdFlag, Level, Scope, Comment, created) VALUES (?, ?, 1, '', 'test', NOW())",
            [$member->getId(), $flagId],
        );
        $this->client->loginUser($member);

        $this->request('GET', '/forums/s' . $this->thread(self::AGORA_THREAD)->getId() . '/reply');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAMemberManagesTheirSubscriptions(): void
    {
        $thread = $this->thread(self::AGORA_THREAD);
        $member = $this->member('member-2');
        $this->client->loginUser($member);

        $crawler = $this->request('GET', '/forums/s' . $thread->getId());
        $this->client->submit($crawler->filter('form[action$="/forums/subscribe/thread/' . $thread->getId() . '"]')->form());
        self::assertSame(1, $this->countRows('members_threads_subscribed', 'IdThread = ? AND IdSubscriber = ? AND notificationsEnabled = 1', [$thread->getId(), $member->getId()]));

        $crawler = $this->request('GET', '/forums/subscriptions');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString(self::AGORA_THREAD, $crawler->filter('#thread-subscriptions')->text());
        $this->client->submit($crawler->filter('form[action$="/forums/subscriptions/disable/thread/' . $thread->getId() . '"]')->form());
        self::assertSame(1, $this->countRows('members_threads_subscribed', 'IdThread = ? AND IdSubscriber = ? AND notificationsEnabled = 0', [$thread->getId(), $member->getId()]));

        $crawler = $this->request('GET', '/forums/subscriptions');
        $this->client->submit($crawler->filter('form[action$="/forums/unsubscribe/thread/' . $thread->getId() . '"]')->form());
        self::assertSame(0, $this->countRows('members_threads_subscribed', 'IdThread = ? AND IdSubscriber = ?', [$thread->getId(), $member->getId()]));
    }

    public function testSwitchesOnPagesNeedTheirToken(): void
    {
        $thread = $this->thread(self::AGORA_THREAD);
        $member = $this->member('member-2');
        $this->client->loginUser($member);

        $this->request('GET', '/forums/subscribe/thread/' . $thread->getId());
        self::assertResponseStatusCodeSame(405);
        $this->client->request('POST', '/forums/subscribe/thread/' . $thread->getId(), ['_token' => 'wrong']);
        self::assertResponseStatusCodeSame(403);
        $this->client->request('POST', '/forums/more/group', ['_token' => 'wrong']);
        self::assertResponseStatusCodeSame(403);
        self::assertSame(0, $this->countRows('members_threads_subscribed', 'IdThread = ? AND IdSubscriber = ?', [$thread->getId(), $member->getId()]));
    }

    public function testTheMailLinksStillWork(): void
    {
        $thread = $this->thread(self::AGORA_THREAD);
        $member = $this->member('member-2');
        $this->connection->executeStatement(
            "INSERT INTO members_threads_subscribed (IdSubscriber, IdThread, ActionToWatch, UnSubscribeKey, notificationsEnabled, created)
             VALUES (?, ?, 'replies', 'key', 1, NOW())",
            [$member->getId(), $thread->getId()],
        );
        $this->client->loginUser($member);

        $this->request('GET', '/forums/subscriptions/disable/thread/' . $thread->getId());
        self::assertSame(1, $this->countRows('members_threads_subscribed', 'IdThread = ? AND IdSubscriber = ? AND notificationsEnabled = 0', [$thread->getId(), $member->getId()]));

        $this->request('GET', '/forums/subscriptions/unsubscribe/thread/' . $thread->getId() . '/1');
        self::assertSame(0, $this->countRows('members_threads_subscribed', 'IdThread = ? AND IdSubscriber = ?', [$thread->getId(), $member->getId()]));
    }

    public function testTheMailLinkSwitchesOffAGroupsNotifications(): void
    {
        $hiking = $this->group('Hiking Enthusiasts');
        $member = $this->member('member-2');
        $this->joinGroup($member, $hiking->getId());
        $this->client->loginUser($member);

        $this->request('GET', '/forums/subscriptions/disable/group/' . $hiking->getId());

        self::assertResponseRedirects('/forums/subscriptions');
        self::assertSame(1, $this->countRows('group_membership', 'group_id = ? AND member_id = ? AND notifications_enabled = 0', [$hiking->getId(), $member->getId()]));
    }

    public function testTheSignedAgoraLinkWorksWithoutLoggingIn(): void
    {
        $member = $this->member('member-4');
        $this->joinGroup($member, GroupEntity::BEWELCOME);
        $url = static::getContainer()->get(UrlGeneratorInterface::class)
            ->generate('agora_notifications_off', ['memberId' => $member->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->request('GET', $url);
        self::assertResponseStatusCodeSame(404);
        self::assertSame(1, $this->countRows('group_membership', 'group_id = 0 AND member_id = ? AND notifications_enabled = 1', [$member->getId()]));

        $this->request('GET', static::getContainer()->get(UriSigner::class)->sign($url, new DateInterval('P1D')));
        self::assertResponseIsSuccessful();
        self::assertSame(1, $this->countRows('group_membership', 'group_id = 0 AND member_id = ? AND notifications_enabled = 0', [$member->getId()]));
    }

    private function request(string $method, string $uri): Crawler
    {
        $this->entityManager()->clear();

        return $this->client->request($method, $uri);
    }

    private function notifications(ForumPost $post, Member $member): int
    {
        return $this->countRows('posts_notificationqueue', 'IdPost = ? AND IdMember = ?', [$post->getId(), $member->getId()]);
    }

    /**
     * @param list<int|string> $parameters
     */
    private function countRows(string $table, string $where, array $parameters): int
    {
        return (int) $this->connection->fetchOne("SELECT COUNT(*) FROM `{$table}` WHERE {$where}", $parameters);
    }

    private function joinGroup(Member $member, int $groupId): void
    {
        $this->connection->executeStatement(
            "INSERT INTO group_membership (member_id, group_id, status, accept_mass_mail, notifications_enabled, created, updated)
             VALUES (?, ?, 'In', 0, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE status = 'In', notifications_enabled = 1",
            [$member->getId(), $groupId],
        );
    }

    private function leaveGroup(Member $member, GroupEntity $group): void
    {
        $this->connection->executeStatement('DELETE FROM group_membership WHERE member_id = ? AND group_id = ?', [$member->getId(), $group->getId()]);
    }

    private function thread(string $title): ForumThread
    {
        $this->entityManager()->clear();
        $thread = $this->entityManager()->getRepository(ForumThread::class)->findOneBy(['title' => $title]);
        self::assertInstanceOf(ForumThread::class, $thread);

        return $thread;
    }

    private function group(string $name): GroupEntity
    {
        $group = $this->entityManager()->getRepository(GroupEntity::class)->findOneBy(['name' => $name]);
        self::assertInstanceOf(GroupEntity::class, $group);

        return $group;
    }

    private function member(string $username): Member
    {
        $member = $this->entityManager()->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
