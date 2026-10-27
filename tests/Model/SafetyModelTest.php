<?php

namespace App\Tests\Model;

use App\Entity\Member;
use App\Model\SafetyModel;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The safety team shown on /safety/team: active members with the SafetyTeam right, read from member (not main's
 * members table).
 */
#[Group('integration')]
class SafetyModelTest extends KernelTestCase
{
    private Connection $connection;
    private SafetyModel $safetyModel;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->connection = static::getContainer()->get(Connection::class);
        $this->safetyModel = static::getContainer()->get(SafetyModel::class);
    }

    public function testListsTheActiveMembersWithTheSafetyTeamRight(): void
    {
        self::assertSame(['bwadmin'], $this->usernames());
    }

    public function testLeavesOutMembersWithoutAnActiveRightOrStatus(): void
    {
        $safetyTeam = (int) $this->connection->fetchOne("SELECT id FROM rights WHERE Name = 'SafetyTeam'");
        [$levelZero, $banned, $second] = $this->connection->fetchFirstColumn(
            "SELECT id FROM member WHERE Status = 'Active' AND Username <> 'bwadmin' ORDER BY id LIMIT 3"
        );
        foreach ([[$levelZero, 0], [$banned, 10], [$second, 1]] as [$member, $level]) {
            $this->connection->executeStatement(
                "INSERT INTO rightsvolunteers (IdMember, IdRight, Level, Scope, Comment, created, updated)
                 VALUES (?, ?, ?, '\"All\"', 'test', NOW(), NOW())",
                [$member, $safetyTeam, $level]
            );
        }
        $this->connection->executeStatement("UPDATE member SET Status = 'Banned' WHERE id = ?", [$banned]);

        $expected = $this->connection->fetchFirstColumn(
            'SELECT Username FROM member WHERE id IN (?, ?) ORDER BY Username',
            [$this->connection->fetchOne("SELECT id FROM member WHERE Username = 'bwadmin'"), $second]
        );
        self::assertSame($expected, $this->usernames());
    }

    /**
     * @return list<string>
     */
    private function usernames(): array
    {
        return array_map(static fn (Member $member): string => $member->getUsername(), $this->safetyModel->getSafetyTeamMembers());
    }
}
