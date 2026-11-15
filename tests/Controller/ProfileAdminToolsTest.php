<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The Admin box of a profile: folded by default, its links lead to the member's edit page, rights and logs; admins and
 * the safety team edit the member's profile there, also the switches that save on their own (the guests), and their
 * account, the email address included.
 */
#[Group('integration')]
class ProfileAdminToolsTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->connection = $this->entityManager->getConnection();
    }

    public function testTheBoxIsFoldedAndLeadsToTheMembersPages(): void
    {
        $this->grant('member-2', ['SafetyTeam', 'Rights', 'Logs']);
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', '/members/member-3');

        self::assertResponseIsSuccessful();
        // one per profile language of the page, all folded
        $boxes = $crawler->filter('details.p-pro-admin-tools');
        self::assertGreaterThan(0, $boxes->count());
        self::assertCount(0, $crawler->filter('details.p-pro-admin-tools[open]'));
        $links = $boxes->first()->filter('.p-pro-admin-tools__link')->each(static fn ($link): string => (string) $link->attr('href'));
        self::assertContains('/members/member-3/edit', $links);
        self::assertContains('/admin/rights/list/member/member-3', $links);
        self::assertContains('/admin/logs?log%5Busername%5D=member-3', $links);
        foreach ($links as $link) {
            $this->client->request('GET', $link);
            self::assertResponseIsSuccessful($link);
        }
    }

    public function testTheSafetyTeamEditsAMembersProfile(): void
    {
        $this->grant('member-2', ['SafetyTeam']);
        $this->client->loginUser($this->member('member-2'));
        $guests = $this->member('member-2')->getMaxGuests();

        $this->client->request('GET', '/members/member-3/edit');
        self::assertResponseIsSuccessful();
        $this->client->request('GET', '/members/member-3/edit/en/aboutme');
        self::assertResponseIsSuccessful();

        // the guests switch saves for the member whose profile it is, not for the volunteer
        $this->client->request('POST', '/members/update/maxguests', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode(['maxGuests' => 7, 'username' => 'member-3']));
        self::assertSame(7, $this->member('member-3')->getMaxGuests());
        self::assertSame($guests, $this->member('member-2')->getMaxGuests());
    }

    public function testTheSafetyTeamChangesAMembersEmailAddress(): void
    {
        // the account page and its email change, without the member's password; the new address waits for the member
        // to confirm it (a mail to it)
        $this->grant('member-2', ['SafetyTeam']);
        $this->client->loginUser($this->member('member-2'));
        $address = 'zztest-new-' . uniqid() . '@example.test';

        $this->client->request('GET', '/members/member-3/account');
        self::assertResponseIsSuccessful();
        $crawler = $this->client->request('GET', '/members/member-3/account/email');
        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('input[type=password]'));
        $this->client->submit($crawler->filter('form.p-account-form')->form(), ['change_email_form[email]' => $address]);

        self::assertResponseRedirects('/members/member-3/account');
        self::assertEmailCount(1);
        self::assertEmailAddressContains(self::getMailerMessage(), 'To', $address);
        self::assertSame($address, $this->member('member-3')->getNewEmail());
        self::assertNull($this->member('member-2')->getNewEmail());
    }

    public function testOthersCantEditAMembersProfile(): void
    {
        $this->client->loginUser($this->member('member-2'));
        $guests = $this->member('member-3')->getMaxGuests();

        $this->client->request('GET', '/members/member-3/edit');
        self::assertResponseRedirects('/members/member-2/edit');
        $this->client->request('GET', '/members/member-3/account');
        self::assertResponseRedirects('/members/member-2/account');
        $this->client->request('GET', '/members/member-3/account/email');
        self::assertResponseRedirects('/members/member-2/account/email');

        $this->client->request('POST', '/members/update/maxguests', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode(['maxGuests' => 9, 'username' => 'member-3']));
        self::assertSame($guests, $this->member('member-3')->getMaxGuests());
    }

    /**
     * @param list<string> $rights names of rights (SafetyTeam, Logs, ...)
     */
    private function grant(string $username, array $rights): void
    {
        foreach ($rights as $right) {
            $this->connection->executeStatement(
                "INSERT INTO rightsvolunteers (IdMember, IdRight, Level, Scope, Comment, created, updated)
                 SELECT m.id, r.id, 10, '\"All\"', 'test', NOW(), NOW() FROM member m, rights r WHERE m.Username = ? AND r.Name = ?
                 ON DUPLICATE KEY UPDATE Level = 10",
                [$username, $right],
            );
        }
    }

    private function member(string $username): Member
    {
        $this->entityManager->clear();

        return $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
    }
}
