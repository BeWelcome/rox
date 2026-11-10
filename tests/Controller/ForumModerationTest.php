<?php

namespace App\Tests\Controller;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ReportStatusType;
use App\Doctrine\WhoSpokeLastType;
use App\Entity\ForumThread;
use App\Entity\Group as GroupEntity;
use App\Entity\Member;
use App\Entity\ReportToModerator;
use App\Model\ForumModerationModel;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

/**
 * Forum moderation: the moderation panel of a thread, the moderators' filter of the discussions, reports to the
 * moderators (the Reports tab of the groups, a report as a conversation) and the rules.
 */
#[Group('integration')]
final class ForumModerationTest extends WebTestCase
{
    private const string AGORA_THREAD = 'Tips for first-time hosts?';
    /** Has the ForumModerator right in the fixtures. */
    private const string MODERATOR = 'member-3';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
    }

    public function testTheRulesAreShown(): void
    {
        $this->client->loginUser($this->member('member-2'));

        // a tab of every group; the old URL is the agora's
        $this->request('GET', '/forums/rules');
        self::assertResponseRedirects('/group/0/rules', 301);

        $crawler = $this->request('GET', '/group/0/rules');
        self::assertResponseIsSuccessful();
        self::assertCount(5, $crawler->filter('.p-group-rules__short li'));
        self::assertSame('/group/0/rules', $crawler->filter('.p-group-head .p-hero__tab--active')->attr('href'));
    }

    public function testOnlyModeratorsModerate(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->request('GET', '/forums/modfulleditpost/' . $this->thread()->getFirstPost()->getId());

        self::assertResponseStatusCodeSame(403);
    }

    public function testTheOldModeratorPageOpensThePanelOnThePost(): void
    {
        $post = $this->thread()->getFirstPost();
        $this->client->loginUser($this->member(self::MODERATOR));

        $this->request('GET', '/forums/modfulleditpost/' . $post->getId());
        self::assertResponseRedirects('/forums/post/' . $post->getId() . '?moderate=' . $post->getId());
        $this->request('GET', '/forums/post/' . $post->getId() . '?moderate=' . $post->getId());
        self::assertResponseRedirects('/forums/s' . $this->thread()->getId() . '?moderate=' . $post->getId() . '#post' . $post->getId());

        $crawler = $this->request('GET', '/forums/s' . $this->thread()->getId() . '?moderate=' . $post->getId());
        self::assertResponseIsSuccessful();
        $panel = $crawler->filter('.js-mod-panel');
        self::assertCount(1, $panel);
        self::assertNull($panel->attr('hidden'));
        self::assertSame((string) $post->getId(), $panel->attr('data-post'));
        self::assertCount(1, $crawler->filter('#post' . $post->getId() . '.is-moderated'));
        self::assertNull($crawler->filter('form[name="moderate_post"]')->attr('hidden'));
        self::assertSame('hidden', $crawler->filter('form[name="moderate_thread"]')->attr('hidden'));
    }

    public function testMembersHaveNoModerationPanel(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->request('GET', '/forums/s' . $this->thread()->getId() . '?section=topic');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('.js-mod-panel, .p-mod-post-shield'));
    }

    public function testAModeratorPinsAndMovesAThread(): void
    {
        $thread = $this->thread();
        $hiking = $this->entityManager()->getRepository(GroupEntity::class)->findOneBy(['name' => 'Hiking Enthusiasts']);
        $this->client->loginUser($this->member(self::MODERATOR));

        $crawler = $this->request('GET', '/forums/s' . $thread->getId() . '?section=topic');
        self::assertResponseIsSuccessful();
        self::assertNull($crawler->filter('form[name="moderate_thread"]')->attr('hidden'));
        $this->client->submit($crawler->filter('form[name="moderate_thread"]')->form([
            'moderate_thread[group]' => (string) $hiking->getId(),
            'moderate_thread[stickyValue]' => '5',
        ]));

        // back to the panel on the post it was sent with
        self::assertResponseRedirects();
        self::assertMatchesRegularExpression('~^/forums/post/(\d+)\?moderate=\1&section=topic&saved=1$~', (string) $this->client->getResponse()->headers->get('Location'));
        $row = $this->connection()->fetchAssociative('SELECT IdGroup, stickyvalue FROM forum_thread WHERE id = ?', [$thread->getId()]);
        self::assertSame($hiking->getId(), (int) $row['IdGroup']);
        self::assertSame(5, (int) $row['stickyvalue']);

        // back on the thread, now in the other group, with the panel on its Topic part and Saved
        $this->entityManager()->clear();
        $this->client->followRedirect();
        $crawler = $this->client->followRedirect();
        self::assertStringStartsWith('/group/' . $hiking->getId() . '/forum/s' . $thread->getId(), $this->client->getRequest()->getPathInfo());
        self::assertCount(1, $crawler->filter('form[name="moderate_thread"] .p-mod-saved'));
        self::assertCount(0, $crawler->filter('form[name="moderate_post"] .p-mod-saved'));
    }

    public function testDeletingTheLastPostMakesTheOneBeforeTheLastPost(): void
    {
        $thread = $this->thread();
        $ids = array_map('intval', $this->connection()->fetchFirstColumn(
            'SELECT id FROM forum_post WHERE threadid = ? ORDER BY create_time DESC, id DESC',
            [$thread->getId()],
        ));
        self::assertGreaterThan(1, \count($ids));
        $this->connection()->executeStatement('UPDATE forum_thread SET last_postid = ? WHERE id = ?', [$ids[0], $thread->getId()]);
        $this->client->loginUser($this->member(self::MODERATOR));

        $crawler = $this->request('GET', '/forums/s' . $thread->getId() . '?moderate=' . $ids[0]);
        $form = $crawler->filter('form[name="moderate_post"]')->form();
        $deleted = $form['moderate_post[deleted]'];
        self::assertInstanceOf(ChoiceFormField::class, $deleted);
        $deleted->tick();
        $this->client->submit($form);
        self::assertResponseRedirects('/forums/post/' . $ids[0] . '?moderate=' . $ids[0] . '&section=post&saved=1');

        $row = $this->connection()->fetchAssociative(
            'SELECT t.last_postid, p.PostDeleted FROM forum_thread t JOIN forum_post p ON p.id = ? WHERE t.id = ?',
            [$ids[0], $thread->getId()],
        );
        self::assertSame(ForumDeleteStatusType::DELETED, $row['PostDeleted']);
        self::assertSame($ids[1], (int) $row['last_postid']);

        // the moderators still see the post, struck through and tagged
        $crawler = $this->request('GET', '/forums/s' . $thread->getId());
        self::assertCount(1, $crawler->filter('#post' . $ids[0] . '.is-deleted .p-thread-post__tag--deleted'));
    }

    public function testAMemberReportsAPostAndAModeratorAnswers(): void
    {
        $post = $this->thread()->getFirstPost();
        $reporter = $this->member('member-2' === $post->getAuthor()->getUsername() ? 'member-4' : 'member-2');
        $this->client->loginUser($reporter);

        $crawler = $this->request('GET', '/forums/reporttomod/' . $post->getId());
        self::assertResponseIsSuccessful();
        // a page of the post's group, without the group's head
        self::assertCount(0, $crawler->filter('.p-group-head'));
        self::assertCount(0, $crawler->filter('#report-messages'));
        $this->client->submit($crawler->filter('form[name="report"]')->form(['report[comment]' => 'This is spam.']));
        $crawler = $this->request('GET', '/forums/reporttomod/' . $post->getId());
        $this->client->submit($crawler->filter('form[name="report"]')->form(['report[comment]' => 'Really.']));

        $report = $this->report($post->getId(), $reporter);
        self::assertSame(ReportStatusType::OPEN, $report->getStatus());
        self::assertStringContainsString('This is spam.', $report->getPostComment());
        self::assertStringContainsString('Really.', $report->getPostComment());

        $this->client->loginUser($this->member(self::MODERATOR));
        $crawler = $this->request('GET', '/group/0/reports');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString($reporter->getUsername(), $crawler->filter('#reports')->text());
        self::assertStringContainsString('This is spam.', $crawler->filter('#reports .p-mod-row__excerpt')->text());
        self::assertCount(1, $crawler->filter('#reports .p-mod-dot'));

        // the post shows who reported it
        $crawler = $this->request('GET', '/forums/s' . $this->thread()->getId());
        self::assertStringContainsString($reporter->getUsername(), $crawler->filter('#post' . $post->getId() . ' .p-mod-reported')->text());

        $crawler = $this->request('GET', '/forums/reporttomod/' . $post->getId() . '/' . $reporter->getId());
        self::assertResponseIsSuccessful();
        self::assertSame('/group/0/reports', $crawler->filter('.p-group-head .p-hero__tab--active')->attr('href'));
        // the oldest message first, both the member's
        $messages = $crawler->filter('#report-messages .p-report-msg');
        self::assertCount(2, $messages);
        self::assertStringContainsString('This is spam.', $messages->eq(0)->text());
        self::assertCount(0, $crawler->filter('#report-messages .p-report-msg--mod'));

        $this->client->submit($crawler->filter('form[name="report_answer"]')->form([
            'report_answer[comment]' => 'Thanks, handled.',
            'report_answer[status]' => ReportStatusType::CLOSED,
        ]));

        $report = $this->report($post->getId(), $reporter);
        self::assertSame(ReportStatusType::CLOSED, $report->getStatus());
        self::assertSame(self::MODERATOR, $report->getModerator()?->getUsername());
        self::assertStringContainsString('Thanks, handled.', $report->getPostComment());

        // the member sees the answer as the moderator's
        $this->client->loginUser($reporter);
        $crawler = $this->request('GET', '/forums/reporttomod/' . $post->getId());
        self::assertStringContainsString('Thanks, handled.', $crawler->filter('#report-messages .p-report-msg--mod')->last()->text());
    }

    public function testAModeratorChangesOnlyTheStatus(): void
    {
        $post = $this->thread()->getFirstPost();
        $reporter = $this->member('member-4');
        static::getContainer()->get(ForumModerationModel::class)->report($post, $reporter, 'Off topic.');
        $this->client->loginUser($this->member(self::MODERATOR));

        $crawler = $this->request('GET', '/forums/reporttomod/' . $post->getId() . '/' . $reporter->getId());
        $this->client->submit($crawler->filter('form[name="report_answer"]')->form([
            'report_answer[status]' => ReportStatusType::IN_DISCUSSION,
        ]));

        $report = $this->report($post->getId(), $reporter);
        self::assertSame(ReportStatusType::IN_DISCUSSION, $report->getStatus());
        self::assertSame(WhoSpokeLastType::MEMBER, $report->getLastWhoSpoke());
        self::assertCount(1, static::getContainer()->get(ForumModerationModel::class)->getMessages($report));
    }

    public function testTheReportsTabListsTheGroupsReportsOrAllGroups(): void
    {
        $post = $this->thread()->getFirstPost();
        $reporter = $this->member('member-4');
        static::getContainer()->get(ForumModerationModel::class)->report($post, $reporter, 'Please have a look.');
        $hiking = $this->entityManager()->getRepository(GroupEntity::class)->findOneBy(['name' => 'Hiking Enthusiasts']);
        $this->client->loginUser($this->member(self::MODERATOR));

        // the report is about an agora post: not on the hiking group's tab, but with all groups
        $crawler = $this->request('GET', '/group/' . $hiking->getId() . '/reports');
        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString($reporter->getUsername(), $crawler->filter('#reports')->text());
        self::assertCount(1, $crawler->filter('#reports .p-mod-list__empty'));

        $crawler = $this->request('GET', '/group/' . $hiking->getId() . '/reports/AllActiveReports?scope=all');
        self::assertStringContainsString($reporter->getUsername(), $crawler->filter('#reports')->text());
        self::assertSame(GroupEntity::BEWELCOME, $this->group($crawler->filter('#reports .p-mod-row__group')->text()));

        // the old list is the one of all groups
        $this->request('GET', '/forums/reporttomod/MyReportActive');
        self::assertResponseRedirects('/group/0/reports/MyReportActive?scope=all', 301);
    }

    public function testTheMessagesOfOldAndNewReportsReadAsAConversation(): void
    {
        $report = new ReportToModerator();
        $report->setReporter($this->member('member-2'));
        $report->setPostComment(
            "<p><small>2026-11-01 10:15 member-3:</small><br>We looked at it.</p><hr>\n"
            . 'On 30-10-2026 09:00 (server time) <a href="members/member-2">member-2</a> wrote:<br/>Still there.' . "<hr />\n"
            . 'On 29-10-2026 08:30 (server time) <a href="members/member-2">member-2</a> wrote:<br/>Spam &amp; ads.',
        );

        $messages = static::getContainer()->get(ForumModerationModel::class)->getMessages($report);

        self::assertCount(3, $messages);
        self::assertSame(['member-2', 'member-2', 'member-3'], array_map(static fn ($message) => $message->author, $messages));
        self::assertSame([false, false, true], array_map(static fn ($message) => $message->moderator, $messages));
        self::assertSame('2026-10-29 08:30', $messages[0]->time?->format('Y-m-d H:i'));
        self::assertSame('Spam &amp; ads.', $messages[0]->text);
        self::assertSame('We looked at it.', $messages[2]->text);

        // a text in neither format stays one message
        $report->setPostComment('<b>Something</b>');
        $messages = static::getContainer()->get(ForumModerationModel::class)->getMessages($report);
        self::assertCount(1, $messages);
        self::assertNull($messages[0]->author);
        self::assertSame('<b>Something</b>', $messages[0]->text);
    }

    public function testModeratorsFilterTheDiscussions(): void
    {
        $thread = $this->thread();
        static::getContainer()->get(ForumModerationModel::class)->report($thread->getFirstPost(), $this->member('member-4'), 'Look.');
        $this->connection()->executeStatement('UPDATE forum_thread SET stickyvalue = 3 WHERE id = ?', [$thread->getId()]);
        $this->client->loginUser($this->member(self::MODERATOR));

        $crawler = $this->request('GET', '/group/0/forum?filter=reported');
        self::assertResponseIsSuccessful();
        $items = $crawler->filter('.c-home-item--thread');
        self::assertCount(1, $items);
        self::assertStringContainsString(self::AGORA_THREAD, $items->text());
        self::assertSame('/group/0/reports', $items->filter('.p-mod-tag--report')->attr('href'));
        self::assertCount(1, $items->filter('.p-mod-tag .fa-thumbtack'));
        self::assertSame('/forums/s' . $thread->getId() . '?section=topic', $items->filter('.p-mod-shield')->attr('href'));
        self::assertSame('/group/0/forum?filter=reported', $crawler->filter('.p-mod-seg .is-active')->attr('href'));

        // members neither filter nor see the markers
        $this->client->loginUser($this->member('member-2'));
        $crawler = $this->request('GET', '/group/0/forum?filter=reported');
        self::assertGreaterThan(1, $crawler->filter('.c-home-item--thread')->count());
        self::assertCount(0, $crawler->filter('.p-mod-bar, .p-mod-tags, .p-mod-shield'));
    }

    private function group(string $name): int
    {
        $group = $this->entityManager()->getRepository(GroupEntity::class)->findOneBy(['name' => $name]);
        self::assertInstanceOf(GroupEntity::class, $group);

        return (int) $group->getId();
    }

    private function report(int $postId, Member $reporter): ReportToModerator
    {
        $this->entityManager()->clear();
        $report = $this->entityManager()->getRepository(ReportToModerator::class)->findOneBy(['post' => $postId, 'reporter' => $reporter->getId()]);
        self::assertInstanceOf(ReportToModerator::class, $report);

        return $report;
    }

    private function request(string $method, string $uri): Crawler
    {
        $this->entityManager()->clear();

        return $this->client->request($method, $uri);
    }

    private function thread(): ForumThread
    {
        $thread = $this->entityManager()->getRepository(ForumThread::class)->findOneBy(['title' => self::AGORA_THREAD]);
        self::assertInstanceOf(ForumThread::class, $thread);

        return $thread;
    }

    private function member(string $username): Member
    {
        $member = $this->entityManager()->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function connection(): Connection
    {
        return $this->entityManager()->getConnection();
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
