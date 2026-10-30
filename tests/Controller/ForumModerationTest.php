<?php

namespace App\Tests\Controller;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ReportStatusType;
use App\Entity\ForumThread;
use App\Entity\Group as GroupEntity;
use App\Entity\Member;
use App\Entity\ReportToModerator;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

/**
 * Forum moderation, now in src: the moderator's page of a post, reports to the moderators, the rules.
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

        $this->request('GET', '/forums/rules');

        self::assertResponseIsSuccessful();
    }

    public function testOnlyModeratorsModerate(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->request('GET', '/forums/modfulleditpost/' . $this->thread()->getFirstPost()->getId());

        self::assertResponseStatusCodeSame(403);
    }

    public function testAModeratorPinsAndMovesAThread(): void
    {
        $thread = $this->thread();
        $hiking = $this->entityManager()->getRepository(GroupEntity::class)->findOneBy(['name' => 'Hiking Enthusiasts']);
        $this->client->loginUser($this->member(self::MODERATOR));

        $crawler = $this->request('GET', '/forums/modfulleditpost/' . $thread->getFirstPost()->getId());
        self::assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('form[name="moderate_thread"]')->form([
            'moderate_thread[group]' => (string) $hiking->getId(),
            'moderate_thread[stickyValue]' => '5',
        ]));

        self::assertResponseRedirects();
        $row = $this->connection()->fetchAssociative('SELECT IdGroup, stickyvalue FROM forum_thread WHERE id = ?', [$thread->getId()]);
        self::assertSame($hiking->getId(), (int) $row['IdGroup']);
        self::assertSame(5, (int) $row['stickyvalue']);
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

        $crawler = $this->request('GET', '/forums/modfulleditpost/' . $ids[0]);
        $form = $crawler->filter('form[name="moderate_post"]')->form();
        $deleted = $form['moderate_post[deleted]'];
        self::assertInstanceOf(ChoiceFormField::class, $deleted);
        $deleted->tick();
        $this->client->submit($form);

        $row = $this->connection()->fetchAssociative(
            'SELECT t.last_postid, p.PostDeleted FROM forum_thread t JOIN forum_post p ON p.id = ? WHERE t.id = ?',
            [$ids[0], $thread->getId()],
        );
        self::assertSame(ForumDeleteStatusType::DELETED, $row['PostDeleted']);
        self::assertSame($ids[1], (int) $row['last_postid']);
    }

    public function testAMemberReportsAPostAndAModeratorAnswers(): void
    {
        $post = $this->thread()->getFirstPost();
        $reporter = $this->member('member-2' === $post->getAuthor()->getUsername() ? 'member-4' : 'member-2');
        $this->client->loginUser($reporter);

        $crawler = $this->request('GET', '/forums/reporttomod/' . $post->getId());
        self::assertResponseIsSuccessful();
        $this->client->submit($crawler->filter('form[name="report"]')->form(['report[comment]' => 'This is spam.']));
        $crawler = $this->request('GET', '/forums/reporttomod/' . $post->getId());
        $this->client->submit($crawler->filter('form[name="report"]')->form(['report[comment]' => 'Really.']));

        $report = $this->report($post->getId(), $reporter);
        self::assertSame(ReportStatusType::OPEN, $report->getStatus());
        self::assertStringContainsString('This is spam.', $report->getPostComment());
        self::assertStringContainsString('Really.', $report->getPostComment());

        $this->client->loginUser($this->member(self::MODERATOR));
        $crawler = $this->request('GET', '/forums/reporttomod/AllActiveReports');
        self::assertStringContainsString($reporter->getUsername(), $crawler->filter('#reports')->text());

        $crawler = $this->request('GET', '/forums/reporttomod/' . $post->getId() . '/' . $reporter->getId());
        $this->client->submit($crawler->filter('form[name="report_answer"]')->form([
            'report_answer[comment]' => 'Thanks, handled.',
            'report_answer[status]' => ReportStatusType::CLOSED,
        ]));

        $report = $this->report($post->getId(), $reporter);
        self::assertSame(ReportStatusType::CLOSED, $report->getStatus());
        self::assertSame(self::MODERATOR, $report->getModerator()?->getUsername());
        self::assertStringContainsString('Thanks, handled.', $report->getPostComment());
    }

    public function testMembersDontSeeOthersReports(): void
    {
        $post = $this->thread()->getFirstPost();
        $this->client->loginUser($this->member('member-2'));

        $this->request('GET', '/forums/reporttomod/AllActiveReports');
        self::assertResponseStatusCodeSame(403);
        $this->request('GET', '/forums/reporttomod/' . $post->getId() . '/' . $this->member('member-4')->getId());
        self::assertResponseStatusCodeSame(403);
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
