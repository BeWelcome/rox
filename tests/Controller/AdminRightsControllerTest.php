<?php

namespace App\Tests\Controller;

use AdminRightsController;
use AdminRightsModel;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use PDB;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use ReadOnlyObject;
use ReadWriteObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('integration')]
#[IgnoreDeprecations('^Creation of dynamic property RoxFrontRouter::\$(?:classes|session_memory) is deprecated$')]
/**
 * @infection-ignore-all
 */
final class AdminRightsControllerTest extends WebTestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testMemberRightsListRenders(): void
    {
        $client = $this->createAdminClient();
        $_SERVER['REQUEST_URI'] = '/admin/rights/list/members';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $client->request('GET', '/admin/rights/list/members');

        $this->assertResponseIsSuccessful();

        $model = new AdminRightsModel();
        $dao = $this->addRightForMemberWithoutAddress($model);
        try {
            $members = $model->getMembersWithRights();
            $this->assertArrayHasKey('member-empty', $members);
            $this->assertMemberDetails($members['member-empty']);
        } finally {
            $dao->exec('ROLLBACK');
        }
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testMemberRightsListRefreshesSelectedMember(): void
    {
        $client = $this->createAdminClient();
        $_SERVER['REQUEST_URI'] = '/admin/rights/list/members';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $crawler = $client->request('GET', '/admin/rights/list/members');
        $option = $crawler->filterXPath('//select[@id="member"]/option[@value != "0"]')->first();
        $memberId = $option->attr('value');
        $username = trim($option->text());
        $this->assertNotNull($memberId);

        $args = (object) ['post' => ['member' => $memberId, 'history' => 1]];
        $redirect = new ReadWriteObject();
        $controller = new AdminRightsController();
        $controller->listMembersCallback($args, new ReadOnlyObject([]), $redirect, new ReadWriteObject());

        $this->assertSame($args->post, $redirect->vars);
        $this->assertArrayHasKey($username, $redirect->membersWithRights);
        $this->assertCount(1, $redirect->membersWithRights);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testRightsMemberListRenders(): void
    {
        $client = $this->createAdminClient();
        $_SERVER['REQUEST_URI'] = '/admin/rights/list/rights';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $client->request('GET', '/admin/rights/list/rights');

        $this->assertResponseIsSuccessful();

        $model = new AdminRightsModel();
        $dao = $this->addRightForMemberWithoutAddress($model);
        try {
            $memberDetails = null;
            foreach ($model->getRightsWithMembers() as $right) {
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

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testRightCanBeAssignedWithoutHistory(): void
    {
        $client = $this->createAdminClient();
        $_SERVER['REQUEST_URI'] = '/admin/rights/assign/member-empty';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $client->request('GET', '/admin/rights/assign/member-empty');
        $this->assertResponseIsSuccessful();

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

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testRemovedRightCanBeAssignedAgain(): void
    {
        $client = $this->createAdminClient();
        $_SERVER['REQUEST_URI'] = '/admin/rights/assign/member-empty';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $client->request('GET', '/admin/rights/assign/member-empty');
        $this->assertResponseIsSuccessful();

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

    private function createAdminClient(): KernelBrowser
    {
        chdir(__DIR__ . '/../../public');
        unset($_SERVER['argc'], $_SERVER['argv']);

        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']);
        $this->assertInstanceOf(Member::class, $member);
        $client->loginUser($member);

        return $client;
    }

    private function assertMemberDetails(object $member): void
    {
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $member->LastLogin);
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
            INSERT INTO rightsvolunteers (Level, Scope, Comment, updated, created, IdMember, IdRight)
            SELECT 10, '"All"', 'Issue 154 regression', NOW(), NOW(), m.id, r.id
            FROM member m, rights r
            WHERE m.Username = 'member-empty' AND r.Name = 'Words'
            SQL);

        return $dao;
    }
}
