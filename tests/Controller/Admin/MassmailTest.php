<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Member;
use App\Model\TranslationModel;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Mime\Email;

/**
 * The mass mail pages, as a volunteer with the MassMail right (bwadmin: level 10, scope All) uses them: write a mass
 * mail (its subject and text are English words, translated like others), choose the recipients, trigger the sending.
 * member-3 lives in Berlin, member-6 and member-7 in Jayapura (Papua, Indonesia).
 */
#[Group('integration')]
class MassmailTest extends WebTestCase
{
    private const string NAME = 'zztest_mailing';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->loginUser($this->member('bwadmin'));
    }

    public function testTheTextIsWrittenInTheEditor(): void
    {
        $crawler = $this->client->request('GET', '/admin/massmail/create');

        $this->assertResponseIsSuccessful();
        // roxeditor.js turns textareas with data-editor-type into CKEditor; newsletters may carry images
        self::assertCount(1, $crawler->filter('textarea[name="massmail[body]"][data-editor-type=textarea][data-image-upload=yes]'));
        self::assertSame('/newsletter/add/image', $crawler->filter('#upload_path')->attr('value'));
        // a new mass mail starts with the greeting and the signature
        $text = $crawler->filter('textarea[name="massmail[body]"]')->text();
        self::assertStringStartsWith('<p>Hello {username},</p>', $text);
        self::assertStringContainsString('The BeWelcome Volunteers', $text);
    }

    public function testCreatingAMassMailStoresSubjectAndTextAsEnglishWords(): void
    {
        $id = $this->create();

        $this->assertResponseRedirects('/admin/massmail/' . $id . '/details');
        $title = $this->word('broadcast_title_' . self::NAME);
        $body = $this->word('broadcast_body_' . self::NAME);
        self::assertSame('The subject', $title['Sentence']);
        self::assertSame('<p>The body</p>', $body['Sentence']);
        self::assertSame('For the test', $body['Description']);
        self::assertSame($this->member('bwadmin')->getId(), (int) $body['author_id']);
        self::assertSame(0, (int) $body['isarchived']);
        self::assertSame('Normal', $this->connection()->fetchOne('SELECT Type FROM broadcast WHERE id = ?', [$id]));

        $crawler = $this->client->request('GET', '/admin/massmail');
        self::assertCount(1, $crawler->filter('a.js-massmail[data-id="' . $id . '"][data-stage=draft]'));
    }

    public function testANameThatIsTakenIsRefused(): void
    {
        $this->create();

        $this->client->request('GET', '/admin/massmail/create');
        $this->client->submitForm('massmail[submit]', $this->fields());

        $this->assertResponseIsSuccessful();
        self::assertSame(1, (int) $this->connection()->fetchOne('SELECT COUNT(*) FROM broadcast WHERE Name = ?', [self::NAME]));
    }

    public function testEditingAMassMailUpdatesItsTexts(): void
    {
        $id = $this->create();

        $this->client->request('GET', '/admin/massmail/' . $id . '/edit');
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('massmail[submit]', [
            'massmail[subject]' => 'New subject',
            'massmail[body]' => '<p>New body</p>',
        ]);

        $this->assertResponseRedirects('/admin/massmail/' . $id . '/details');
        self::assertSame('New subject', $this->word('broadcast_title_' . self::NAME)['Sentence']);
        self::assertSame('<p>New body</p>', $this->word('broadcast_body_' . self::NAME)['Sentence']);
        self::assertSame('For the test', $this->word('broadcast_body_' . self::NAME)['Description']);
    }

    public function testMembersAreEnqueuedByUsername(): void
    {
        $id = $this->create();

        $this->client->request('GET', '/admin/massmail/' . $id . '/enqueue');
        $this->client->submitForm('members[enqueue]', ['members[members]' => 'usernames', 'members[usernames]' => 'member-3; member-6']);

        $this->assertResponseRedirects('/admin/massmail/' . $id . '/details');
        self::assertSame(['member-3', 'member-6'], $this->enqueued($id));
        self::assertSame(2, $this->statistics($id)['enqueued']);
    }

    public function testNoUsernamesIsAnError(): void
    {
        $id = $this->create();

        $this->client->request('GET', '/admin/massmail/' . $id . '/enqueue');
        $this->client->submitForm('members[enqueue]', ['members[members]' => 'usernames', 'members[usernames]' => '']);

        $this->assertResponseIsSuccessful();
        self::assertSame(0, $this->recipients($id));
    }

    public function testMembersAreEnqueuedByTheirPlace(): void
    {
        $id = $this->create();

        $this->client->request('GET', '/admin/massmail/' . $id . '/enqueue');
        $form = $this->client->getCrawler()->selectButton('location[enqueue]')->form();
        $form['location[country]'] = 'ID';
        $this->client->request('POST', '/admin/massmail/' . $id . '/enqueue', [
            'location' => ['country' => 'ID', 'adminUnit' => '36', 'place' => '', '_token' => $form['location[_token]']->getValue(), 'enqueue' => ''],
        ]);

        $this->assertResponseRedirects('/admin/massmail/' . $id . '/details');
        $usernames = $this->enqueued($id);
        self::assertContains('member-6', $usernames);
        self::assertContains('member-7', $usernames);
        self::assertNotContains('member-3', $usernames);
    }

    public function testTheEnqueuePageListsCountriesRegionsAndPlaces(): void
    {
        $id = $this->create();

        $crawler = $this->client->request('GET', '/admin/massmail/' . $id . '/enqueue');
        self::assertContains('Germany', $crawler->filter('select[name="location[country]"] option')->each(static fn (Crawler $o) => $o->text()));
        // terms of use only for a mass mail of that type
        self::assertCount(0, $crawler->filter('#termsofuse-pane'));

        $this->client->request('GET', '/admin/massmail/adminunits/DE');
        self::assertContains('Land Berlin', array_column($this->json(), 'name'));
        $this->client->request('GET', '/admin/massmail/places/DE/16');
        self::assertContains('Berlin', array_column($this->json(), 'name'));
    }

    public function testTheSendingIsTriggeredAndStopped(): void
    {
        $id = $this->create();
        $this->client->request('GET', '/admin/massmail/' . $id . '/enqueue');
        $this->client->submitForm('members[enqueue]', ['members[members]' => 'usernames', 'members[usernames]' => 'member-3;member-6']);
        $token = $this->token($id);

        self::assertSame(['enqueued' => 2, 'triggered' => 0, 'sent' => 0, 'failed' => 0], $this->statistics($id));

        $this->client->request('POST', '/admin/massmail/' . $id . '/trigger', ['_token' => $token]);
        $this->assertResponseRedirects('/admin/massmail/' . $id . '/details');
        self::assertSame(2, $this->recipients($id, 'ToSend'));
        self::assertSame(['enqueued' => 0, 'triggered' => 2, 'sent' => 0, 'failed' => 0], $this->statistics($id));
        self::assertSame('Triggered', $this->connection()->fetchOne('SELECT Status FROM broadcast WHERE id = ?', [$id]));
        // a triggered mass mail isn't edited
        $this->client->request('GET', '/admin/massmail/' . $id . '/edit');
        $this->assertResponseRedirects('/admin/massmail/' . $id . '/details');

        // stopping moves the messages back to the queue
        $this->client->request('POST', '/admin/massmail/' . $id . '/untrigger', ['_token' => $token]);
        self::assertSame(2, $this->recipients($id, 'ToApprove'));
        self::assertSame(['enqueued' => 2, 'triggered' => 0, 'sent' => 0, 'failed' => 0], $this->statistics($id));
        self::assertSame('Created', $this->connection()->fetchOne('SELECT Status FROM broadcast WHERE id = ?', [$id]));
        self::assertSame('enqueued', $this->stage($id));
    }

    public function testAStoppedSendingKeepsTheSentMessagesAndResumesWithTheOthers(): void
    {
        $id = $this->create();
        $this->client->request('GET', '/admin/massmail/' . $id . '/enqueue');
        $this->client->submitForm('members[enqueue]', ['members[members]' => 'usernames', 'members[usernames]' => 'member-3, member-6, member-7']);
        $this->client->request('POST', '/admin/massmail/' . $id . '/trigger', ['_token' => $this->token($id)]);
        $send = new CommandTester(new Application(self::$kernel)->find('send:massmail'));
        $send->execute(['batchSize' => '1']);

        self::assertSame(['enqueued' => 0, 'triggered' => 2, 'sent' => 1, 'failed' => 0], $this->statistics($id));
        self::assertSame('sending', $this->stage($id));

        $this->client->request('POST', '/admin/massmail/' . $id . '/untrigger', ['_token' => $this->token($id)]);

        $this->assertResponseRedirects('/admin/massmail/' . $id . '/details');
        self::assertSame(1, $this->recipients($id, 'Sent'));
        self::assertSame(2, $this->recipients($id, 'ToApprove'));
        self::assertSame(['enqueued' => 2, 'triggered' => 0, 'sent' => 1, 'failed' => 0], $this->statistics($id));
        $crawler = $this->client->followRedirect();
        self::assertSame('stopped', $crawler->filter('.js-massmail-status')->attr('data-stage'));
        self::assertCount(1, $crawler->filter('.c-inline-flash'));
        // resume: the form of the trigger, next to the removal from the queue
        self::assertCount(1, $crawler->filter('form[action$="/' . $id . '/trigger"]'));
        self::assertCount(1, $crawler->filter('form[action$="/' . $id . '/unqueue"]'));

        $this->client->request('POST', '/admin/massmail/' . $id . '/trigger', ['_token' => $this->token($id)]);
        $send->execute(['batchSize' => '10']);

        // each of the three got it once
        self::assertSame(3, $this->recipients($id, 'Sent'));
        self::assertSame(3, $this->recipients($id));
        self::assertSame(['enqueued' => 0, 'triggered' => 0, 'sent' => 3, 'failed' => 0], $this->statistics($id));
        self::assertSame('sent', $this->stage($id));
    }

    public function testTheDetailsShowTheStageAndTheNextStep(): void
    {
        $id = $this->create();

        $crawler = $this->client->request('GET', '/admin/massmail/' . $id . '/details');
        self::assertSame('draft', $crawler->filter('.js-massmail-status')->attr('data-stage'));
        self::assertCount(1, $crawler->filter('.c-massmail-next a[href="/admin/massmail/' . $id . '/enqueue"]'));
        self::assertCount(0, $crawler->filter('.c-massmail-next form'));
        self::assertCount(1, $crawler->filter('a[href="/admin/massmail/' . $id . '/edit"]'));

        $this->client->request('GET', '/admin/massmail/' . $id . '/enqueue');
        $this->client->submitForm('members[enqueue]', ['members[members]' => 'usernames', 'members[usernames]' => 'member-3']);
        $crawler = $this->client->followRedirect();
        self::assertSame('enqueued', $crawler->filter('.js-massmail-status')->attr('data-stage'));
        self::assertCount(1, $crawler->filter('.c-inline-flash'));
        self::assertSame(['unqueue', 'trigger'], $crawler->filter('.c-massmail-next form')->each(
            static fn (Crawler $form): string => basename((string) $form->attr('action')),
        ));
        // written, enqueued is the current step
        self::assertSame(['done', 'current', 'todo', 'todo'], $this->steps($crawler));

        $this->client->request('POST', '/admin/massmail/' . $id . '/trigger', ['_token' => $this->token($id)]);
        $crawler = $this->client->followRedirect();
        self::assertSame('triggered', $crawler->filter('.js-massmail-status')->attr('data-stage'));
        self::assertSame(['untrigger'], $crawler->filter('.c-massmail-next form')->each(
            static fn (Crawler $form): string => basename((string) $form->attr('action')),
        ));
        self::assertSame(['done', 'done', 'current', 'todo'], $this->steps($crawler));
        self::assertCount(0, $crawler->filter('a[href="/admin/massmail/' . $id . '/edit"]'));
    }

    public function testATestGoesToEachMemberInTheirLanguageAndChangesNoCounts(): void
    {
        $id = $this->create();
        foreach (['broadcast_title_' . self::NAME => 'Le sujet', 'broadcast_body_' . self::NAME => '<p>Bonjour {username},</p>'] as $code => $french) {
            $this->connection()->executeStatement(
                "INSERT INTO word (code, domain, ShortCode, Sentence, created, updated, majorupdate, donottranslate, Description, TranslationPriority, isarchived, author_id)
                SELECT code, domain, 'fr', ?, created, updated, majorupdate, donottranslate, Description, TranslationPriority, isarchived, author_id
                FROM word WHERE code = ? AND ShortCode = 'en'",
                [$french, $code],
            );
        }
        static::getContainer()->get(TranslationModel::class)->refreshTranslationsCache();
        $this->connection()->executeStatement("UPDATE member SET Locale = 'fr' WHERE Username = 'member-3'");

        $this->client->request('GET', '/admin/massmail/' . $id . '/test');
        $this->assertResponseIsSuccessful();
        $this->client->submitForm($this->testButton(), ['form[members]' => 'member-3, bwadmin']);

        $this->assertResponseRedirects('/admin/massmail/' . $id . '/details');
        $subjects = [];
        foreach ($this->getMailerMessages() as $mail) {
            self::assertInstanceOf(Email::class, $mail);
            $subjects[$mail->getTo()[0]->getName()] = $mail->getSubject();
            if ('member-3' === $mail->getTo()[0]->getName()) {
                // {username} in the text is the recipient
                self::assertStringContainsString('Bonjour member-3', (string) $mail->getHtmlBody());
            }
        }
        self::assertSame(['member-3' => 'Le sujet', 'bwadmin' => 'The subject'], $subjects);
        self::assertSame(0, $this->recipients($id));
        self::assertSame(['enqueued' => 0, 'triggered' => 0, 'sent' => 0, 'failed' => 0], $this->statistics($id));
        self::assertCount(1, $this->client->followRedirect()->filter('.c-inline-flash'));
    }

    public function testATestNeedsAMember(): void
    {
        $id = $this->create();

        foreach (['', 'nobody-with-this-name'] as $usernames) {
            $this->client->request('GET', '/admin/massmail/' . $id . '/test');
            $crawler = $this->client->submitForm($this->testButton(), ['form[members]' => $usernames]);

            $this->assertResponseIsSuccessful();
            self::assertCount(1, $crawler->filter('.c-massmail-form__errors'), 'usernames: "' . $usernames . '"');
            self::assertEmailCount(0);
        }
    }

    public function testMembersWhoCantReceiveItAnyLongerAreSkipped(): void
    {
        $id = $this->create();
        $this->client->request('GET', '/admin/massmail/' . $id . '/enqueue');
        $this->client->submitForm('members[enqueue]', ['members[members]' => 'usernames', 'members[usernames]' => 'member-3;member-6']);
        $this->client->request('POST', '/admin/massmail/' . $id . '/trigger', ['_token' => $this->token($id)]);
        // suspended after being enqueued
        $this->connection()->executeStatement("UPDATE member SET Status = 'SuspendedBeta' WHERE Username = 'member-6'");

        $tester = new CommandTester(new Application(self::$kernel)->find('send:massmail'));
        $tester->execute(['batchSize' => '10']);

        self::assertSame(1, $this->recipients($id, 'Sent'));
        self::assertSame(1, $this->recipients($id, 'Skipped'));
        self::assertSame(0, $this->recipients($id, 'ToSend'));
        self::assertSame(['enqueued' => 0, 'triggered' => 0, 'sent' => 1, 'failed' => 0], $this->statistics($id));
    }

    public function testAnOldReminderIsntEnqueued(): void
    {
        $id = $this->create();
        $this->connection()->executeStatement("UPDATE broadcast SET Type = 'RemindToLog' WHERE id = ?", [$id]);

        $this->client->request('GET', '/admin/massmail/' . $id . '/enqueue');

        $this->assertResponseRedirects('/admin/massmail/' . $id . '/details');
    }

    public function testEnqueuedMembersAreRemovedAgain(): void
    {
        $id = $this->create();
        $this->client->request('GET', '/admin/massmail/' . $id . '/enqueue');
        $this->client->submitForm('members[enqueue]', ['members[members]' => 'usernames', 'members[usernames]' => 'member-3']);

        self::assertSame(1, $this->statistics($id)['enqueued']);

        $this->client->request('POST', '/admin/massmail/' . $id . '/unqueue', ['_token' => $this->token($id)]);

        self::assertSame(0, $this->recipients($id));
        self::assertSame(0, $this->statistics($id)['enqueued']);
    }

    public function testSendingCountsTheSentMails(): void
    {
        $id = $this->create();
        $this->client->request('GET', '/admin/massmail/' . $id . '/enqueue');
        $this->client->submitForm('members[enqueue]', ['members[members]' => 'usernames', 'members[usernames]' => 'member-3;member-6']);
        $this->client->request('POST', '/admin/massmail/' . $id . '/trigger', ['_token' => $this->token($id)]);

        $tester = new CommandTester(new Application(self::$kernel)->find('send:massmail'));
        $tester->execute(['batchSize' => '10']);

        $tester->assertCommandIsSuccessful();
        self::assertSame(2, $this->recipients($id, 'Sent'));
        self::assertSame(['enqueued' => 0, 'triggered' => 0, 'sent' => 2, 'failed' => 0], $this->statistics($id));
        // the list shows the counts: enqueued, triggered, sent, failed
        $crawler = $this->client->request('GET', '/admin/massmail');
        $row = $crawler->filter('a.js-massmail[data-id="' . $id . '"]');
        self::assertSame('sent', $row->attr('data-stage'));
        self::assertSame(['–', '–', '2', '–'], $row->filter('.c-massmail-list__count')->each(static fn (Crawler $count): string => trim($count->text())));
    }

    public function testTheActionsNeedTheToken(): void
    {
        $id = $this->create();
        $this->client->request('GET', '/admin/massmail/' . $id . '/enqueue');
        $this->client->submitForm('members[enqueue]', ['members[members]' => 'usernames', 'members[usernames]' => 'member-3']);

        $this->client->request('POST', '/admin/massmail/' . $id . '/trigger', ['_token' => 'wrong']);

        $this->assertResponseStatusCodeSame(403);
        self::assertSame(1, $this->recipients($id, 'ToApprove'));
    }

    public function testThePagesNeedTheRight(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/admin/massmail');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testTheOldUrlsLeadToTheNewOnes(): void
    {
        $id = $this->create();

        $old = [
            '/admin/massmail/details/' . $id => '/admin/massmail/' . $id . '/details',
            '/admin/massmail/details/' . $id . '/sent' => '/admin/massmail/' . $id . '/details',
            '/admin/massmail/details/' . $id . '/sent/page/2' => '/admin/massmail/' . $id . '/details',
            '/admin/massmail/edit/' . $id => '/admin/massmail/' . $id . '/edit',
            '/admin/massmail/enqueue/' . $id => '/admin/massmail/' . $id . '/enqueue',
            '/admin/massmail/test/' . $id => '/admin/massmail/' . $id . '/test',
        ];
        foreach ($old as $url => $new) {
            $this->client->request('GET', $url);
            $this->assertResponseRedirects($new, 301, $url);
        }
    }

    private function create(): int
    {
        $this->client->request('GET', '/admin/massmail/create');
        $this->client->submitForm('massmail[submit]', $this->fields());

        return (int) $this->connection()->fetchOne('SELECT id FROM broadcast WHERE Name = ?', [self::NAME]);
    }

    /**
     * @return array<string, string>
     */
    private function fields(): array
    {
        return [
            'massmail[type]' => 'Normal',
            'massmail[name]' => self::NAME,
            'massmail[subject]' => 'The subject',
            'massmail[body]' => '<p>The body</p>',
            'massmail[description]' => 'For the test',
        ];
    }

    private function testButton(): string
    {
        return (string) $this->client->getCrawler()->filter('form button[type=submit]')->last()->text();
    }

    private function stage(int $id): string
    {
        return (string) $this->client->request('GET', '/admin/massmail/' . $id . '/details')
            ->filter('.js-massmail-status')->attr('data-stage');
    }

    /**
     * @return list<string> the state of the four steps on the details page: done, current or todo
     */
    private function steps(Crawler $crawler): array
    {
        return $crawler->filter('.c-massmail-step')->each(
            static fn (Crawler $step): string => (string) preg_replace('/^.*c-massmail-step--(\w+).*$/', '$1', (string) $step->attr('class')),
        );
    }

    private function token(int $id): string
    {
        return (string) $this->client->request('GET', '/admin/massmail/' . $id . '/details')
            ->filter('form[action$="/trigger"] input[name="_token"], form[action$="/unqueue"] input[name="_token"], form[action$="/untrigger"] input[name="_token"]')
            ->first()->attr('value');
    }

    /**
     * @return list<string> the usernames of the enqueued members
     */
    private function enqueued(int $id): array
    {
        return $this->connection()->fetchFirstColumn(
            "SELECT m.Username FROM broadcastmessages bm JOIN member m ON m.id = bm.IdReceiver
            WHERE bm.IdBroadcast = ? AND bm.Status = 'ToApprove' ORDER BY m.Username",
            [$id],
        );
    }

    /**
     * @return array{enqueued: int, triggered: int, sent: int, failed: int}
     */
    private function statistics(int $id): array
    {
        $row = $this->connection()->fetchAssociative(
            'SELECT enqueued, triggered, sent, failed FROM broadcast_statistics WHERE broadcast_id = ?',
            [$id],
        ) ?: ['enqueued' => 0, 'triggered' => 0, 'sent' => 0, 'failed' => 0];

        return array_map(intval(...), $row);
    }

    private function recipients(int $id, ?string $status = null): int
    {
        return (int) $this->connection()->fetchOne(
            'SELECT COUNT(*) FROM broadcastmessages WHERE IdBroadcast = ?' . (null !== $status ? ' AND Status = ?' : ''),
            null !== $status ? [$id, $status] : [$id],
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function json(): array
    {
        $this->assertResponseIsSuccessful();

        return json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    private function word(string $code): array
    {
        $row = $this->connection()->fetchAssociative("SELECT * FROM word WHERE code = ? AND ShortCode = 'en'", [$code]);
        self::assertIsArray($row, $code . ' is missing in word');

        return $row;
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
