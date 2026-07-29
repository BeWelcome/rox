<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Group('integration')]
final class AdminRightsControllerTest extends WebTestCase
{
    public function testAccessIsDeniedWithoutRightsManagementRight(): void
    {
        $client = static::createClient();
        $this->login($client, 'member-2');

        $client->request('GET', '/admin/rights');

        self::assertResponseStatusCodeSame(403);
    }

    public function testScopedManagerCannotViewOrMutateOtherRights(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $this->grantManagementRight($connection, 'member-empty', 'Rights', '"Words";"Flags";');
        $flagsId = $this->getRightId($connection, 'Flags');
        $groupId = $this->getRightId($connection, 'Group');
        $this->addRightAssignment($connection, 'member-1', $groupId, 10, '"All"', 'Out of scope');
        $this->login($client, 'member-empty', $entityManager);

        $client->request('GET', "/admin/rights/list/rights/{$flagsId}");
        self::assertResponseIsSuccessful();

        $client->request('GET', "/admin/rights/list/rights/{$groupId}");
        self::assertResponseStatusCodeSame(403);

        $client->request('GET', "/admin/rights/edit/{$groupId}/member-1");
        self::assertResponseStatusCodeSame(403);

        $client->request('GET', "/admin/rights/remove/{$groupId}/member-1");
        self::assertResponseStatusCodeSame(403);

        $client->request('GET', '/admin/rights/create');
        self::assertResponseStatusCodeSame(403);

        $crawler = $client->request('GET', '/admin/rights');
        $token = $crawler->filter('input[name="right_assignment[_token]"]')->attr('value');
        $client->request('POST', '/admin/rights', [
            'right_assignment' => [
                'username' => 'member-2',
                'right' => (string) $groupId,
                'level' => '10',
                'scope' => '"All"',
                'comment' => 'Out-of-scope form tampering',
                'submit' => '',
                '_token' => $token,
            ],
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSame(0, $this->countRightAssignments($connection, 'member-2', $groupId));
    }

    public function testListsIncludeAddressedAndAddresslessMembersAndUseGetFilters(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $wordsId = $this->getRightId($connection, 'Words');
        $this->addRightAssignment(
            $connection,
            'member-empty',
            $wordsId,
            10,
            '"All"',
            'Addressless right holder',
        );
        $this->grantManagementRight($connection, 'member-2', 'Rights', '"All"');
        $rightsId = $this->getRightId($connection, 'Rights');
        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO address (member_id, location, active)
                SELECT id, 2082600, 1
                FROM member
                WHERE Username = 'member-2'
                SQL,
        );
        $this->login($client, 'member-2', $entityManager);

        $crawler = $client->request('GET', '/admin/rights/list/members');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Berlin', $crawler->filter('table')->text());
        self::assertStringContainsString('Germany', $crawler->filter('table')->text());
        self::assertStringContainsString('member-empty', $crawler->filter('table')->text());

        $crawler = $client->request('GET', '/admin/rights/list/members', [
            'member' => 'member-2',
            'right' => $rightsId,
            'history' => 1,
        ]);
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('table tbody tr'));
        self::assertStringContainsString('Berlin', $crawler->filter('table tbody')->text());
        self::assertStringNotContainsString('Jayapura', $crawler->filter('table tbody')->text());

        $crawler = $client->request('GET', '/admin/rights/list/members', [
            'member' => 'member-empty',
            'right' => $wordsId,
            'history' => 1,
        ]);
        self::assertResponseIsSuccessful();
        self::assertSame('member-empty', $crawler->filter('#rights-member')->attr('value'));
        self::assertSame((string) $wordsId, $crawler->filter('#rights-right option[selected]')->attr('value'));
        self::assertCount(1, $crawler->filter('table tbody tr'));
        self::assertStringNotContainsString('Berlin', $crawler->filter('table tbody')->text());
    }

    public function testAssignmentsArePaginatedAtFiftyRows(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $suffix = (string) random_int(100_000, 999_999);

        for ($index = 1; $index <= 51; ++$index) {
            $rightId = $this->createRight($connection, "Paging {$suffix}-{$index}");
            $this->addRightAssignment($connection, 'member-empty', $rightId, 1, '"All"', 'Paging test');
        }
        $this->grantManagementRight($connection, 'member-2', 'Rights', '"All"');
        $this->login($client, 'member-2', $entityManager);

        $crawler = $client->request('GET', '/admin/rights/list/members', [
            'member' => 'member-empty',
            'history' => 1,
        ]);
        self::assertResponseIsSuccessful();
        self::assertCount(50, $crawler->filter('table tbody tr'));

        $crawler = $client->request('GET', '/admin/rights/list/members', [
            'member' => 'member-empty',
            'history' => 1,
            'page' => 2,
        ]);
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('table tbody tr'));
        self::assertStringContainsString('Paging test', $crawler->filter('table tbody')->text());
    }

    public function testFreshAssignmentSetsBothTimestampsAndRejectsDuplicateAndMissingCsrf(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $wordsId = $this->getRightId($connection, 'Words');
        $groupId = $this->getRightId($connection, 'Group');
        $this->deleteRightAssignment($connection, 'member-empty', $wordsId);
        $this->deleteRightAssignment($connection, 'member-empty', $groupId);
        $this->grantManagementRight($connection, 'member-2', 'Rights', '"All"');
        $this->login($client, 'member-2', $entityManager);

        $crawler = $client->request('GET', '/admin/rights');
        $form = $crawler->filter('form')->form([
            'right_assignment[username]' => 'member-empty',
            'right_assignment[right]' => (string) $wordsId,
            'right_assignment[level]' => '10',
            'right_assignment[scope]' => '"DE";"FR";',
            'right_assignment[comment]' => 'Fresh assignment',
        ]);
        $client->submit($form);

        self::assertResponseRedirects('/admin/rights/list/member/member-empty');
        $assignment = $connection->fetchAssociative(
            <<<'SQL'
                SELECT rv.created, rv.updated, rv.Level, rv.Scope, rv.Comment
                FROM rightsvolunteers rv
                INNER JOIN member m ON m.id = rv.IdMember
                WHERE m.Username = ? AND rv.IdRight = ?
                SQL,
            ['member-empty', $wordsId],
        );
        self::assertIsArray($assignment);
        self::assertSame($assignment['created'], $assignment['updated']);
        self::assertSame(10, (int) $assignment['Level']);
        self::assertSame('"DE";"FR";', $assignment['Scope']);
        self::assertSame('Fresh assignment', $assignment['Comment']);

        $crawler = $client->request('GET', '/admin/rights');
        $form = $crawler->filter('form')->form([
            'right_assignment[username]' => 'member-empty',
            'right_assignment[right]' => (string) $wordsId,
            'right_assignment[level]' => '10',
            'right_assignment[scope]' => '"All"',
            'right_assignment[comment]' => 'Duplicate assignment',
        ]);
        $client->submit($form);
        self::assertResponseStatusCodeSame(422);
        self::assertSame(1, $this->countRightAssignments($connection, 'member-empty', $wordsId));

        $crawler = $client->request('GET', '/admin/rights');
        $form = $crawler->filter('form')->form([
            'right_assignment[username]' => 'member-empty',
            'right_assignment[right]' => (string) $groupId,
            'right_assignment[level]' => '10',
            'right_assignment[scope]' => '"Words""Group"',
            'right_assignment[comment]' => 'Malformed scope',
        ]);
        $client->submit($form);
        self::assertResponseStatusCodeSame(422);
        self::assertSame(0, $this->countRightAssignments($connection, 'member-empty', $groupId));

        $client->request('POST', '/admin/rights', [
            'right_assignment' => [
                'username' => 'member-empty',
                'right' => (string) $groupId,
                'level' => '10',
                'scope' => '"All"',
                'comment' => 'Missing token',
                'submit' => '',
            ],
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSame(0, $this->countRightAssignments($connection, 'member-empty', $groupId));
    }

    public function testRemovalKeepsHistoryAndAssignmentReactivatesTheSameRow(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $wordsId = $this->getRightId($connection, 'Words');
        $this->deleteRightAssignment($connection, 'member-empty', $wordsId);
        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO rightsvolunteers
                    (Level, Scope, Comment, updated, created, IdMember, IdRight)
                SELECT 10, '"All"', 'Original assignment', '2020-01-03 04:05:06',
                       '2020-01-02 03:04:05', m.id, ?
                FROM member m
                WHERE m.Username = 'member-empty'
                SQL,
            [$wordsId],
        );
        $assignmentId = (int) $connection->lastInsertId();
        $this->grantManagementRight($connection, 'member-2', 'Rights', '"All"');
        $this->login($client, 'member-2', $entityManager);

        $crawler = $client->request('GET', "/admin/rights/remove/{$wordsId}/member-empty");
        $client->submit($crawler->filter('form')->form());
        self::assertResponseRedirects('/admin/rights/list/member/member-empty');

        $removed = $connection->fetchAssociative(
            'SELECT Level, Comment, created FROM rightsvolunteers WHERE id = ?',
            [$assignmentId],
        );
        self::assertIsArray($removed);
        self::assertSame(0, (int) $removed['Level']);
        self::assertStringContainsString('Removed by member-2 on ', $removed['Comment']);
        self::assertSame('2020-01-02 03:04:05', $removed['created']);

        $crawler = $client->request('GET', '/admin/rights/list/member/member-empty?history=0');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Original assignment', $crawler->filter('table')->text());

        $crawler = $client->request('GET', '/admin/rights/assign/member-empty');
        $form = $crawler->filter('form')->form([
            'right_assignment[right]' => (string) $wordsId,
            'right_assignment[level]' => '7',
            'right_assignment[scope]' => '"All"',
            'right_assignment[comment]' => 'Assigned again',
        ]);
        $client->submit($form);
        self::assertResponseRedirects('/admin/rights/list/member/member-empty');

        $reactivated = $connection->fetchAssociative(
            'SELECT id, Level, Comment, created FROM rightsvolunteers WHERE IdMember = '
            . '(SELECT id FROM member WHERE Username = ?) AND IdRight = ?',
            ['member-empty', $wordsId],
        );
        self::assertIsArray($reactivated);
        self::assertSame($assignmentId, (int) $reactivated['id']);
        self::assertSame(7, (int) $reactivated['Level']);
        self::assertSame('Assigned again', $reactivated['Comment']);
        self::assertSame('2020-01-02 03:04:05', $reactivated['created']);
    }

    public function testRightRouteNamesAndProfileAdminLinkRemainValid(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $this->grantManagementRight($connection, 'member-2', 'Rights', '"All"');
        $manager = $this->login($client, 'member-2', $entityManager);
        $router = static::getContainer()->get(UrlGeneratorInterface::class);

        $routes = [
            'admin_rights' => [[], '/admin/rights'],
            'admin_rights_assign' => [['username' => 'member-1'], '/admin/rights/assign/member-1'],
            'admin_rights_overview' => [[], '/admin/rights/overview'],
            'admin_rights_members' => [[], '/admin/rights/list/members'],
            'admin_rights_member' => [['username' => 'member-1'], '/admin/rights/list/member/member-1'],
            'admin_rights_rights' => [[], '/admin/rights/list/rights'],
            'admin_rights_right' => [['id' => 2], '/admin/rights/list/rights/2'],
            'admin_rights_create' => [[], '/admin/rights/create'],
            'admin_rights_edit' => [
                ['id' => 2, 'username' => 'member-1'],
                '/admin/rights/edit/2/member-1',
            ],
            'admin_rights_remove' => [
                ['id' => 2, 'username' => 'member-1'],
                '/admin/rights/remove/2/member-1',
            ],
        ];
        foreach ($routes as $route => [$parameters, $path]) {
            self::assertSame($path, $router->generate($route, $parameters));
        }

        $client->loginUser($manager);
        $crawler = $client->request('GET', '/members/member-1');
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('a[href="/admin/rights/list/member/member-1"]'));
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function login(
        KernelBrowser $client,
        string $username,
        ?EntityManagerInterface $entityManager = null,
    ): Member {
        $entityManager ??= $this->getEntityManager();
        $entityManager->clear();
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        self::assertInstanceOf(Member::class, $member);
        $client->loginUser($member);

        return $member;
    }

    private function grantManagementRight(
        Connection $connection,
        string $username,
        string $rightName,
        string $scope,
    ): void {
        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO rightsvolunteers
                    (Level, Scope, Comment, updated, created, IdMember, IdRight)
                SELECT 10, ?, 'Integration test manager', NOW(), NOW(), m.id, r.id
                FROM member m
                INNER JOIN rights r ON r.Name = ?
                WHERE m.Username = ?
                ON DUPLICATE KEY UPDATE
                    Level = VALUES(Level),
                    Scope = VALUES(Scope),
                    Comment = VALUES(Comment),
                    updated = NOW()
                SQL,
            [$scope, $rightName, $username],
        );
    }

    private function createRight(Connection $connection, string $name): int
    {
        $connection->insert('rights', [
            'Name' => $name,
            'Description' => 'Pagination integration test',
            'created' => '2026-01-01 00:00:00',
        ]);

        return (int) $connection->lastInsertId();
    }

    private function getRightId(Connection $connection, string $name): int
    {
        return (int) $connection->fetchOne('SELECT id FROM rights WHERE Name = ?', [$name]);
    }

    private function addRightAssignment(
        Connection $connection,
        string $username,
        int $rightId,
        int $level,
        string $scope,
        string $comment,
    ): void {
        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO rightsvolunteers
                    (Level, Scope, Comment, updated, created, IdMember, IdRight)
                SELECT ?, ?, ?, NOW(), NOW(), m.id, ?
                FROM member m
                WHERE m.Username = ?
                ON DUPLICATE KEY UPDATE
                    Level = VALUES(Level),
                    Scope = VALUES(Scope),
                    Comment = VALUES(Comment),
                    updated = NOW()
                SQL,
            [$level, $scope, $comment, $rightId, $username],
        );
    }

    private function deleteRightAssignment(Connection $connection, string $username, int $rightId): void
    {
        $connection->executeStatement(
            <<<'SQL'
                DELETE rv
                FROM rightsvolunteers rv
                INNER JOIN member m ON m.id = rv.IdMember
                WHERE m.Username = ? AND rv.IdRight = ?
                SQL,
            [$username, $rightId],
        );
    }

    private function countRightAssignments(Connection $connection, string $username, int $rightId): int
    {
        return (int) $connection->fetchOne(
            <<<'SQL'
                SELECT COUNT(*)
                FROM rightsvolunteers rv
                INNER JOIN member m ON m.id = rv.IdMember
                WHERE m.Username = ? AND rv.IdRight = ?
                SQL,
            [$username, $rightId],
        );
    }
}
