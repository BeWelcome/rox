<?php

namespace App\Tests\Controller;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Only active members (Active, OutOfRemind) write messages, hosting requests, invitations and comments, ask for
 * friendships, plan trips, create groups, activities and polls, and ask to join groups that need approval
 * (Member::isActiveMember(), ActiveMemberTrait). The status is the first reason: a member who hasn't given the
 * details of the sign-up goes to its setup page, one who hasn't confirmed the address back where they came from,
 * with a notice, whatever the profile has (member-2's has a picture and an About me). Neither comments on community
 * news, edits the wiki or answers an activity (shown no box, no editor, no answer buttons).
 */
#[Group('integration')]
class NotActiveMembersTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @return iterable<string, array{string, bool}> the status, whether the member gave the details of the sign-up
     */
    public static function notActiveYet(): iterable
    {
        yield 'just signed up' => [MemberStatusType::AWAITING_MAIL_CONFIRMATION, false];
        yield 'address confirmed, no details' => [MemberStatusType::MAIL_CONFIRMED, false];
        yield 'details given, address not confirmed' => [MemberStatusType::ACCOUNT_ACTIVATED, true];
    }

    #[DataProvider('notActiveYet')]
    public function testAMemberNotActiveYetIsTurnedDown(string $status, bool $detailsGiven): void
    {
        [$leg, $traveller] = $this->otherMembersLeg();
        $member = $this->memberWithStatus('member-2', $status);
        $setup = '/members/member-2/finalize';

        $forms = [
            '/new/trip' => '/trips',
            '/new/group' => '/groups/mygroups',
            '/new/message/member-3' => '/members/member-3',
            '/new/request/member-3' => '/members/member-3',
            '/new/invitation/' . $leg => '/members/' . $traveller,
            '/members/member-3/comment/add' => '/members/member-3',
            '/members/member-3/friendship/add' => '/members/member-3',
            '/activities/create' => '/activities/myactivities',
            '/polls/create' => '/polls',
            // the fixtures' group that needs approval
            '/group/3/join' => '/group/3',
        ];
        foreach ($forms as $form => $elsewhere) {
            $this->client->loginUser($member);
            $this->client->request('GET', $form);

            self::assertResponseRedirects($detailsGiven ? $elsewhere : $setup, null, $form . ' (' . $status . ')');
            $this->client->followRedirect();
            self::assertSelectorExists('#toasts .bw-toast', $form . ' (' . $status . '): no notice');
        }
    }

    #[DataProvider('notActiveYet')]
    public function testAMemberNotActiveYetNeitherCommentsOnNewsNorEditsTheWikiNorAnswersActivities(string $status, bool $detailsGiven): void
    {
        // the same for all: these pages show no box, editor or buttons, whether or not the details were given
        unset($detailsGiven);
        $this->client->loginUser($this->memberWithStatus('member-2', $status));

        $crawler = $this->client->request('GET', '/communitynews/1');
        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('form[data-comment-form], form[data-reply-form]'), 'a comment box (' . $status . ')');

        $this->client->request('GET', '/wiki/Zztest_not_active/edit');
        self::assertResponseRedirects();
        self::assertStringStartsWith('/wiki/', (string) $this->client->getResponse()->headers->get('Location'));
        self::assertStringNotContainsString('/edit', (string) $this->client->getResponse()->headers->get('Location'));

        $activity = (int) static::getContainer()->get(Connection::class)
            ->fetchOne('SELECT id FROM activity WHERE status = 0 ORDER BY id LIMIT 1');
        $this->client->request('POST', '/activities/' . $activity . '/answer', ['activity_attend' => ['status' => 1]]);
        self::assertResponseStatusCodeSame(403, 'an answer to the activity (' . $status . ')');
    }

    #[DataProvider('notActiveYet')]
    public function testAMemberNotActiveYetWritesPrivateNotes(string $status, bool $detailsGiven): void
    {
        // private: open to every member who can log in
        unset($detailsGiven);
        $this->client->loginUser($this->memberWithStatus('member-2', $status));

        $crawler = $this->client->request('GET', '/members/member-3/note/add');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('main form'), 'no note form (' . $status . ')');
    }

    public function testAnActiveMemberGetsTheForms(): void
    {
        $member = $this->memberWithStatus('member-2', MemberStatusType::ACTIVE);
        $this->client->loginUser($member);

        $this->client->request('GET', '/new/trip');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name=trip]');
        $this->client->request('GET', '/new/group');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name=group]');
    }

    public function testInTheSidePanelTheRefusalIsShownThere(): void
    {
        $member = $this->memberWithStatus('member-2', MemberStatusType::MAIL_CONFIRMED);
        $this->client->loginUser($member);

        $this->client->request('GET', '/new/message/member-3?embed=1');

        // the panel says why instead of loading the setup page into it
        self::assertResponseIsSuccessful();
    }

    private function memberWithStatus(string $username, string $status): Member
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);
        $member->setStatus($status);
        $entityManager->flush();

        return $member;
    }

    /**
     * @return array{int, string} a leg of a trip of another member than member-2, and that member's username
     */
    private function otherMembersLeg(): array
    {
        $row = static::getContainer()->get(Connection::class)->fetchAssociative(
            "SELECT l.id, m.Username FROM leg l JOIN trip t ON t.id = l.trip_id JOIN member m ON m.id = t.creator_id
             WHERE l.removed IS NULL AND t.deleted IS NULL AND m.Username <> 'member-2' ORDER BY l.id LIMIT 1",
        );
        self::assertIsArray($row, 'the fixtures have no trip of another member');

        return [(int) $row['id'], (string) $row['Username']];
    }
}
