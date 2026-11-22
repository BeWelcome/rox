<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\CheckerController;
use App\Entity\Activity;
use App\Entity\ActivityAttendee;
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

    public function testTheProcessedBlockWordMessagesAreTheOnesWithABlockWord(): void
    {
        // decided by a checker, either way; and waiting, but of a sender who was banned meanwhile
        $keptAsOk = $this->messageOf('member-3', 'Checked', 'SpamBlkWord');
        $keptAsSpam = $this->messageOf('member-5', 'Checked', 'SpamBlkWord,SpamSayChecker');
        $ofBannedSender = $this->messageOf('member-7', 'ToCheck', 'SpamBlkWord,SpamSayMember');
        // not these: no block word (though checked, or of a banned sender), and a block word still to check
        $checkedWithoutBlockWord = $this->messageOf('member-3', 'Checked', 'NotSpam');
        $bannedWithoutBlockWord = $this->messageOf('member-7', 'Sent', 'NotSpam');
        $toCheck = $this->messageOf('member-5', 'ToCheck', 'SpamBlkWord');
        $this->connection()->executeStatement("UPDATE member SET Status = 'Banned' WHERE Username = 'member-7'");
        // the newest first
        $this->connection()->executeStatement('UPDATE message SET created = NOW() - INTERVAL 3 DAY WHERE id = ?', [$keptAsOk]);
        $this->connection()->executeStatement('UPDATE message SET created = NOW() - INTERVAL 1 DAY WHERE id = ?', [$keptAsSpam]);
        $this->connection()->executeStatement('UPDATE message SET created = NOW() - INTERVAL 2 DAY WHERE id = ?', [$ofBannedSender]);
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->client->request('GET', '/admin/spam/messages/blocked/processed');

        $this->assertResponseIsSuccessful();
        $listed = $crawler->filter('.js-checker-row input[value="' . CheckerController::SPAM . '"]')->each(
            static fn (Crawler $input): int => (int) preg_replace('/\\D/', '', (string) $input->attr('name')),
        );
        self::assertSame([$keptAsSpam, $ofBannedSender, $keptAsOk], $listed);
        self::assertNotContains($checkedWithoutBlockWord, $listed);
        self::assertNotContains($bannedWithoutBlockWord, $listed);
        self::assertNotContains($toCheck, $listed);
        // the stored decision is preselected
        self::assertCount(1, $this->row($crawler, $keptAsSpam)->filter('input[value="' . CheckerController::SPAM . '"][checked]'));
        self::assertCount(1, $this->row($crawler, $keptAsOk)->filter('input[value="' . CheckerController::NOT_SPAM . '"][checked]'));
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
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $ids = array_map('intval', $entityManager
            ->createQuery('SELECT a.id FROM ' . Activity::class . ' a ORDER BY a.id')
            ->setMaxResults(2)
            ->getSingleColumnResult());
        self::assertCount(2, $ids);
        // longer than a day: looks like spam
        $entityManager
            ->createQuery('UPDATE ' . Activity::class . " a SET a.ends = DATE_ADD(a.starts, 3, 'DAY') WHERE a.id IN (:ids)")
            ->setParameter('ids', $ids)
            ->execute();
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
        self::assertSame(0, (int) $entityManager
            ->createQuery('SELECT COUNT(t.id) FROM ' . ActivityAttendee::class . ' t WHERE IDENTITY(t.activity) IN (:ids)')
            ->setParameter('ids', $ids)
            ->getSingleScalarResult());

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
     * A message sent by the member, in this state. Returns its id.
     */
    private function messageOf(string $sender, string $status, string $spamInfo): int
    {
        $id = (int) $this->connection()->fetchOne(
            "SELECT MIN(m.id) FROM message m JOIN member mb ON mb.id = m.sender_id
             WHERE mb.Username = ? AND m.subject_id IS NOT NULL AND m.status = 'Sent' AND m.spam_info = 'NotSpam' AND m.checker_comment IS NULL",
            [$sender],
        );
        self::assertGreaterThan(0, $id, 'no message of ' . $sender . ' left for the test');
        $this->connection()->executeStatement(
            "UPDATE message SET status = ?, spam_info = ?, checker_comment = 'zztest' WHERE id = ?",
            [$status, $spamInfo, $id],
        );

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
        return (int) static::getContainer()->get(EntityManagerInterface::class)
            ->createQuery('SELECT COUNT(a.id) FROM ' . Activity::class . ' a WHERE a.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getSingleScalarResult();
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
