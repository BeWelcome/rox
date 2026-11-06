<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The checkers' messages (one decision per message) and activities. bwadmin is in the Safety Team; the test turns
 * fixture messages into reported and block word ones.
 */
#[Group('integration')]
final class CheckerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        // only the messages of the test in the sections
        $this->connection()->executeStatement("UPDATE message SET status = 'Sent', spam_info = 'NotSpam' WHERE status IN ('ToCheck', 'Checked')");
    }

    public function testAReportedMessageGetsOneDecision(): void
    {
        $message = $this->message('ToCheck', 'SpamSayMember', 'zztest buy now', 'zztest he asks for money');
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->client->request('GET', '/admin/spam/messages');

        $this->assertResponseIsSuccessful();
        $row = $this->row($crawler, $message);
        self::assertStringContainsString('zztest buy now', $row->text());
        self::assertStringContainsString('zztest he asks for money', $row->filter('.c-cadmin-report')->text());
        self::assertCount(0, $row->filter('input[type="radio"]:checked'));
        self::assertSame('1', trim($crawler->filter('.c-cnews-menu__item.is-active .c-cnews-menu__count')->text()));

        $this->client->request('POST', '/admin/spam/messages', ['_token' => $this->token($crawler), 'decisions' => [$message => 'spam']]);

        $this->assertResponseRedirects('/admin/spam/messages');
        self::assertSame(['Checked', 'SpamSayChecker,SpamSayMember'], $this->state($message));
        $crawler = $this->client->followRedirect();
        self::assertSelectorExists('.c-cnews-empty');
        $saved = static::getContainer()->get(TranslatorInterface::class)->trans('admin.checker.messages.saved', ['count' => 1, 'spam' => 1, 'ok' => 0]);
        self::assertSame($saved, $crawler->filter('.c-inline-flash__text')->text());

        // the processed messages show the decision, which can be changed
        $crawler = $this->client->request('GET', '/admin/spam/messages/processed');
        self::assertSame('spam', $this->row($crawler, $message)->filter('input[type="radio"]:checked')->attr('value'));
        $this->client->request('POST', '/admin/spam/messages/processed', ['_token' => $this->token($crawler), 'decisions' => [$message => 'ok']]);
        $this->assertResponseRedirects('/admin/spam/messages/processed');
        self::assertSame(['Checked', 'SpamSayMember'], $this->state($message));
    }

    public function testOnlyTheMessagesOfThePageAreDecided(): void
    {
        $message = $this->message('ToCheck', 'SpamSayMember', 'zztest reported');
        $other = (int) $this->connection()->fetchOne("SELECT MIN(id) FROM message WHERE status = 'Sent'");
        $this->client->loginUser($this->member('bwadmin'));
        $token = $this->token($this->client->request('GET', '/admin/spam/messages'));

        $this->client->request('POST', '/admin/spam/messages', ['_token' => $token, 'decisions' => [$message => 'maybe', $other => 'spam']]);

        self::assertSame(['ToCheck', 'SpamSayMember'], $this->state($message));
        self::assertSame(['Sent', 'NotSpam'], $this->state($other));
    }

    public function testTheBlockWordsAreMarked(): void
    {
        $message = $this->message('ToCheck', 'SpamBlkWord', 'zztest mail me at <a href="mailto:x@example.org">x@example.org</a> or call support');
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->client->request('GET', '/admin/spam/messages/blocked');

        $this->assertResponseIsSuccessful();
        $row = $this->row($crawler, $message);
        self::assertSame(['@', 'support'], $row->filter('.c-checker__text mark')->each(static fn (Crawler $mark): string => $mark->text()));
        self::assertSame('mailto:x@example.org', $row->filter('.c-checker__text a')->attr('href'));
        self::assertSame(['@', 'support'], $row->filter('.c-checker__words mark')->each(static fn (Crawler $mark): string => $mark->text()));
    }

    public function testTheDecisionsNeedTheTokenAndTheRight(): void
    {
        $message = $this->message('ToCheck', 'SpamSayMember', 'zztest reported');
        $this->client->loginUser($this->member('bwadmin'));

        $this->client->request('POST', '/admin/spam/messages', ['_token' => 'wrong', 'decisions' => [$message => 'spam']]);
        $this->assertResponseStatusCodeSame(403);

        $this->client->loginUser($this->member('member-6'));
        $this->client->request('GET', '/admin/spam/messages');
        $this->assertResponseStatusCodeSame(403);
        self::assertSame(['ToCheck', 'SpamSayMember'], $this->state($message));
    }

    public function testActivitiesAreDeleted(): void
    {
        $ids = array_map('intval', $this->connection()->fetchFirstColumn('SELECT id FROM activities ORDER BY id LIMIT 2'));
        self::assertCount(2, $ids);
        // longer than a day: looks like spam
        $this->connection()->executeStatement('UPDATE activities SET dateTimeEnd = dateTimeStart + INTERVAL 3 DAY WHERE id IN (?, ?)', $ids);
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->client->request('GET', '/admin/spam/activities');
        $this->assertResponseIsSuccessful();
        foreach ($ids as $id) {
            self::assertCount(1, $crawler->filter('input[name="ids[]"][value="' . $id . '"]'));
        }
        $token = $this->token($crawler);

        $this->client->request('POST', '/admin/spam/activities', ['_token' => $token, 'delete' => $ids[0]]);
        $this->assertResponseRedirects('/admin/spam/activities');
        self::assertSame(0, $this->activities([$ids[0]]));
        self::assertSame(1, $this->activities([$ids[1]]));

        $this->client->request('POST', '/admin/spam/activities', ['_token' => $token, 'ids' => [$ids[1]]]);
        self::assertSame(0, $this->activities([$ids[1]]));
        self::assertSame(0, (int) $this->connection()->fetchOne('SELECT COUNT(*) FROM activitiesattendees WHERE activityId IN (?, ?)', $ids));

        $this->client->request('POST', '/admin/spam/activities', ['_token' => 'wrong', 'ids' => [$ids[1]]]);
        $this->assertResponseStatusCodeSame(403);
    }

    private function message(string $status, string $spamInfo, string $text, ?string $comment = null): int
    {
        $id = (int) $this->connection()->fetchOne("SELECT MIN(id) FROM message WHERE subject_id IS NOT NULL AND status = 'Sent'");
        $this->connection()->executeStatement(
            'UPDATE message SET status = ?, spam_info = ?, message = ?, checker_comment = ? WHERE id = ?',
            [$status, $spamInfo, $text, $comment, $id],
        );
        // a sender who isn't banned (the block word section shows active senders only)
        $this->connection()->executeStatement("UPDATE member SET Status = 'Active' WHERE id = (SELECT sender_id FROM message WHERE id = ?)", [$id]);

        return $id;
    }

    /**
     * @return array{string, string} the message's status and spam info
     */
    private function state(int $id): array
    {
        $row = $this->connection()->fetchAssociative('SELECT status, spam_info FROM message WHERE id = ?', [$id]);

        return [(string) $row['status'], (string) $row['spam_info']];
    }

    private function row(Crawler $crawler, int $message): Crawler
    {
        $row = $crawler->filter('.js-checker-row')->reduce(
            static fn (Crawler $row): bool => 2 === $row->filter('input[name="decisions[' . $message . ']"]')->count(),
        );
        self::assertCount(1, $row);

        return $row;
    }

    private function token(Crawler $crawler): string
    {
        return (string) $crawler->filter('main form input[name="_token"]')->first()->attr('value');
    }

    /**
     * @param list<int> $ids
     */
    private function activities(array $ids): int
    {
        return (int) $this->connection()->fetchOne('SELECT COUNT(*) FROM activities WHERE id IN (' . implode(',', $ids) . ')');
    }

    private function member(string $username): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function connection(): Connection
    {
        return static::getContainer()->get(Connection::class);
    }
}
