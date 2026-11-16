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
 * Only active members (Active, OutOfRemind) write messages, hosting requests and invitations, plan trips and create
 * groups (Member::isActiveMember(), ActiveMemberTrait). The status is the first reason: a member who hasn't given the
 * details of the sign-up goes to its setup page, one who hasn't confirmed the address back where they came from,
 * with a notice, whatever the profile has (member-2's has a picture and an About me).
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
        ];
        foreach ($forms as $form => $elsewhere) {
            $this->client->loginUser($member);
            $this->client->request('GET', $form);

            self::assertResponseRedirects($detailsGiven ? $elsewhere : $setup, null, $form . ' (' . $status . ')');
            $this->client->followRedirect();
            self::assertSelectorExists('#toasts .bw-toast', $form . ' (' . $status . '): no notice');
        }
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
