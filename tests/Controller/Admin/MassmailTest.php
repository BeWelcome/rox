<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DomCrawler\Crawler;

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
        self::assertCount(1, $crawler->filter('tr.js-massmail[data-id="' . $id . '"]'));
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
        $crawler = $this->client->request('GET', '/admin/massmail/' . $id . '/recipients/enqueued');
        $rows = $this->rows($crawler);
        self::assertSame(['member-3', 'member-6'], array_keys($rows));
        self::assertSame('Germany', $rows['member-3'][1]);
        self::assertSame('Indonesia', $rows['member-6'][1]);
        self::assertSame('English', $rows['member-3'][3]);
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
        $usernames = array_keys($this->rows($this->client->request('GET', '/admin/massmail/' . $id . '/recipients/enqueued')));
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

        $this->client->request('POST', '/admin/massmail/' . $id . '/untrigger', ['_token' => $token]);
        self::assertSame(0, $this->recipients($id));
        self::assertSame(['enqueued' => 0, 'triggered' => 0, 'sent' => 0, 'failed' => 0], $this->statistics($id));
        self::assertSame('Created', $this->connection()->fetchOne('SELECT Status FROM broadcast WHERE id = ?', [$id]));
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
        // the list shows the counts
        $crawler = $this->client->request('GET', '/admin/massmail');
        self::assertSame('2', trim($crawler->filter('tr.js-massmail[data-id="' . $id . '"] td')->eq(5)->text()));
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
            '/admin/massmail/details/' . $id . '/sent' => '/admin/massmail/' . $id . '/recipients/sent',
            '/admin/massmail/details/' . $id . '/sent/page/2' => '/admin/massmail/' . $id . '/recipients/sent/2',
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

    private function token(int $id): string
    {
        return (string) $this->client->request('GET', '/admin/massmail/' . $id . '/details')
            ->filter('form[action$="/trigger"] input[name="_token"], form[action$="/unqueue"] input[name="_token"]')
            ->first()->attr('value');
    }

    /**
     * @return array<string, list<string>> the cells of each recipient's row by username
     */
    private function rows(Crawler $crawler): array
    {
        $rows = [];
        $crawler->filter('table.js-recipients tbody tr')->each(static function (Crawler $tr) use (&$rows): void {
            $cells = $tr->filter('td')->each(static fn (Crawler $td): string => trim($td->text()));
            $rows[$cells[0]] = $cells;
        });

        return $rows;
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
