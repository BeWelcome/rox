<?php

namespace App\Tests\Controller;

use AdminRightsController;
use AdminRightsModel;
use App\Entity\Member;
use App\Utilities\SessionSingleton;
use DAMA\DoctrineTestBundle\PHPUnit\SkipDatabaseRollback;
use Doctrine\ORM\EntityManagerInterface;
use EnvironmentExplorer;
use InvalidArgumentException;
use PDB;
use PHPUnit\Framework\Attributes\Group;
use ReadOnlyObject;
use ReadWriteObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Group('integration')]
#[SkipDatabaseRollback]
/**
 * @infection-ignore-all
 */
final class AdminRightsControllerTest extends KernelTestCase
{
    public function testMemberRightsListIncludesMemberWithoutAddressAndReusesResult(): void
    {
        $this->initializeLegacyEnvironment();
        $model = new AdminRightsModel();
        $dao = $this->addRightForMemberWithoutAddress($model);
        try {
            $page = new AdminRightsController()->listMembers();

            $this->assertArrayHasKey('member-empty', $page->members);
            $this->assertMemberDetails($page->members['member-empty']);
            $this->assertSame($page->members, $page->membersWithRights);
        } finally {
            $dao->exec('ROLLBACK');
        }
    }

    public function testMemberRightsListRefreshesSelectedMember(): void
    {
        $this->initializeLegacyEnvironment();
        $model = new AdminRightsModel();
        $dao = $this->addRightForMemberWithoutAddress($model);
        $controller = new AdminRightsController();
        try {
            $unfilteredArgs = (object) ['post' => ['member' => 0]];
            $unfilteredRedirect = new ReadWriteObject();
            $controller->listMembersCallback(
                $unfilteredArgs,
                new ReadOnlyObject([]),
                $unfilteredRedirect,
                new ReadWriteObject()
            );
            $this->assertSame($unfilteredRedirect->members, $unfilteredRedirect->membersWithRights);

            $memberId = $unfilteredRedirect->members['member-empty']->id;
            $args = (object) ['post' => ['member' => $memberId, 'history' => 1]];
            $redirect = new ReadWriteObject();
            $controller->listMembersCallback($args, new ReadOnlyObject([]), $redirect, new ReadWriteObject());

            $this->assertSame($args->post, $redirect->vars);
            $this->assertArrayHasKey('member-empty', $redirect->membersWithRights);
            $this->assertCount(1, $redirect->membersWithRights);
        } finally {
            $dao->exec('ROLLBACK');
        }
    }

    public function testRightsMemberListIncludesMemberWithoutAddress(): void
    {
        $this->initializeLegacyEnvironment();
        $model = new AdminRightsModel();
        $dao = $this->addRightForMemberWithoutAddress($model);
        try {
            $memberDetails = null;
            foreach (new AdminRightsController()->listRights()->rightsWithMembers as $right) {
                foreach ($right->Members as $member) {
                    if ('member-empty' === $member->Username) {
                        $memberDetails = $member;
                        break 2;
                    }
                }
            }
            $this->assertNotNull($memberDetails);
            $this->assertMemberDetails($memberDetails);
        } finally {
            $dao->exec('ROLLBACK');
        }
    }

    public function testRightCanBeAssignedWithoutHistory(): void
    {
        $this->initializeLegacyEnvironment();
        $model = new AdminRightsModel();
        $dao = $model->dao;
        $dao->exec('START TRANSACTION');
        try {
            $right = $dao->query("SELECT id FROM rights WHERE Name = 'Words'")->fetch(PDB::FETCH_OBJ);
            $existing = $dao->query(<<<'SQL'
                SELECT COUNT(*) AS count
                FROM rightsvolunteers rv, member m
                WHERE rv.IdMember = m.id AND rv.IdRight = (SELECT id FROM rights WHERE Name = 'Words')
                    AND m.Username = 'member-empty'
                SQL)->fetch(PDB::FETCH_OBJ);
            $this->assertSame(0, (int) $existing->count);
            $vars = [
                'username' => 'member-empty',
                'rightid' => $right->id,
                'level' => 10,
                'scope' => '"All"',
                'comment' => 'First assignment',
            ];

            $this->assertSame([], $model->checkAssignVarsOk($vars));
            $model->assignRight($vars);

            $assignment = $dao->query(<<<'SQL'
                SELECT rv.Level, rv.Scope, rv.Comment, rv.updated, rv.created
                FROM rightsvolunteers rv, member m, rights r
                WHERE rv.IdMember = m.id AND rv.IdRight = r.id
                    AND m.Username = 'member-empty' AND r.Name = 'Words'
                SQL)->fetch(PDB::FETCH_OBJ);
            $this->assertSame(10, (int) $assignment->Level);
            $this->assertSame('"All"', $assignment->Scope);
            $this->assertSame('First assignment', $assignment->Comment);
            $this->assertSame($assignment->created, $assignment->updated);
        } finally {
            $dao->exec('ROLLBACK');
        }
    }

    public function testRemovedRightCanBeAssignedAgain(): void
    {
        $this->initializeLegacyEnvironment();
        $model = new AdminRightsModel();
        $dao = $model->dao;
        $dao->exec('START TRANSACTION');
        try {
            $dao->exec(<<<'SQL'
                INSERT INTO rightsvolunteers (Level, Scope, Comment, updated, created, IdMember, IdRight)
                SELECT 0, '"All"', 'Removed right history', NOW(), NOW(), m.id, r.id
                FROM member m, rights r
                WHERE m.Username = 'member-empty' AND r.Name = 'Words'
                SQL);
            $right = $dao->query("SELECT id FROM rights WHERE Name = 'Words'")->fetch(PDB::FETCH_OBJ);
            $vars = [
                'username' => 'member-empty',
                'rightid' => $right->id,
                'level' => 10,
                'scope' => '"All"',
                'comment' => 'Assigned again',
            ];

            $this->assertSame([], $model->checkAssignVarsOk($vars));
            $model->assignRight($vars);

            $assignment = $dao->query(<<<'SQL'
                SELECT COUNT(*) AS count, MAX(rv.Level) AS Level, MAX(rv.Scope) AS Scope, MAX(rv.Comment) AS Comment
                FROM rightsvolunteers rv, member m, rights r
                WHERE rv.IdMember = m.id AND rv.IdRight = r.id
                    AND m.Username = 'member-empty' AND r.Name = 'Words'
                SQL)->fetch(PDB::FETCH_OBJ);
            $this->assertSame(1, (int) $assignment->count);
            $this->assertSame(10, (int) $assignment->Level);
            $this->assertSame('"All"', $assignment->Scope);
            $this->assertSame('Assigned again', $assignment->Comment);
            $this->assertContains('AdminRightsAlreadyAssigned', $model->checkAssignVarsOk($vars));
        } finally {
            $dao->exec('ROLLBACK');
        }
    }

    private function initializeLegacyEnvironment(): void
    {
        static::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']);
        $this->assertInstanceOf(Member::class, $member);

        try {
            $session = SessionSingleton::getSession();
        } catch (InvalidArgumentException) {
            $session = new Session(new MockArraySessionStorage());
            SessionSingleton::createInstance($session);
        }
        $session->set('IdMember', $member->getId());

        $databaseName = $entityManager->getConnection()->getDatabase();
        $this->assertNotNull($databaseName);

        $workingDirectory = getcwd();
        try {
            $this->assertTrue(chdir($container->getParameter('kernel.project_dir') . '/public'));
            new EnvironmentExplorer($container->get(UrlGeneratorInterface::class))->initializeGlobalState(
                $container->getParameter('database_host'),
                $databaseName,
                $container->getParameter('database_user'),
                $container->getParameter('database_password'),
                $container->getParameter('manticore.host'),
                $container->getParameter('manticore.port'),
            );
        } finally {
            chdir($workingDirectory);
        }
    }

    private function assertMemberDetails(object $member): void
    {
        $this->assertSame('2026-01-02', $member->LastLogin);
    }

    private function addRightForMemberWithoutAddress(AdminRightsModel $model): PDB
    {
        $dao = $model->dao;
        $dao->exec('START TRANSACTION');
        $addressCount = $dao->query(<<<'SQL'
            SELECT COUNT(*) AS count
            FROM address a, member m
            WHERE a.member_id = m.id AND a.active = 1 AND m.Username = 'member-empty'
            SQL)->fetch(PDB::FETCH_OBJ);
        $this->assertSame(0, (int) $addressCount->count);
        $dao->exec(<<<'SQL'
            UPDATE member SET LastActive = '2026-01-02 03:04:05' WHERE Username = 'member-empty'
            SQL);
        $dao->exec(<<<'SQL'
            INSERT INTO rightsvolunteers (Level, Scope, Comment, updated, created, IdMember, IdRight)
            SELECT 10, '"All"', 'Issue 154 regression', NOW(), NOW(), m.id, r.id
            FROM member m, rights r
            WHERE m.Username = 'member-empty' AND r.Name = 'Words'
            SQL);

        return $dao;
    }
}
