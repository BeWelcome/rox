<?php

namespace App\Tests\Legacy;

use App\Entity\Member;
use App\Repository\MemberRepository;
use App\Utilities\SessionSingleton;
use EnvironmentExplorer;
use Group;
use InvalidArgumentException;
use PDB;
use PHPUnit\Framework\Attributes\Group as TestGroup;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PVars;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[TestGroup('integration')]
#[IgnoreDeprecations]
final class GroupTest extends KernelTestCase
{
    public function testDeleteGroupRemovesAllMembershipsAndOwnerScopes(): void
    {
        static::bootKernel();
        $container = static::getContainer();
        $memberRepository = $container->get(MemberRepository::class);
        $owner = $memberRepository->loadUserByIdentifier('bwadmin');
        $applicant = $memberRepository->loadUserByIdentifier('member-1');
        $inactiveMember = $memberRepository->loadUserByIdentifier('member-banned');

        self::assertInstanceOf(Member::class, $owner);
        self::assertInstanceOf(Member::class, $applicant);
        self::assertInstanceOf(Member::class, $inactiveMember);

        try {
            $session = SessionSingleton::getSession();
        } catch (InvalidArgumentException) {
            $session = new Session(new MockArraySessionStorage());
            SessionSingleton::createInstance($session);
        }
        $previousMemberId = $session->get('IdMember');
        $hadMemberId = $session->has('IdMember');
        $session->set('IdMember', $owner->getId());

        try {
            $workingDirectory = getcwd();
            try {
                self::assertTrue(chdir($container->getParameter('kernel.project_dir') . '/public'));
                new EnvironmentExplorer($container->get(UrlGeneratorInterface::class))->initializeGlobalState(
                    $container->getParameter('database_host'),
                    $container->getParameter('database_name'),
                    $container->getParameter('database_user'),
                    $container->getParameter('database_password'),
                    $container->getParameter('manticore.host'),
                    $container->getParameter('manticore.port'),
                );
            } finally {
                chdir($workingDirectory);
            }

            $dao = new Group()->dao;
            $groupId = -random_int(1_000_000, 2_000_000);
            $blockedGroupId = $groupId - 1;
            try {
                $this->createGroupFixture($dao, $groupId, $owner, $applicant, $inactiveMember);
                $group = new Group($groupId);

                $debug = PVars::get()->debug;
                PVars::register('debug', true);
                try {
                    self::assertTrue($group->isLoaded());
                    self::assertSame(3, $this->countRows($dao, 'membersgroups', 'IdGroup', $groupId));
                    self::assertSame(1, $this->countRows($dao, 'privilegescopes', 'IdType', $groupId));
                    self::assertTrue($group->deleteGroup());
                    self::assertSame(0, $this->countRows($dao, 'groups', 'id', $groupId));
                    self::assertSame(0, $this->countRows($dao, 'membersgroups', 'IdGroup', $groupId));
                    self::assertSame(0, $this->countRows($dao, 'privilegescopes', 'IdType', $groupId));

                    $this->createGroupFixture($dao, $blockedGroupId, $owner, $applicant, $inactiveMember);
                    $this->createGroupDeleteBlocker($dao, $blockedGroupId);
                    self::assertSame(1, $this->countRows($dao, 'polls_list_allowed_groups', 'IdGroup', $blockedGroupId));

                    PVars::register('debug', false);
                    self::assertFalse(new Group($blockedGroupId)->deleteGroup());
                    self::assertSame(1, $this->countRows($dao, 'groups', 'id', $blockedGroupId));
                    self::assertSame(3, $this->countRows($dao, 'membersgroups', 'IdGroup', $blockedGroupId));
                    self::assertSame(1, $this->countRows($dao, 'privilegescopes', 'IdType', $blockedGroupId));
                } finally {
                    PVars::register('debug', $debug);
                }
            } finally {
                $this->removeGroupFixture($dao, $groupId);
                $this->removeGroupFixture($dao, $blockedGroupId);
            }
        } finally {
            if ($hadMemberId) {
                $session->set('IdMember', $previousMemberId);
            } else {
                $session->remove('IdMember');
            }
        }
    }

    public function testDeleteGroupContinuesWithoutGroupOwnerRole(): void
    {
        $group = new GroupDeleteDouble(roleExists: false, membershipDeleteResults: [true]);

        self::assertTrue($group->deleteGroup());
        self::assertTrue($group->groupDeleteCalled);
        self::assertSame(['START TRANSACTION', 'COMMIT'], $group->getTransactionStatements());
    }

    public function testDeleteGroupStopsWhenScopeDeletionFails(): void
    {
        $group = new GroupDeleteDouble(scopeDeleteResults: [false]);

        self::assertFalse($group->deleteGroup());
        self::assertFalse($group->groupDeleteCalled);
        self::assertSame(['START TRANSACTION', 'ROLLBACK'], $group->getTransactionStatements());
    }

    public function testDeleteGroupStopsWhenMembershipDeletionFails(): void
    {
        $group = new GroupDeleteDouble(membershipDeleteResults: [false]);

        self::assertFalse($group->deleteGroup());
        self::assertFalse($group->groupDeleteCalled);
        self::assertSame(['START TRANSACTION', 'ROLLBACK'], $group->getTransactionStatements());
    }

    public function testDeleteGroupRollsBackAndRethrowsDeletionException(): void
    {
        $failure = new RuntimeException('Scope deletion failed');
        $group = new GroupDeleteDouble(scopeDeleteResults: [$failure]);

        try {
            $group->deleteGroup();
            self::fail('The deletion exception was not rethrown.');
        } catch (RuntimeException $caught) {
            self::assertSame($failure, $caught);
        }

        self::assertFalse($group->groupDeleteCalled);
        self::assertSame(['START TRANSACTION', 'ROLLBACK'], $group->getTransactionStatements());
    }

    private function createGroupFixture(
        PDB $dao,
        int $groupId,
        Member $owner,
        Member $applicant,
        Member $inactiveMember,
    ): void {
        $dao->exec("INSERT INTO groups (id, Name, Type, created, Picture, MoreInfo, IdDescription, VisiblePosts, approved) VALUES ({$groupId}, 'Issue 80 regression', 'Public', NOW(), '', '', 0, 'yes', 1)");
        foreach (
            [
                [$owner->getId(), 'In'],
                [$applicant->getId(), 'WantToBeIn'],
                [$inactiveMember->getId(), 'In'],
            ] as [$memberId, $status]
        ) {
            $dao->exec("INSERT INTO membersgroups (updated, created, comment, Status, IacceptMassMailFromThisGroup, CanSendGroupMessage, notificationsEnabled, IdMember, IdGroup) VALUES (NOW(), NOW(), 0, '{$status}', 'no', 'yes', 1, {$memberId}, {$groupId})");
        }
        $dao->exec("INSERT INTO privilegescopes (updated, IdType, IdMember, IdRole, IdPrivilege) SELECT NOW(), '{$groupId}', {$owner->getId()}, r.id, p.id FROM roles r CROSS JOIN privileges p WHERE r.name = 'GroupOwner' AND p.controller = 'GroupsController'");
    }

    private function countRows(PDB $dao, string $table, string $column, int $groupId): int
    {
        $result = $dao->query("SELECT COUNT(*) AS count FROM `{$table}` WHERE `{$column}` = '{$groupId}'");

        return (int) $result->fetch(PDB::FETCH_OBJ)->count;
    }

    private function createGroupDeleteBlocker(PDB $dao, int $groupId): void
    {
        $dao->exec("INSERT INTO polls (Status, ResultsVisibility, Type, updated, Started, Ended, created, title, ForMembersOnly, IdLocationsList, IdGroupsList, IdCountriesList, OpenToSubGroups, TypeOfChoice, CanChangeVote, AllowComment, Description, WhereToRestrictMember, Anonym, id) VALUES ('Active', 'AfterVote', 'Group', NOW(), NOW(), NOW(), NOW(), 0, 'no', 0, 0, 0, 'no', 'Single', 'no', 'no', 0, '', 'no', {$groupId})");
        $dao->exec("INSERT INTO polls_list_allowed_groups (IdPoll, IdGroup) VALUES ({$groupId}, {$groupId})");
    }

    private function removeGroupFixture(PDB $dao, int $groupId): void
    {
        $dao->exec("DELETE FROM polls_list_allowed_groups WHERE IdGroup = '{$groupId}'");
        $dao->exec("DELETE FROM polls WHERE id = '{$groupId}'");
        $dao->exec("DELETE FROM privilegescopes WHERE IdType = '{$groupId}'");
        $dao->exec("DELETE FROM membersgroups WHERE IdGroup = '{$groupId}'");
        $dao->exec("DELETE FROM groups WHERE id = '{$groupId}'");
    }
}
