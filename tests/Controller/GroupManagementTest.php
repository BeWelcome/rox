<?php

namespace App\Tests\Controller;

use App\Doctrine\GroupMembershipStatusType;
use App\Entity\Group as GroupEntity;
use App\Entity\GroupCategory;
use App\Entity\GroupMembership;
use App\Entity\Member;
use App\Entity\RelatedGroup;
use App\Model\GroupModel;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

/**
 * What members and admins do with a group, now in src: member settings, leaving, member administration, admins,
 * group settings, related groups.
 */
#[Group('integration')]
final class GroupManagementTest extends WebTestCase
{
    private const int BERLIN = 2950159;

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private GroupEntity $group;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        // one container for the test and its requests, so the entities stay in one entity manager
        $this->client->disableReboot();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->group = $this->entityManager->getRepository(GroupEntity::class)->findOneBy(['name' => 'Hiking Enthusiasts']);
        foreach (['member-1', 'member-2', 'member-4'] as $username) {
            $this->join($this->member($username), $this->group);
        }
        $this->groupModel()->addAdministrator($this->group, $this->member('member-1'));
        $this->entityManager->clear();
        $this->group = $this->entityManager->find(GroupEntity::class, $this->group->getId());
    }

    public function testAMemberChangesTheirSettings(): void
    {
        $this->client->loginUser($this->member('member-2'));
        $crawler = $this->client->request('GET', $this->url('/membersettings'));
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="membership_settings"]')->form();
        $form['membership_settings[comment]'] = 'I love mountains';
        $notifications = $form['membership_settings[notificationsEnabled]'];
        self::assertInstanceOf(ChoiceFormField::class, $notifications);
        $notifications->untick();
        $this->client->submit($form);

        self::assertResponseRedirects($this->url('/membersettings'));
        $membership = $this->membership('member-2');
        self::assertSame('I love mountains', $membership->getComment());
        self::assertFalse($membership->isNotificationsEnabled());
    }

    public function testAMemberLeavesTheGroup(): void
    {
        $this->client->loginUser($this->member('member-2'));
        $crawler = $this->client->request('GET', $this->url('/leave'));
        self::assertResponseIsSuccessful();

        $this->client->submit($crawler->filter('form[action$="/leave/true"]')->form());

        self::assertResponseRedirects($this->url(''));
        self::assertNull($this->membership('member-2'));
    }

    public function testAnAdminCantLeaveBeforeSteppingDown(): void
    {
        $this->client->loginUser($this->member('member-1'));

        $this->client->request('GET', $this->url('/leave'));

        self::assertResponseRedirects($this->url(''));
        self::assertNotNull($this->membership('member-1'));
    }

    public function testOnlyAdminsSeeTheMemberAdministration(): void
    {
        $this->client->loginUser($this->member('member-2'));
        $this->client->request('GET', $this->url('/memberadministration'));
        self::assertResponseStatusCodeSame(403);

        $this->client->loginUser($this->member('member-1'));
        $this->client->request('GET', $this->url('/memberadministration'));
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table', 'member-2');
    }

    public function testAnAdminKicksBansAndPromotesMembers(): void
    {
        $this->client->loginUser($this->member('member-1'));

        $this->adminAction('/kickmember/', 'member-2');
        self::assertNull($this->membership('member-2'));

        $this->adminAction('/banmember/', 'member-4');
        self::assertSame(GroupMembershipStatusType::KICKED_FROM_GROUP, $this->membership('member-4')->getStatus());

        $this->join($this->member('member-2'), $this->group);
        $this->adminAction('/addAdmin/', 'member-2');
        self::assertTrue($this->groupModel()->isAdministrator($this->group, $this->member('member-2')));
    }

    public function testAnAdminCantBeKicked(): void
    {
        $this->groupModel()->addAdministrator($this->group, $this->member('member-2'));
        $this->client->loginUser($this->member('member-1'));

        $crawler = $this->client->request('GET', $this->url('/memberadministration'));

        self::assertCount(0, $crawler->filter('form[action$="/kickmember/' . $this->member('member-2')->getId() . '"]'));
        self::assertFalse($this->groupModel()->removeMember($this->group, $this->member('member-2'), false));
        self::assertNotNull($this->membership('member-2'));
    }

    public function testActionsNeedTheirToken(): void
    {
        $this->client->loginUser($this->member('member-1'));

        $this->client->request('POST', $this->url('/kickmember/' . $this->member('member-2')->getId()), ['_token' => 'wrong']);

        self::assertResponseStatusCodeSame(403);
        self::assertNotNull($this->membership('member-2'));
    }

    public function testAnAdminAcceptsAnApplicant(): void
    {
        $applicant = $this->member('member-5');
        $this->join($applicant, $this->group, GroupMembershipStatusType::APPLIED_FOR_MEMBERSHIP);
        $this->client->loginUser($this->member('member-1'));

        $this->adminAction('/acceptmember/', 'member-5');

        self::assertSame(GroupMembershipStatusType::CURRENT_MEMBER, $this->membership('member-5')->getStatus());
    }

    public function testTheLastAdminCantStepDown(): void
    {
        $this->client->loginUser($this->member('member-1'));

        $crawler = $this->client->request('GET', $this->url(''));
        $this->client->submit($crawler->filter('form[action$="/resignAdmin"]')->form());

        self::assertResponseRedirects($this->url(''));
        self::assertTrue($this->groupModel()->isAdministrator($this->group, $this->member('member-1')));

        $this->groupModel()->addAdministrator($this->group, $this->member('member-2'));
        $crawler = $this->client->request('GET', $this->url(''));
        $this->client->submit($crawler->filter('form[action$="/resignAdmin"]')->form());
        self::assertFalse($this->groupModel()->isAdministrator($this->group, $this->member('member-1')));
    }

    public function testAnAdminChangesTheGroupSettings(): void
    {
        $category = new GroupCategory()->setName('en', 'Outdoors');
        $this->entityManager->persist($category);
        $this->entityManager->flush();
        $this->client->loginUser($this->member('member-1'));

        $crawler = $this->client->request('GET', $this->url('/groupsettings'));
        self::assertResponseIsSuccessful();
        $form = $crawler->filter('form[name="group_settings"]')->form();
        $form['group_settings[description]'] = 'We walk a lot.';
        $form['group_settings[visiblePosts]'] = 'No';
        $form['group_settings[categories]'][0]->tick();
        $form['group_settings[location]'] = (string) self::BERLIN;
        $this->client->submit($form);

        self::assertResponseRedirects($this->url(''));
        $this->entityManager->clear();
        $group = $this->entityManager->find(GroupEntity::class, $this->group->getId());
        self::assertSame('We walk a lot.', $group->getDescription('en'));
        self::assertTrue($group->isMembersOnly());
        self::assertSame(['Outdoors'], array_map(static fn (GroupCategory $c) => $c->getName(), $group->getCategories()));
        self::assertSame(self::BERLIN, $group->getLocation()?->getGeonameId());
    }

    public function testOtherMembersCantChangeTheGroupSettings(): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', $this->url('/groupsettings'));

        self::assertResponseStatusCodeSame(403);
    }

    public function testOnlyTheInvitedMemberAnswersAnInvitation(): void
    {
        $this->join($this->member('member-5'), $this->group, GroupMembershipStatusType::INVITED_INTO_GROUP);
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/group/' . $this->group->getId() . '/accept/' . $this->member('member-5')->getId());

        self::assertResponseStatusCodeSame(403);
        self::assertSame(GroupMembershipStatusType::INVITED_INTO_GROUP, $this->membership('member-5')->getStatus());
    }

    public function testAnAdminWithdrawsAnInvitation(): void
    {
        $invited = $this->member('member-5');
        $this->join($invited, $this->group, GroupMembershipStatusType::INVITED_INTO_GROUP);

        $this->client->loginUser($this->member('member-2'));
        $this->client->request('POST', '/group/' . $this->group->getId() . '/withdraw/' . $invited->getId());
        self::assertResponseStatusCodeSame(403);

        $this->client->loginUser($this->member('member-1'));
        $crawler = $this->client->request('GET', $this->url('/memberadministration'));
        $this->client->submit($crawler->filter('form[action$="/withdraw/' . $invited->getId() . '"]')->form());

        self::assertNull($this->membership('member-5'));
    }

    public function testAMemberLinksAndUnlinksARelatedGroup(): void
    {
        $other = $this->entityManager->getRepository(GroupEntity::class)->findOneBy(['name' => 'Language Exchange']);
        $this->join($this->member('member-2'), $other);
        $this->client->loginUser($this->member('member-2'));

        $crawler = $this->client->request('GET', $this->url('/selectrelatedgroup'));
        $this->client->submit($crawler->filter('form[action$="/addrelatedgroup/' . $other->getId() . '"]')->form());
        self::assertResponseRedirects($this->url('/relatedgroupsettings'));
        self::assertNotNull($this->link($other));

        $crawler = $this->client->request('GET', $this->url('/selectdeleterelatedgroup'));
        $this->client->submit($crawler->filter('form[action$="/deleterelatedgroup/' . $other->getId() . '"]')->form());
        self::assertResponseRedirects($this->url('/relatedgroupsettings'));
        self::assertNull($this->link($other));

        $crawler = $this->client->request('GET', $this->url('/relatedgroupsettings'));
        self::assertResponseIsSuccessful();
        // added and removed again
        self::assertCount(1, $crawler->filter('#related-group-log li'));
        self::assertStringContainsString('Language Exchange', $crawler->filter('#related-group-log')->text());
    }

    private function adminAction(string $path, string $username): void
    {
        $crawler = $this->client->request('GET', $this->url('/memberadministration'));
        $form = $crawler->filter('form[action$="' . $path . $this->member($username)->getId() . '"]');
        self::assertCount(1, $form, "the member administration has a form for {$path}{$username}");

        $this->client->submit($form->form());

        self::assertResponseRedirects($this->url('/memberadministration'));
    }

    private function link(GroupEntity $related): ?RelatedGroup
    {
        $this->entityManager->clear();

        return $this->entityManager->getRepository(RelatedGroup::class)->findOneBy([
            'group' => $this->group->getId(),
            'relatedGroup' => $related->getId(),
            'deletedBy' => null,
        ]);
    }

    private function url(string $path): string
    {
        return '/group/' . $this->group->getId() . $path;
    }

    private function membership(string $username): ?GroupMembership
    {
        $this->entityManager->clear();

        return $this->entityManager->getRepository(GroupMembership::class)->findOneBy([
            'group' => $this->group->getId(),
            'member' => $this->member($username)->getId(),
        ]);
    }

    private function join(Member $member, GroupEntity $group, string $status = GroupMembershipStatusType::CURRENT_MEMBER): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'INSERT INTO group_membership (member_id, group_id, status, accept_mass_mail, notifications_enabled, created, updated)'
            . ' VALUES (?, ?, ?, 0, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE status = VALUES(status)',
            [$member->getId(), $group->getId(), $status],
        );
    }

    private function groupModel(): GroupModel
    {
        return static::getContainer()->get(GroupModel::class);
    }

    private function member(string $username): Member
    {
        $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }
}
