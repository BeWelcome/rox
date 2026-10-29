<?php

namespace App\Tests\Controller;

use AdminMassmailModel;
use App\Entity\Member;
use DAMA\DoctrineTestBundle\PHPUnit\SkipDatabaseRollback;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ReflectionProperty;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The legacy mass mail admin on the tables of develop: a mailing's subject and body are translations
 * (broadcast_title_<name>, broadcast_body_<name>) in word, which the translator and the sender read; the
 * recipients come from member, their place from address and location. The old tables (words, members, geonames,
 * geonamescountries) don't exist in a new database, and languages is empty.
 *
 * As in ForumTranslationsTest: the legacy code uses its own database connection (no rollback, cleanup in
 * tearDown()), keeps state in static singletons (a process per test), and its form handlers end with exit(), so
 * the tests call what the create and edit form handler calls, after a legacy page has set up the legacy
 * environment for the logged-in admin.
 *
 * @infection-ignore-all
 */
#[Group('integration')]
#[SkipDatabaseRollback]
#[RunTestsInSeparateProcesses]
class MassmailWordTest extends WebTestCase
{
    private const string NAME = 'zztest_mailing';

    private KernelBrowser $client;
    private string $originalWorkingDirectory;

    protected function setUp(): void
    {
        // The legacy pages find their files relative to public/ (SCRIPT_BASE)
        $this->originalWorkingDirectory = (string) getcwd();
        chdir(__DIR__ . '/../../public');
        $this->client = static::createClient();
        // bwadmin has the MassMail right in the fixtures
        $this->client->loginUser($this->admin());
        $this->legacyRequest('/admin/massmail');
    }

    protected function tearDown(): void
    {
        $connection = $this->connection();
        $connection->executeStatement('DELETE FROM word WHERE code IN (?, ?)', ['broadcast_title_' . self::NAME, 'broadcast_body_' . self::NAME]);
        $connection->executeStatement('DELETE bm FROM broadcastmessages bm JOIN broadcast b ON b.id = bm.IdBroadcast WHERE b.Name = ?', [self::NAME]);
        $connection->executeStatement('DELETE FROM broadcast WHERE Name = ?', [self::NAME]);
        chdir($this->originalWorkingDirectory);

        parent::tearDown();
    }

    public function testTheBodyIsWrittenInTheEditor(): void
    {
        $crawler = $this->legacyRequest('/admin/massmail/create');

        // roxeditor.js turns textareas with data-editor-type into CKEditor; newsletters may carry images
        self::assertCount(1, $crawler->filter('textarea[name=Body][data-editor-type=textarea][data-image-upload=yes]'));
    }

    public function testCreatingAMailingStoresSubjectAndBodyAsEnglishTexts(): void
    {
        new AdminMassmailModel()->createMassmail(self::NAME, 'Normal', 'The subject', '<p>The body</p>', 'For the test');

        $title = $this->word('broadcast_title_' . self::NAME);
        $body = $this->word('broadcast_body_' . self::NAME);
        self::assertSame('The subject', $title['Sentence']);
        self::assertSame('<p>The body</p>', $body['Sentence']);
        self::assertSame('For the test', $body['Description']);
        self::assertSame($this->admin()->getId(), (int) $body['author_id']);
        self::assertSame(0, (int) $body['isarchived']);
    }

    public function testEditingAMailingUpdatesItsTexts(): void
    {
        $model = new AdminMassmailModel();
        $model->createMassmail(self::NAME, 'Normal', 'The subject', '<p>The body</p>', 'For the test');
        $id = (int) $this->connection()->fetchOne('SELECT id FROM broadcast WHERE Name = ?', [self::NAME]);

        $model->updateMassmail($id, self::NAME, 'Normal', 'New subject', '<p>New body</p>');

        self::assertSame('New subject', $this->word('broadcast_title_' . self::NAME)['Sentence']);
        self::assertSame('<p>New body</p>', $this->word('broadcast_body_' . self::NAME)['Sentence']);
        $mailing = $model->getMassmail($id);
        self::assertSame('New subject', $mailing->Subject);
        self::assertSame('<p>New body</p>', $mailing->Body);
        self::assertSame(['en'], array_map(static fn ($language) => $language->ShortCode, $mailing->Languages));
    }

    public function testANameThatIsTakenIsRefused(): void
    {
        $model = new AdminMassmailModel();
        $model->createMassmail(self::NAME, 'Normal', 'The subject', '<p>The body</p>', 'For the test');

        $vars = ['Id' => 0, 'Name' => self::NAME, 'Subject' => 's', 'Body' => 'b', 'Description' => 'd', 'Type' => 'Normal'];

        self::assertContains('AdminMassMailCodeExists', $model->massmailEditCreateVarsOk($vars));
    }

    public function testMembersAreEnqueuedByUsername(): void
    {
        $model = new AdminMassmailModel();
        $id = $this->createMailing($model);

        $count = $model->enqueueMassmail([
            'id' => $id, 'enqueuemembers' => 1, 'members-type' => 'usernames', 'usernames' => 'member-3;member-6',
            'max-messages' => '',
        ]);

        self::assertSame(2, $count);
        $recipients = $model->getMassmailRecipientsInfo($id, 'enqueued', 0, 20);
        $byUsername = array_column(array_map(static fn ($r) => (array) $r, $recipients), null, 'Username');
        self::assertSame(['member-3', 'member-6'], array_keys($byUsername));
        self::assertSame('Germany', $byUsername['member-3']['Country']);
        self::assertSame('Indonesia', $byUsername['member-6']['Country']);
        self::assertSame('English', $byUsername['member-3']['Language']);
        self::assertSame(2, (int) $model->getMassmailRecipientsCount($id, 'enqueued'));
    }

    public function testMembersAreEnqueuedByTheirPlace(): void
    {
        $model = new AdminMassmailModel();
        $id = $this->createMailing($model);

        $model->enqueueMassmail(['id' => $id, 'enqueuelocation' => 1, 'CountryIsoCode' => 'ID', 'AdminUnits' => '36']);

        // member-6 and member-7 live in Jayapura (Papua, Indonesia), member-3 in Berlin
        $usernames = array_map(static fn ($r) => $r->Username, $model->getMassmailRecipientsInfo($id, 'enqueued', 0, 20));
        self::assertContains('member-6', $usernames);
        self::assertContains('member-7', $usernames);
        self::assertNotContains('member-3', $usernames);
        self::assertSame(['Indonesia'], array_values(array_unique(array_map(
            static fn ($r) => $r->Country,
            $model->getMassmailRecipientsInfo($id, 'enqueued', 0, 20)
        ))));
    }

    public function testTheEnqueueFormListsCountriesRegionsAndPlaces(): void
    {
        $model = new AdminMassmailModel();

        self::assertContains('Land Berlin', array_map(static fn ($u) => $u->name, $model->getAdminUnits('DE')));
        self::assertContains('Berlin', array_map(static fn ($p) => $p->name, $model->getPlaces('DE', '16')));
        $id = $this->createMailing($model);
        $crawler = $this->legacyRequest('/admin/massmail/enqueue/' . $id);
        self::assertContains('Germany', $crawler->filter('option')->each(static fn ($o) => $o->text()));
    }

    public function testTheReminderCountsAreRead(): void
    {
        $model = new AdminMassmailModel();

        self::assertIsNumeric($model->getMailToConfirmCount());
        self::assertIsNumeric($model->getIncorrectBirthDateCount());
        self::assertIsNumeric($model->getSuspensionNotificationCount());
    }

    private function createMailing(AdminMassmailModel $model): int
    {
        $model->createMassmail(self::NAME, 'Normal', 'The subject', '<p>The body</p>', 'For the test');

        return (int) $this->connection()->fetchOne('SELECT id FROM broadcast WHERE Name = ?', [self::NAME]);
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

    /**
     * Requests a legacy page, as ForumTranslationsTest::legacyRequest() does.
     */
    private function legacyRequest(string $url): Crawler
    {
        $server = $_SERVER;
        $_SERVER['argc'] = 0;
        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        foreach ([['PRequest', '_instance'], ['RequestRouter', '_request_args']] as [$class, $property]) {
            if (class_exists($class, false)) {
                new ReflectionProperty($class, $property)->setValue(null, null);
            }
        }
        try {
            $crawler = $this->client->request('GET', $url);
            $this->assertResponseIsSuccessful();

            return $crawler;
        } finally {
            $_SERVER = $server;
        }
    }

    private function admin(): Member
    {
        $member = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function connection(): Connection
    {
        return static::getContainer()->get(Connection::class);
    }
}
