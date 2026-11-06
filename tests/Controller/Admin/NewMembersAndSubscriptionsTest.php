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
 * The new members list for the greeters and the Safety Team, and the tool that turns the forum notifications of a
 * member on or off. In the fixtures member-2 greets new members, bwadmin is in the Safety Team, member-3 manages
 * subscriptions and member-6 (Jayapura, Indonesia) is the new member. The greeters choose their countries and send a
 * standard greeting to many new members at once.
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
        self::assertCount(0, $row->filter('.c-newmembers__email'));
        self::assertCount(0, $row->filter('select[name="status"]'));
    }

    public function testTheSafetyTeamAlsoSeesMembersWhoHaveNotFinishedSigningUp(): void
    {
        $this->connection()->executeStatement("UPDATE member SET Status = 'MailConfirmed' WHERE id = ?", [$this->newMember]);
        $this->client->loginUser($this->member('bwadmin'));

        $crawler = $this->client->request('GET', '/admin/newmembers');

        $this->assertResponseIsSuccessful();
        $row = $this->row($crawler, 'member-6');
        self::assertStringContainsString('@', $row->filter('.c-newmembers__email')->text());
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
        self::assertCount(0, $crawler->filter('.js-new-member[data-username="member-6"]'));
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

    public function testTheVolunteersCountriesAreSavedAndFilterTheList(): void
    {
        $country = $this->countryOfTheNewMember();
        $this->client->loginUser($this->member('member-2'));
        $token = $this->token($this->client->request('GET', '/admin/newmembers'));

        $this->client->request('POST', '/admin/newmembers/countries', ['_token' => $token, 'add' => $country]);
        $this->assertResponseRedirects('/admin/newmembers');
        self::assertSame((string) $country, $this->savedCountries());

        $crawler = $this->client->request('GET', '/admin/newmembers');
        self::assertCount(1, $crawler->filter('.c-newmembers__pill input[name="remove"][value="' . $country . '"]'));
        $this->row($crawler, 'member-6');

        // another country only: member-6 isn't shown
        $this->client->request('POST', '/admin/newmembers/countries', ['_token' => $token, 'remove' => $country]);
        $this->client->request('POST', '/admin/newmembers/countries', ['_token' => $token, 'add' => $this->anotherCountry($country)]);
        $crawler = $this->client->request('GET', '/admin/newmembers');
        self::assertCount(0, $crawler->filter('.js-new-member[data-username="member-6"]'));

        $this->client->request('POST', '/admin/newmembers/countries', ['_token' => 'wrong', 'remove' => $this->anotherCountry($country)]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testTheProfileFilterShowsMembersWithoutPhotoAndText(): void
    {
        $this->connection()->executeStatement("UPDATE member SET AboutMe = '' WHERE id = ?", [$this->newMember]);
        $this->connection()->executeStatement('DELETE FROM member_photo WHERE member_id = ?', [$this->newMember]);
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/admin/newmembers?profile=empty');
        $this->assertResponseIsSuccessful();
        self::assertStringContainsString('is-empty', (string) $this->row($crawler, 'member-6')->attr('class'));

        $crawler = $this->client->request('GET', '/admin/newmembers?profile=full');
        self::assertCount(0, $crawler->filter('.js-new-member[data-username="member-6"]'));
    }

    public function testTheStandardGreetingIsAMessageToEachMember(): void
    {
        $this->client->loginUser($this->member('member-2'));
        $token = $this->token($this->client->request('GET', '/admin/newmembers'));
        $messages = $this->messagesTo($this->newMember);

        $this->client->request('POST', '/admin/newmembers/greeting/global', [
            '_token' => $token,
            'usernames' => ['member-6'],
            'subject' => 'Welcome, %username%!',
            'text' => "Hi %username%,\n\nwelcome <b>here</b>.",
        ]);

        $this->assertResponseRedirects('/admin/newmembers');
        self::assertSame($messages + 1, $this->messagesTo($this->newMember));
        $message = $this->connection()->fetchAssociative(
            'SELECT m.message, m.sender_id, s.subject FROM message m JOIN subject s ON s.id = m.subject_id WHERE m.receiver_id = ? ORDER BY m.id DESC LIMIT 1',
            [$this->newMember],
        );
        self::assertSame('Welcome, member-6!', $message['subject']);
        self::assertStringContainsString('Hi member-6,', $message['message']);
        self::assertStringContainsString('&lt;b&gt;here&lt;/b&gt;', $message['message']);
        self::assertSame($this->member('member-2')->getId(), (int) $message['sender_id']);
        self::assertSame(2, $this->bewelcomed());

        $crawler = $this->client->followRedirect();
        $sent = static::getContainer()->get(TranslatorInterface::class)->trans('admin.newmembers.bulk.sent.global', ['count' => 1]);
        self::assertStringContainsString($sent, $crawler->text());
    }

    public function testTheStandardGreetingNeedsMembersATextAndTheToken(): void
    {
        $this->client->loginUser($this->member('member-2'));
        $token = $this->token($this->client->request('GET', '/admin/newmembers'));
        $messages = $this->messagesTo($this->newMember);

        $this->client->request('POST', '/admin/newmembers/greeting/local', ['_token' => $token, 'usernames' => [], 'subject' => 'Hi', 'text' => 'Hi']);
        $this->assertResponseRedirects('/admin/newmembers');
        $this->client->request('POST', '/admin/newmembers/greeting/local', ['_token' => $token, 'usernames' => ['member-6'], 'subject' => 'Hi', 'text' => ' ']);
        $this->client->request('POST', '/admin/newmembers/greeting/local', ['_token' => 'wrong', 'usernames' => ['member-6'], 'subject' => 'Hi', 'text' => 'Hi']);
        $this->assertResponseStatusCodeSame(403);

        $this->client->loginUser($this->member('member-6'));
        $this->client->request('POST', '/admin/newmembers/greeting/local', ['_token' => $token, 'usernames' => ['member-6'], 'subject' => 'Hi', 'text' => 'Hi']);
        $this->assertResponseStatusCodeSame(403);

        self::assertSame($messages, $this->messagesTo($this->newMember));
        self::assertSame(0, $this->bewelcomed());
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
        $row = $crawler->filter('.js-new-member[data-username="' . $username . '"]');
        self::assertCount(1, $row);

        return $row;
    }

    private function token(Crawler $crawler): string
    {
        return (string) $crawler->filter('form[action*="/admin/newmembers/"] input[name="_token"]')->first()->attr('value');
    }

    private function countryOfTheNewMember(): int
    {
        return (int) $this->connection()->fetchOne(
            'SELECT g.country FROM address a JOIN location g ON g.geoname_id = a.location WHERE a.member_id = ? AND a.active = 1',
            [$this->newMember],
        );
    }

    private function anotherCountry(int $country): int
    {
        return (int) $this->connection()->fetchOne(
            'SELECT DISTINCT g.country FROM address a JOIN location g ON g.geoname_id = a.location WHERE g.country <> ? AND g.country IS NOT NULL LIMIT 1',
            [$country],
        );
    }

    private function savedCountries(): string
    {
        return (string) $this->connection()->fetchOne(
            "SELECT mp.Value FROM memberspreferences mp JOIN preferences p ON p.id = mp.IdPreference WHERE p.codeName = 'NewMembersCountries' AND mp.IdMember = ?",
            [$this->member('member-2')->getId()],
        );
    }

    private function messagesTo(int $memberId): int
    {
        return (int) $this->connection()->fetchOne('SELECT COUNT(*) FROM message WHERE receiver_id = ?', [$memberId]);
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
