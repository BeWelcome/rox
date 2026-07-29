<?php

namespace App\Tests\Controller;

use App\Entity\Flag;
use App\Entity\Member;
use App\Model\Admin\FlagsModel;
use App\Repository\FlagMemberRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Group('integration')]
final class AdminFlagsControllerTest extends WebTestCase
{
    public function testAccessIsDeniedWithoutFlagsManagementRight(): void
    {
        $client = static::createClient();
        $this->login($client, 'bwadmin');

        $client->request('GET', '/admin/flags');

        self::assertResponseStatusCodeSame(403);
    }

    public function testCreatingFlagsRequiresAllOrCreateScope(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $this->grantFlagsManagement($connection, 'bwadmin', '"ForumModerator"');
        $this->login($client, 'bwadmin', $entityManager);

        $client->request('GET', '/admin/flags/create');

        self::assertResponseStatusCodeSame(403);
    }

    public function testNewestAssignmentIsCurrentAndHistoryIdentifiesSupersededRows(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $this->grantFlagsManagement($connection, 'bwadmin', '"All"');
        $historyFlagId = $this->createFlag($connection, 'Flag history');
        $addressedFlagId = $this->createFlag($connection, 'Addressed flag');
        $this->addFlagAssignment(
            $connection,
            'member-empty',
            $historyFlagId,
            8,
            'Old active cycle',
            '2021-01-01 00:00:00',
        );
        $this->addFlagAssignment(
            $connection,
            'member-empty',
            $historyFlagId,
            0,
            'Newest removed cycle',
            '2021-01-01 00:00:00',
        );
        $this->addFlagAssignment(
            $connection,
            'bwadmin',
            $addressedFlagId,
            5,
            'Addressed current cycle',
            '2022-01-01 00:00:00',
        );
        $this->login($client, 'bwadmin', $entityManager);

        $crawler = $client->request('GET', '/admin/flags/list/members', [
            'member' => 'member-empty',
            'flag' => $historyFlagId,
        ]);
        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('Old active cycle', $crawler->filter('table')->text());

        $crawler = $client->request('GET', '/admin/flags/list/members', [
            'member' => 'member-empty',
            'flag' => $historyFlagId,
            'history' => 1,
        ]);
        self::assertResponseIsSuccessful();
        self::assertCount(2, $crawler->filter('table tbody tr'));
        self::assertStringContainsString('Old active cycle', $crawler->filter('table')->text());
        self::assertStringContainsString('Newest removed cycle', $crawler->filter('table')->text());
        self::assertCount(1, $crawler->filter('table .badge'));

        $crawler = $client->request('GET', '/admin/flags/list/members', ['history' => 1]);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Berlin', $crawler->filter('table')->text());
        self::assertStringContainsString('Germany', $crawler->filter('table')->text());
        self::assertStringContainsString('member-empty', $crawler->filter('table')->text());
    }

    public function testFlagAssignmentsArePaginatedAtFiftyRows(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $this->grantFlagsManagement($connection, 'bwadmin', '"All"');
        $suffix = (string) random_int(100_000, 999_999);

        for ($index = 1; $index <= 51; ++$index) {
            $flagId = $this->createFlag($connection, "Paging flag {$suffix}-{$index}");
            $this->addFlagAssignment(
                $connection,
                'member-empty',
                $flagId,
                1,
                'Paging test',
                \sprintf('2026-01-01 00:%02d:00', $index % 60),
            );
        }
        $this->login($client, 'bwadmin', $entityManager);

        $crawler = $client->request('GET', '/admin/flags/list/members', [
            'member' => 'member-empty',
        ]);
        self::assertResponseIsSuccessful();
        self::assertCount(50, $crawler->filter('table tbody tr'));

        $crawler = $client->request('GET', '/admin/flags/list/members', [
            'member' => 'member-empty',
            'page' => 2,
        ]);
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('table tbody tr'));
        self::assertStringContainsString('Paging test', $crawler->filter('table tbody')->text());
    }

    public function testRemoveAndReassignCreatesANewCycleConsumedByLegacyNewestRecordRule(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $this->grantFlagsManagement($connection, 'bwadmin', '"All"');
        $flagId = $this->createFlag($connection, 'NotAllowedToPostInForum');
        $this->login($client, 'bwadmin', $entityManager);

        $crawler = $client->request('GET', '/admin/flags/assign/member-empty');
        $form = $crawler->filter('form')->form([
            'flag_assignment[flag]' => (string) $flagId,
            'flag_assignment[level]' => '10',
            'flag_assignment[scope]' => '"All"',
            'flag_assignment[comment]' => 'Initial forum restriction',
        ]);
        $client->submit($form);
        self::assertResponseRedirects('/admin/flags/list/members/member-empty');
        self::assertSame(10, $this->legacyCurrentLevel($connection, 'member-empty', $flagId));

        $crawler = $client->request('GET', "/admin/flags/remove/{$flagId}/member-empty");
        $client->submit($crawler->filter('form')->form());
        self::assertResponseRedirects('/admin/flags/list/members/member-empty?history=1');
        self::assertSame(0, $this->legacyCurrentLevel($connection, 'member-empty', $flagId));

        $crawler = $client->request('GET', '/admin/flags/assign/member-empty');
        $form = $crawler->filter('form')->form([
            'flag_assignment[flag]' => (string) $flagId,
            'flag_assignment[level]' => '6',
            'flag_assignment[scope]' => '"All"',
            'flag_assignment[comment]' => 'Forum restriction assigned again',
        ]);
        $client->submit($form);
        self::assertResponseRedirects('/admin/flags/list/members/member-empty');

        $cycles = $connection->fetchAllAssociative(
            <<<'SQL'
                SELECT fm.id, fm.Level, fm.Comment, fm.created
                FROM flagsmembers fm
                INNER JOIN member m ON m.id = fm.IdMember
                WHERE m.Username = ? AND fm.IdFlag = ?
                ORDER BY fm.created DESC, fm.id DESC
                SQL,
            ['member-empty', $flagId],
        );
        self::assertCount(2, $cycles);
        self::assertSame(6, (int) $cycles[0]['Level']);
        self::assertSame(0, (int) $cycles[1]['Level']);
        self::assertGreaterThan($cycles[1]['created'], $cycles[0]['created']);
        self::assertSame(6, $this->legacyCurrentLevel($connection, 'member-empty', $flagId));

        $crawler = $client->request('GET', "/admin/flags/edit/{$flagId}/member-empty");
        $form = $crawler->filter('form')->form([
            'flag_assignment[level]' => '7',
            'flag_assignment[scope]' => '"All"',
            'flag_assignment[comment]' => 'Edited current cycle',
        ]);
        $client->submit($form);
        self::assertResponseRedirects('/admin/flags/list/members/member-empty');

        $comments = $connection->fetchFirstColumn(
            <<<'SQL'
                SELECT fm.Comment
                FROM flagsmembers fm
                INNER JOIN member m ON m.id = fm.IdMember
                WHERE m.Username = ? AND fm.IdFlag = ?
                ORDER BY fm.created DESC, fm.id DESC
                SQL,
            ['member-empty', $flagId],
        );
        self::assertSame('Edited current cycle', $comments[0]);
        self::assertStringContainsString('Removed by bwadmin on ', $comments[1]);
    }

    public function testSupersededFlagCycleCannotBeChangedThroughAStaleEntity(): void
    {
        static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $flagId = $this->createFlag($connection, 'Immutable flag history');
        $this->addFlagAssignment(
            $connection,
            'member-empty',
            $flagId,
            5,
            'Older cycle',
            '2020-01-01 00:00:00',
        );
        $entityManager->clear();

        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-empty']);
        $manager = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']);
        $flag = $entityManager->getRepository(Flag::class)->find($flagId);
        self::assertInstanceOf(Member::class, $member);
        self::assertInstanceOf(Member::class, $manager);
        self::assertInstanceOf(Flag::class, $flag);

        $repository = static::getContainer()->get(FlagMemberRepository::class);
        $staleAssignment = $repository->findCurrent($member, $flag);
        self::assertNotNull($staleAssignment);

        $this->addFlagAssignment(
            $connection,
            'member-empty',
            $flagId,
            6,
            'Current cycle',
            '2021-01-01 00:00:00',
        );

        $model = static::getContainer()->get(FlagsModel::class);
        self::assertFalse($model->edit($staleAssignment, 9, '"All"', 'Changed stale cycle'));
        self::assertFalse($model->remove($staleAssignment, $manager));

        $comments = $connection->fetchFirstColumn(
            'SELECT Comment FROM flagsmembers WHERE IdFlag = ? ORDER BY created, id',
            [$flagId],
        );
        self::assertSame(['Older cycle', 'Current cycle'], $comments);

        $currentAssignment = $repository->findCurrent($member, $flag);
        self::assertNotNull($currentAssignment);
        $connection->update('flagsmembers', [
            'Level' => 0,
            'Comment' => 'Removed by another request',
        ], [
            'id' => $currentAssignment->getId(),
        ]);

        self::assertFalse($model->edit($currentAssignment, 9, '"All"', 'Resurrected by stale edit'));
        self::assertFalse($model->remove($currentAssignment, $manager));
        $removed = $connection->fetchAssociative(
            'SELECT Level, Comment FROM flagsmembers WHERE id = ?',
            [$currentAssignment->getId()],
        );
        self::assertIsArray($removed);
        self::assertSame(0, (int) $removed['Level']);
        self::assertSame('Removed by another request', $removed['Comment']);
    }

    public function testDuplicateInvalidMemberAndMissingCsrfDoNotCreateAssignments(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $this->grantFlagsManagement($connection, 'bwadmin', '"All"');
        $flagId = $this->createFlag($connection, 'Duplicate flag');
        $otherFlagId = $this->createFlag($connection, 'CSRF flag');
        $this->addFlagAssignment(
            $connection,
            'member-empty',
            $flagId,
            5,
            'Existing current assignment',
            '2026-01-01 00:00:00',
        );
        $this->login($client, 'bwadmin', $entityManager);

        $crawler = $client->request('GET', '/admin/flags/assign');
        $form = $crawler->filter('form')->form([
            'flag_assignment[username]' => 'member-empty',
            'flag_assignment[flag]' => (string) $flagId,
            'flag_assignment[level]' => '5',
            'flag_assignment[scope]' => '',
            'flag_assignment[comment]' => 'Duplicate',
        ]);
        $client->submit($form);
        self::assertResponseStatusCodeSame(422);
        self::assertSame(1, $this->countFlagAssignments($connection, 'member-empty', $flagId));

        $crawler = $client->request('GET', '/admin/flags/assign');
        $form = $crawler->filter('form')->form([
            'flag_assignment[username]' => 'does-not-exist',
            'flag_assignment[flag]' => (string) $otherFlagId,
            'flag_assignment[level]' => '5',
            'flag_assignment[scope]' => '',
            'flag_assignment[comment]' => 'Invalid member',
        ]);
        $client->submit($form);
        self::assertResponseStatusCodeSame(422);
        self::assertSame(0, $this->countFlagAssignments($connection, 'member-empty', $otherFlagId));

        $client->request('POST', '/admin/flags/assign', [
            'flag_assignment' => [
                'username' => 'member-empty',
                'flag' => (string) $otherFlagId,
                'level' => '5',
                'scope' => '',
                'comment' => 'Missing token',
                'submit' => '',
            ],
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSame(0, $this->countFlagAssignments($connection, 'member-empty', $otherFlagId));
    }

    public function testCreatedFlagsHaveRelevanceOneHundredAndIrrelevantFlagsStayHidden(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $this->grantFlagsManagement($connection, 'bwadmin', '"Create"');
        $hiddenFlagId = $this->createFlag($connection, 'Hidden legacy flag', 0);
        self::assertGreaterThan(0, $hiddenFlagId);
        $this->login($client, 'bwadmin', $entityManager);

        $crawler = $client->request('GET', '/admin/flags/create');
        $form = $crawler->filter('form')->form([
            'flag_definition[name]' => 'Created modern flag',
            'flag_definition[description]' => 'Created through Symfony',
        ]);
        $client->submit($form);
        self::assertResponseRedirects('/admin/flags/overview');
        self::assertSame(100, (int) $connection->fetchOne(
            'SELECT Relevance FROM flags WHERE Name = ?',
            ['Created modern flag'],
        ));

        $crawler = $client->request('GET', '/admin/flags/overview');
        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('Hidden legacy flag', $crawler->filter('table')->text());
        self::assertStringContainsString('Created modern flag', $crawler->filter('table')->text());

        $crawler = $client->request('GET', '/admin/flags/create');
        $form = $crawler->filter('form')->form([
            'flag_definition[name]' => 'Created modern flag',
            'flag_definition[description]' => 'Duplicate definition',
        ]);
        $client->submit($form);
        self::assertResponseStatusCodeSame(422);
        self::assertSame(1, (int) $connection->fetchOne(
            'SELECT COUNT(*) FROM flags WHERE Name = ?',
            ['Created modern flag'],
        ));
    }

    public function testFlagRouteNamesAndProfileAdminLinkRemainValid(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $this->grantFlagsManagement($connection, 'bwadmin', '"All"');
        $manager = $this->login($client, 'bwadmin', $entityManager);
        $router = static::getContainer()->get(UrlGeneratorInterface::class);

        $routes = [
            'admin_flags' => [[], '/admin/flags'],
            'admin_flags_overview' => [[], '/admin/flags/overview'],
            'admin_flags_members' => [[], '/admin/flags/list/members'],
            'admin_flags_member' => [['username' => 'member-1'], '/admin/flags/list/members/member-1'],
            'admin_flags_flags' => [[], '/admin/flags/list/flags'],
            'admin_flags_flag' => [['id' => 3], '/admin/flags/list/flags/3'],
            'admin_flags_assign' => [[], '/admin/flags/assign'],
            'admin_flags_assign_user' => [['username' => 'member-1'], '/admin/flags/assign/member-1'],
            'admin_flags_create' => [[], '/admin/flags/create'],
            'admin_flags_edit' => [
                ['id' => 3, 'username' => 'member-1'],
                '/admin/flags/edit/3/member-1',
            ],
            'admin_flags_remove' => [
                ['id' => 3, 'username' => 'member-1'],
                '/admin/flags/remove/3/member-1',
            ],
        ];
        foreach ($routes as $route => [$parameters, $path]) {
            self::assertSame($path, $router->generate($route, $parameters));
        }

        $client->loginUser($manager);
        $crawler = $client->request('GET', '/members/member-1');
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('a[href="/admin/flags/list/members/member-1"]'));
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

    private function grantFlagsManagement(Connection $connection, string $username, string $scope): void
    {
        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO rightsvolunteers
                    (Level, Scope, Comment, updated, created, IdMember, IdRight)
                SELECT 10, ?, 'Integration test flag manager', NOW(), NOW(), m.id, r.id
                FROM member m
                INNER JOIN rights r ON r.Name = 'Flags'
                WHERE m.Username = ?
                ON DUPLICATE KEY UPDATE
                    Level = VALUES(Level),
                    Scope = VALUES(Scope),
                    Comment = VALUES(Comment),
                    updated = NOW()
                SQL,
            [$scope, $username],
        );
    }

    private function createFlag(Connection $connection, string $name, int $relevance = 100): int
    {
        $connection->insert('flags', [
            'Name' => $name,
            'Description' => 'Integration test flag',
            'Relevance' => $relevance,
            'created' => '2026-01-01 00:00:00',
            'updated' => null,
        ]);

        return (int) $connection->lastInsertId();
    }

    private function addFlagAssignment(
        Connection $connection,
        string $username,
        int $flagId,
        int $level,
        string $comment,
        string $created,
    ): void {
        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO flagsmembers
                    (Level, Scope, Comment, updated, created, IdMember, IdFlag)
                SELECT ?, '"All"', ?, NULL, ?, m.id, ?
                FROM member m
                WHERE m.Username = ?
                SQL,
            [$level, $comment, $created, $flagId, $username],
        );
    }

    private function countFlagAssignments(Connection $connection, string $username, int $flagId): int
    {
        return (int) $connection->fetchOne(
            <<<'SQL'
                SELECT COUNT(*)
                FROM flagsmembers fm
                INNER JOIN member m ON m.id = fm.IdMember
                WHERE m.Username = ? AND fm.IdFlag = ?
                SQL,
            [$username, $flagId],
        );
    }

    private function legacyCurrentLevel(Connection $connection, string $username, int $flagId): int
    {
        return (int) $connection->fetchOne(
            <<<'SQL'
                SELECT fm.Level
                FROM flagsmembers fm
                INNER JOIN member m ON m.id = fm.IdMember
                WHERE m.Username = ? AND fm.IdFlag = ?
                ORDER BY fm.created DESC
                LIMIT 1
                SQL,
            [$username, $flagId],
        );
    }
}
