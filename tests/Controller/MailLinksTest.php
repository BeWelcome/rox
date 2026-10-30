<?php

namespace App\Tests\Controller;

use App\Doctrine\GroupMembershipStatusType;
use App\Entity\Friend;
use App\Entity\Group as GroupEntity;
use App\Entity\Member;
use App\Model\GroupModel;
use App\Service\MailLinkSigner;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Mime\Email;

/**
 * The links in mails that accept or decline an invitation, a join request or a friendship work with their
 * signature only, for 30 days; without it the member gets a note and the page.
 */
#[Group('integration')]
final class MailLinksTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testAnInvitationIsAnsweredWithTheSignedLinkOnly(): void
    {
        $group = $this->group('Hiking Enthusiasts');
        $member = $this->member('member-2');
        $this->setMembership($group, $member, GroupMembershipStatusType::INVITED_INTO_GROUP);
        $this->client->loginUser($member);
        $parameters = ['groupId' => $group->getId(), 'memberId' => $member->getId()];

        $this->assertRejected($this->path('accept_invite_to_group', $parameters), '/group/' . $group->getId());
        $this->assertRejected($this->tampered('accept_invite_to_group', $parameters), '/group/' . $group->getId());
        $this->assertRejected($this->expired('accept_invite_to_group', $parameters), '/group/' . $group->getId());
        // the links of old mails
        $oldLink = '/group/' . $group->getId() . '/acceptinvitation/' . $member->getId();
        $this->assertRejected($oldLink, '/group/' . $group->getId());
        self::assertSame(GroupMembershipStatusType::INVITED_INTO_GROUP, $this->membershipStatus($group, $member));

        $this->client->request('GET', $this->signed('accept_invite_to_group', $parameters));
        self::assertResponseRedirects('/group/' . $group->getId());
        self::assertSame(GroupMembershipStatusType::CURRENT_MEMBER, $this->membershipStatus($group, $member));
    }

    public function testAJoinRequestIsAnsweredWithTheSignedLinkOnly(): void
    {
        $group = $this->group('Vegan Travelers');
        $admin = $this->member('member-3');
        $member = $this->member('member-2');
        $this->setMembership($group, $admin, GroupMembershipStatusType::CURRENT_MEMBER);
        static::getContainer()->get(GroupModel::class)->addAdministrator($group, $admin);
        $this->setMembership($group, $member, GroupMembershipStatusType::APPLIED_FOR_MEMBERSHIP);
        $this->client->loginUser($admin);
        $parameters = ['groupId' => $group->getId(), 'memberId' => $member->getId()];
        $administration = '/group/' . $group->getId() . '/memberadministration';

        $this->assertRejected($this->path('group_accept_join', $parameters), $administration);
        $this->assertRejected($this->expired('group_decline_join', $parameters), $administration);
        self::assertSame(GroupMembershipStatusType::APPLIED_FOR_MEMBERSHIP, $this->membershipStatus($group, $member));

        $this->client->request('GET', $this->signed('group_accept_join', $parameters));
        self::assertResponseRedirects('/group/' . $group->getId());
        self::assertSame(GroupMembershipStatusType::CURRENT_MEMBER, $this->membershipStatus($group, $member));
    }

    public function testAFriendshipIsConfirmedWithTheSignedLinkOnly(): void
    {
        $receiver = $this->member('member-3');
        $sender = $this->member('member-2');
        $this->client->loginUser($sender);

        // asked for on the page (with the form's token); the mail has the signed links
        $crawler = $this->client->request('GET', '/members/member-3/friendship/add');
        $this->client->submit($crawler->filter('button[name="add_friendship"]')->form());
        $mail = $this->mailTo($receiver);
        self::assertStringContainsString('_hash=', $mail);
        self::assertStringContainsString('_expiration=', $mail);

        // the sender can't confirm their own request by making up the link
        $parameters = ['left' => 'member-2', 'right' => 'member-3'];
        $this->assertRejected($this->path('confirm_friendship', $parameters), '/members/member-3/friendship/edit');
        self::assertFalse($this->friendship($sender, $receiver)->getLeftConfirmed());

        $this->client->loginUser($receiver);
        $parameters = ['left' => 'member-3', 'right' => 'member-2'];
        $this->assertRejected($this->expired('confirm_friendship', $parameters), '/members/member-2/friendship/edit');
        $this->client->request('GET', $this->signed('confirm_friendship', $parameters));
        self::assertResponseRedirects('/members/member-3/friends');
        self::assertTrue($this->friendship($sender, $receiver)->getLeftConfirmed());
    }

    public function testTheFriendshipFormsNeedTheirToken(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('POST', '/members/member-3/friendship/add', ['add_friendship' => 'add']);
        self::assertResponseStatusCodeSame(403);
        $this->client->request('POST', '/members/member-3/friendship/edit', ['remove_friendship' => 'remove']);
        self::assertResponseStatusCodeSame(403);
    }

    private function assertRejected(string $url, string $page): void
    {
        $this->client->request('GET', $url);
        self::assertResponseRedirects($page, null, $url);
        $this->client->followRedirect();
        self::assertSelectorTextContains('#toasts, .alert, body', 'flash.mail.link.expired');
        $this->entityManager->clear();
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function signed(string $route, array $parameters): string
    {
        $this->entityManager->clear();

        return static::getContainer()->get(MailLinkSigner::class)->signedUrl($route, $parameters);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function expired(string $route, array $parameters): string
    {
        $signer = static::getContainer()->get(UriSigner::class);

        return $signer->sign($this->url($route, $parameters), new DateTime('-1 day'));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function tampered(string $route, array $parameters): string
    {
        return preg_replace('/_hash=[^&]+/', '_hash=tampered', $this->signed($route, $parameters));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function path(string $route, array $parameters): string
    {
        return static::getContainer()->get('router')->generate($route, $parameters);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function url(string $route, array $parameters): string
    {
        return static::getContainer()->get('router')->generate($route, $parameters, 0);
    }

    private function setMembership(GroupEntity $group, Member $member, string $status): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'INSERT INTO group_membership
                (member_id, group_id, status, accept_mass_mail, notifications_enabled, created, updated)
             VALUES (?, ?, ?, 0, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE status = VALUES(status)',
            [$member->getId(), $group->getId(), $status],
        );
    }

    private function membershipStatus(GroupEntity $group, Member $member): string
    {
        return (string) $this->entityManager->getConnection()->fetchOne(
            'SELECT status FROM group_membership WHERE group_id = ? AND member_id = ?',
            [$group->getId(), $member->getId()],
        );
    }

    private function friendship(Member $one, Member $other): Friend
    {
        $this->entityManager->clear();
        $friendship = $this->entityManager->getRepository(Friend::class)->findFriendshipBetween(
            $this->member($one->getUsername()),
            $this->member($other->getUsername()),
        );
        self::assertInstanceOf(Friend::class, $friendship);

        return $friendship;
    }

    private function mailTo(Member $member): string
    {
        foreach (array_reverse(self::getMailerMessages()) as $message) {
            if ($message instanceof Email && $message->getTo()[0]->getAddress() === $member->getEmail()) {
                return (string) $message->getHtmlBody();
            }
        }
        self::fail('No mail to ' . $member->getUsername());
    }

    private function group(string $name): GroupEntity
    {
        $group = $this->entityManager->getRepository(GroupEntity::class)->findOneBy(['name' => $name]);
        self::assertInstanceOf(GroupEntity::class, $group);

        return $group;
    }

    private function member(string $username): Member
    {
        $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
