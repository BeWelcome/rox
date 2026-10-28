<?php

namespace App\Tests\Controller;

use AdminSubscriptionsModel;
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
 * The admin tools that are still legacy pages (build/admin): the new members list for greeters and the Safety Team,
 * the subscriptions tool, and the page for volunteers without the right. They read develop's tables (member,
 * address, location, member_language_level, group_membership).
 *
 * As in ForumTranslationsTest: the legacy code uses its own database connection (no rollback; tearDown() puts the
 * data back) and keeps state in static singletons (a process per test).
 *
 * @infection-ignore-all
 */
#[Group('integration')]
#[SkipDatabaseRollback]
#[RunTestsInSeparateProcesses]
class LegacyAdminToolsTest extends WebTestCase
{
    private KernelBrowser $client;
    private string $originalWorkingDirectory;
    /** @var array<string, mixed> */
    private array $newMember;

    protected function setUp(): void
    {
        $this->originalWorkingDirectory = (string) getcwd();
        chdir(__DIR__ . '/../../public');
        $this->client = static::createClient();
        // member-6 lives in Jayapura (Papua, Indonesia): a new member for the list
        $this->newMember = $this->connection()->fetchAssociative(
            "SELECT id, Status, created, bewelcomed FROM member WHERE Username = 'member-6'"
        );
        $this->connection()->executeStatement(
            'UPDATE member SET created = NOW() - INTERVAL 2 DAY, bewelcomed = 0 WHERE id = ?',
            [$this->newMember['id']],
        );
    }

    protected function tearDown(): void
    {
        $this->connection()->executeStatement(
            'UPDATE member SET Status = ?, created = ?, bewelcomed = ? WHERE id = ?',
            [$this->newMember['Status'], $this->newMember['created'], $this->newMember['bewelcomed'], $this->newMember['id']],
        );
        $this->connection()->executeStatement('UPDATE members_threads_subscribed SET notificationsEnabled = 1');
        $this->connection()->executeStatement('UPDATE group_membership SET notifications_enabled = 1, accept_mass_mail = 0');
        chdir($this->originalWorkingDirectory);

        parent::tearDown();
    }

    public function testGreetersSeeTheNewMembersWithPlaceAndLanguages(): void
    {
        // member-2 has the NewMembersBeWelcome right in the fixtures
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->legacyRequest('/admin/newmembers');

        $this->assertResponseIsSuccessful();
        $row = $crawler->filter('#newmembers tr')->reduce(static fn (Crawler $tr) => str_contains($tr->text(), 'member-6'));
        self::assertCount(1, $row);
        self::assertStringContainsString('Jayapura, Indonesia', $row->text());
        self::assertStringContainsString('lang_fr', $row->text() . $row->html());
        // the e-mail address is for the Safety Team only
        self::assertStringNotContainsString('@', $row->filter('td')->eq(1)->text());
    }

    public function testTheSafetyTeamAlsoSeesMembersWhoHaveNotFinishedSigningUp(): void
    {
        $this->connection()->executeStatement("UPDATE member SET Status = 'MailConfirmed' WHERE id = ?", [$this->newMember['id']]);
        // bwadmin is in the Safety Team in the fixtures
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->legacyRequest('/admin/newmembers');

        $this->assertResponseIsSuccessful();
        self::assertStringContainsString('member-6', $crawler->filter('#newmembers')->text());
    }

    public function testTheVolunteerMenuLinksGreetersToTheNewMembersList(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/members/member-2');

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('#volunteer_menu a[href$="/admin/newmembers"]'));
    }

    /**
     * checkRights() sends volunteers without the right there; the redirect itself ends with exit(), so it can't be
     * requested within PHPUnit.
     */
    public function testTheNoRightsPageShows(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->legacyRequest('/admin/norights');

        $this->assertResponseIsSuccessful();
        self::assertStringContainsString('Volunteer_Join', $crawler->filter('body')->text());
    }

    public function testSubscriptionsAreSwitchedOffAndOnAgain(): void
    {
        $member = $this->member('member-2');
        $this->connection()->executeStatement('UPDATE group_membership SET accept_mass_mail = 1 WHERE member_id = ?', [$member->getId()]);
        // member-3 has the ManageSubscriptions right in the fixtures
        $this->client->loginUser($this->member('member-3'));
        $this->legacyRequest('/admin/subscriptions');
        $this->assertResponseIsSuccessful();

        $model = new AdminSubscriptionsModel();
        $model->manageSubscriptions(['username' => 'member-2'], false);
        self::assertSame(0, $this->groupNotifications($member->getId()));

        $model->manageSubscriptions(['username' => 'member-2'], true);
        self::assertGreaterThan(0, $this->groupNotifications($member->getId()));
        // the members stay subscribed: no negative member ids
        self::assertSame(0, (int) $this->connection()->fetchOne('SELECT COUNT(*) FROM group_membership WHERE member_id < 0'));
    }

    private function groupNotifications(int $memberId): int
    {
        return (int) $this->connection()->fetchOne(
            'SELECT COALESCE(SUM(notifications_enabled), 0) FROM group_membership WHERE member_id = ?',
            [$memberId],
        );
    }

    /**
     * Requests a legacy page, as ForumTranslationsTest::legacyRequest() does.
     */
    private function legacyRequest(string $url, bool $followRedirects = true): Crawler
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
            $this->client->followRedirects($followRedirects);

            return $this->client->request('GET', $url);
        } finally {
            $_SERVER = $server;
        }
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
