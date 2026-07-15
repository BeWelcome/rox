<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('integration')]
final class GroupControllerTest extends WebTestCase
{
    public function testConfirmedDeleteRemovesAllMembershipsAndOwnerScopes(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();
        $owner = $this->getMember($entityManager, 'member-2');
        $applicant = $this->getMember($entityManager, 'member-1');
        $inactiveMember = $this->getMember($entityManager, 'member-banned');
        $groupId = random_int(1_500_000_000, 2_000_000_000);

        try {
            $this->createGroupFixture($connection, $groupId, $owner, $applicant, $inactiveMember);
            $client->loginUser($owner);

            $client->request('GET', "/group/{$groupId}/delete/true");

            self::assertResponseRedirects('/groups/');
            self::assertSame(0, $this->countRows($connection, 'groups', 'id', $groupId));
            self::assertSame(0, $this->countRows($connection, 'membersgroups', 'IdGroup', $groupId));
            self::assertSame(0, $this->countRows($connection, 'privilegescopes', 'IdType', $groupId));
        } finally {
            $this->removeGroupFixture($connection, $groupId);
        }
    }

    public function testDeleteFailureRollsBackAndReturnsToConfirmation(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();
        $owner = $this->getMember($entityManager, 'member-2');
        $applicant = $this->getMember($entityManager, 'member-1');
        $inactiveMember = $this->getMember($entityManager, 'member-banned');
        $groupId = random_int(1_500_000_000, 2_000_000_000);

        try {
            $this->createGroupFixture($connection, $groupId, $owner, $applicant, $inactiveMember);
            $this->createGroupDeleteBlocker($connection, $groupId);
            $client->loginUser($owner);

            $client->request('GET', "/group/{$groupId}/delete/true");

            self::assertResponseRedirects("/group/{$groupId}/delete");
            self::assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->peek('error'));
            self::assertSame(1, $this->countRows($connection, 'groups', 'id', $groupId));
            self::assertSame(3, $this->countRows($connection, 'membersgroups', 'IdGroup', $groupId));
            self::assertSame(1, $this->countRows($connection, 'privilegescopes', 'IdType', $groupId));
        } finally {
            $this->removeGroupFixture($connection, $groupId);
        }
    }

    private function getMember(EntityManagerInterface $entityManager, string $username): Member
    {
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);

        return $member;
    }

    private function createGroupFixture(
        Connection $connection,
        int $groupId,
        Member $owner,
        Member $applicant,
        Member $inactiveMember,
    ): void {
        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO groups
                    (id, Name, Type, created, Picture, MoreInfo, IdDescription, VisiblePosts, approved)
                VALUES
                    (?, 'Issue 80 regression', 'Public', NOW(), '', '', 0, 'yes', 1)
                SQL,
            [$groupId],
        );

        foreach (
            [
                [$owner->getId(), 'In'],
                [$applicant->getId(), 'WantToBeIn'],
                [$inactiveMember->getId(), 'In'],
            ] as [$memberId, $status]
        ) {
            $connection->executeStatement(
                <<<'SQL'
                    INSERT INTO membersgroups
                        (updated, created, comment, Status, IacceptMassMailFromThisGroup,
                         CanSendGroupMessage, notificationsEnabled, IdMember, IdGroup)
                    VALUES
                        (NOW(), NOW(), 0, ?, 'no', 'yes', 1, ?, ?)
                    SQL,
                [$status, $memberId, $groupId],
            );
        }

        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO privilegescopes (updated, IdType, IdMember, IdRole, IdPrivilege)
                SELECT NOW(), ?, ?, r.id, p.id
                FROM roles r
                CROSS JOIN privileges p
                WHERE r.name = 'GroupOwner' AND p.controller = 'GroupsController'
                SQL,
            [(string) $groupId, $owner->getId()],
        );
    }

    private function createGroupDeleteBlocker(Connection $connection, int $groupId): void
    {
        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO polls
                    (Status, ResultsVisibility, Type, updated, Started, Ended, created, title,
                     ForMembersOnly, IdLocationsList, IdGroupsList, IdCountriesList, OpenToSubGroups,
                     TypeOfChoice, CanChangeVote, AllowComment, Description, WhereToRestrictMember, Anonym, id)
                VALUES
                    ('Active', 'AfterVote', 'Group', NOW(), NOW(), NOW(), NOW(), 0,
                     'no', 0, 0, 0, 'no', 'Single', 'no', 'no', 0, '', 'no', ?)
                SQL,
            [$groupId],
        );
        $connection->executeStatement(
            'INSERT INTO polls_list_allowed_groups (IdPoll, IdGroup) VALUES (?, ?)',
            [$groupId, $groupId],
        );
    }

    private function countRows(Connection $connection, string $table, string $column, int $groupId): int
    {
        return (int) $connection->fetchOne(
            "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?",
            [$groupId],
        );
    }

    private function removeGroupFixture(Connection $connection, int $groupId): void
    {
        $connection->executeStatement('DELETE FROM polls_list_allowed_groups WHERE IdGroup = ?', [$groupId]);
        $connection->executeStatement('DELETE FROM polls WHERE id = ?', [$groupId]);
        $connection->executeStatement('DELETE FROM privilegescopes WHERE IdType = ?', [(string) $groupId]);
        $connection->executeStatement('DELETE FROM membersgroups WHERE IdGroup = ?', [$groupId]);
        $connection->executeStatement('DELETE FROM groups WHERE id = ?', [$groupId]);
    }
}
