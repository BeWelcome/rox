<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The new members list for the greeters and the Safety Team, and the tool that turns the forum notifications of a
 * member on or off. In the fixtures member-2 greets new members, bwadmin is in the Safety Team, member-3 manages
 * subscriptions and member-6 (Jayapura, Indonesia) is the new member.
 */
#[Group('integration')]
class NewMembersAndSubscriptionsTest extends WebTestCase
{
    private KernelBrowser $client;
    private int $newMember;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->newMember = (int) $this->connection()->fetchOne("SELECT id FROM member WHERE Username = 'member-6'");
        $this->connection()->executeStatement(
            'UPDATE member SET created = NOW() - INTERVAL 2 DAY, bewelcomed = 0 WHERE id = ?',
            [$this->newMember],
        );
    }

    public function testGreetersSeeTheNewMembersWithPlaceAndLanguages(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/admin/newmembers');

        $this->assertResponseIsSuccessful();
        $row = $this->row($crawler, 'member-6');
        self::assertStringContainsString('Jayapura, Indonesia', $row->text());
        self::assertCount(2, $row->filter('form[action$="/greeting/local"], form[action$="/greeting/global"]'));
        // the e-mail address and the status are for the Safety Team only
        self::assertStringNotContainsString('@', $row->filter('td')->eq(1)->text());
        self::assertCount(0, $row->filter('select[name="status"]'));
    }

    public function testTheSafetyTeamAlsoSeesMembersWhoHaveNotFinishedSigningUp(): void
    {
        $this->connection()->executeStatement("UPDATE member SET Status = 'MailConfirmed' WHERE id = ?", [$this->newMember]);
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->client->request('GET', '/admin/newmembers');

        $this->assertResponseIsSuccessful();
        $row = $this->row($crawler, 'member-6');
        self::assertStringContainsString('@', $row->filter('td')->eq(1)->text());
        self::assertCount(1, $row->filter('select[name="status"]'));
    }

    public function testAGreetingIsMarkedAsSentAndOpensTheMessage(): void
    {
        $this->client->loginUser($this->member('member-2'));
        $token = $this->token($this->client->request('GET', '/admin/newmembers'));

        $this->client->request('POST', '/admin/newmembers/member-6/greeting/local', ['_token' => $token]);
        $this->assertResponseRedirects('/new/message/member-6');
        self::assertSame(1, $this->bewelcomed());

        $this->client->request('POST', '/admin/newmembers/member-6/greeting/global', ['_token' => $token]);
        self::assertSame(3, $this->bewelcomed());

        // greeted twice: not in the greeters' list any longer
        $crawler = $this->client->request('GET', '/admin/newmembers');
        self::assertCount(0, $crawler->filter('tr.js-new-member[data-username="member-6"]'));
    }

    public function testAGreetingNeedsTheToken(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('POST', '/admin/newmembers/member-6/greeting/local', ['_token' => 'wrong']);

        $this->assertResponseStatusCodeSame(403);
        self::assertSame(0, $this->bewelcomed());
    }

    public function testTheSafetyTeamChangesTheStatus(): void
    {
        $this->client->loginUser($this->member('bwadmin'));
        $token = $this->token($this->client->request('GET', '/admin/newmembers'));

        $this->client->request('POST', '/admin/newmembers/member-6/status', ['_token' => $token, 'status' => 'SuspendedBeta', 'page' => 1]);
        $this->assertResponseRedirects('/admin/newmembers');
        self::assertSame('SuspendedBeta', $this->memberStatus());

        $this->client->request('POST', '/admin/newmembers/member-6/status', ['_token' => $token, 'status' => 'Renamed']);
        $this->assertResponseStatusCodeSame(404);
        self::assertSame('SuspendedBeta', $this->memberStatus());
    }

    public function testGreetersDontChangeTheStatus(): void
    {
        $this->client->loginUser($this->member('member-2'));
        $token = $this->token($this->client->request('GET', '/admin/newmembers'));

        $this->client->request('POST', '/admin/newmembers/member-6/status', ['_token' => $token, 'status' => 'Banned']);

        $this->assertResponseStatusCodeSame(403);
        self::assertSame('Active', $this->memberStatus());
    }

    public function testOthersDontSeeTheList(): void
    {
        $this->client->loginUser($this->member('member-6'));

        $this->client->request('GET', '/admin/newmembers');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testTheOldUrlsLeadToTheNewOnes(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/admin/newmembers/page/2');
        $this->assertResponseRedirects('/admin/newmembers/2', 301);

        $this->client->request('GET', '/admin/norights');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testTheVolunteerMenuLinksTheTools(): void
    {
        $this->client->loginUser($this->member('member-2'));
        $crawler = $this->client->request('GET', '/members/member-2');
        self::assertCount(1, $crawler->filter('#volunteer_menu a[href$="/admin/newmembers"]'));

        $this->client->loginUser($this->member('member-3'));
        $crawler = $this->client->request('GET', '/members/member-3');
        self::assertCount(1, $crawler->filter('#volunteer_menu a[href$="/admin/subscriptions"]'));
    }

    public function testTheForumNotificationsAreSwitchedOffAndOnAgain(): void
    {
        $member = $this->member('member-2');
        $this->connection()->executeStatement('UPDATE group_membership SET accept_mass_mail = 1, notifications_enabled = 1 WHERE member_id = ?', [$member->getId()]);
        $this->client->loginUser($this->member('member-3'));

        $this->client->request('GET', '/admin/subscriptions');
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('forum_notifications[disable]', ['forum_notifications[username]' => 'member-2']);
        $this->assertResponseRedirects('/admin/subscriptions');
        self::assertSame(0, $this->notifications($member->getId()));

        $this->client->request('GET', '/admin/subscriptions');
        $this->client->submitForm('forum_notifications[enable]', ['forum_notifications[username]' => 'member-2']);
        self::assertGreaterThan(0, $this->notifications($member->getId()));
    }

    public function testAnUnknownMemberIsShown(): void
    {
        $this->client->loginUser($this->member('member-3'));
        $this->client->request('GET', '/admin/subscriptions');

        $crawler = $this->client->submitForm('forum_notifications[disable]', ['forum_notifications[username]' => 'nobody-here']);

        $this->assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filter('.invalid-feedback, .form-error-message')->count());
    }

    public function testTheToolNeedsTheRight(): void
    {
        $this->client->loginUser($this->member('member-6'));

        $this->client->request('GET', '/admin/subscriptions');

        $this->assertResponseStatusCodeSame(403);
    }

    private function row(Crawler $crawler, string $username): Crawler
    {
        $row = $crawler->filter('tr.js-new-member[data-username="' . $username . '"]');
        self::assertCount(1, $row);

        return $row;
    }

    private function token(Crawler $crawler): string
    {
        return (string) $crawler->filter('form[action*="/greeting/"] input[name="_token"], form[action$="/status"] input[name="_token"]')->first()->attr('value');
    }

    private function bewelcomed(): int
    {
        return (int) $this->connection()->fetchOne('SELECT bewelcomed FROM member WHERE id = ?', [$this->newMember]);
    }

    private function memberStatus(): string
    {
        return (string) $this->connection()->fetchOne('SELECT Status FROM member WHERE id = ?', [$this->newMember]);
    }

    private function notifications(int $memberId): int
    {
        return (int) $this->connection()->fetchOne(
            'SELECT COALESCE(SUM(notifications_enabled), 0) FROM group_membership WHERE member_id = ?',
            [$memberId],
        ) + (int) $this->connection()->fetchOne(
            'SELECT COALESCE(SUM(notificationsEnabled), 0) FROM members_threads_subscribed WHERE IdSubscriber = ?',
            [$memberId],
        );
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
