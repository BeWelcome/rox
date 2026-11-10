<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
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
        $client->request('GET', '/admin/rights/list/members', ['right' => $groupId]);
        self::assertResponseStatusCodeSame(403);

        $client->request('GET', "/admin/rights/edit/{$groupId}/member-1");
        self::assertResponseStatusCodeSame(403);

        $crawler = $client->request('GET', '/admin/rights/list/members');
        $client->request('POST', "/admin/rights/remove/{$groupId}/member-1", ['_token' => $this->removeToken($crawler)]);
        self::assertResponseStatusCodeSame(403);
        self::assertSame(10, $this->level($connection, 'member-1', $groupId));

        // the overview lists only the rights of the scope
        $crawler = $client->request('GET', '/admin/rights/overview');
        self::assertSame(['Flags', 'Words'], $crawler->filter('.c-radmin-overview__name')->each(
            static fn (Crawler $name): string => $name->text(),
        ));

        $crawler = $client->request('GET', '/admin/rights');
        // the member is searched with suggestions (member/suggest)
        self::assertCount(1, $crawler->filter('input#right_assignment_username[data-member-suggest="fill"][aria-controls="right_assignment_username_list"]'));
        self::assertCount(1, $crawler->filter('ul#right_assignment_username_list.c-member-suggest'));
        $token = $crawler->filter('input[name="right_assignment[_token]"]')->attr('value');
        $client->request('POST', '/admin/rights', [
            'right_assignment' => [
                'username' => 'member-2',
                'right' => (string) $groupId,
                'level' => '10',
                'scope' => '"All"',
                'comment' => 'Out-of-scope form tampering',
                '_token' => $token,
            ],
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSame(0, $this->countRightAssignments($connection, 'member-2', $groupId));
    }

    public function testByMemberListsTheMembersAndShowsTheChosenOnesRightsAndHistory(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $wordsId = $this->getRightId($connection, 'Words');
        $groupId = $this->getRightId($connection, 'Group');
        $this->addRightAssignment($connection, 'member-empty', $wordsId, 10, '"DE";"FR";', 'Translates German and French');
        $this->addRightAssignment($connection, 'member-empty', $groupId, 0, '"All"', 'Approved groups until 2025');
        $this->grantManagementRight($connection, 'member-2', 'Rights', '"All"');
        $connection->executeStatement(
            "INSERT INTO address (member_id, location, active) SELECT id, 2082600, 1 FROM member WHERE Username = 'member-2'",
        );
        $this->login($client, 'member-2', $entityManager);

        $crawler = $client->request('GET', '/admin/rights/list/members', ['member' => 'member-2']);
        self::assertResponseIsSuccessful();
        // the member filter suggests members; picking one filters by them
        self::assertCount(1, $crawler->filter('input#c-radmin-member-filter[name="member"][value="member-2"][data-member-suggest="submit"]'));
        self::assertCount(1, $crawler->filter('ul#c-radmin-member-filter_list.c-member-suggest'));
        // the tab keeps the chosen member
        self::assertSame('/admin/rights/list/member/member-2', $crawler->filter('.p-hero__tab--active')->attr('href'));
        $row = $crawler->filter('.c-cadmin-row.is-selected');
        self::assertStringContainsString('member-2', $row->text());
        self::assertStringContainsString('Berlin, Germany', $row->text());

        // from the profile: searched and chosen
        $crawler = $client->request('GET', '/admin/rights/list/member/member-empty');
        self::assertResponseIsSuccessful();
        self::assertSame('member-empty', $crawler->filter('.c-radmin-filters input[name="member"]')->attr('value'));
        $row = $crawler->filter('.c-cadmin-row.is-selected');
        // a tag per current right, and the removed ones as a count
        self::assertSame(['Words'], $row->filter('.c-tag')->each(static fn (Crawler $tag): string => $tag->text()));
        self::assertCount(1, $row->filter('.c-radmin-earlier'));
        self::assertSame('member-empty', $crawler->filter('.c-cadmin-person__name')->text());
        $block = $crawler->filter('.c-radmin-block');
        self::assertCount(1, $block);
        self::assertSame('Words', $block->filter('.c-radmin-block__name')->text());
        self::assertSame(['DE', 'FR'], $block->filter('.c-radmin-scope__chip')->each(static fn (Crawler $chip): string => $chip->text()));
        self::assertStringContainsString('Translates German and French', $block->text());
        self::assertSame("/admin/rights/list/rights/{$wordsId}", $block->filter('.c-radmin-block__name')->attr('href'));
        self::assertCount(1, $block->filter("[data-remove][data-action=\"/admin/rights/remove/{$wordsId}/member-empty\"]"));
        $history = $crawler->filter('.c-radmin-history__row');
        self::assertCount(1, $history);
        self::assertStringContainsString('Approved groups until 2025', $history->text());
        self::assertCount(0, $history->filter('[data-remove]'));
        self::assertSame('/admin/rights/assign/member-empty', $crawler->filter('.c-radmin-assign')->attr('href'));
    }

    public function testAMemberWithoutRightsShowsTheEmptyStateInsteadOfTheForm(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $this->grantManagementRight($entityManager->getConnection(), 'member-2', 'Rights', '"All"');
        $this->login($client, 'member-2', $entityManager);

        $crawler = $client->request('GET', '/admin/rights/list/member/member-1');

        self::assertResponseIsSuccessful();
        self::assertSame('member-1', $crawler->filter('.c-cadmin-person__name')->text());
        self::assertSelectorExists('.c-cadmin__detail .c-radmin-empty');
        self::assertCount(0, $crawler->filter('.c-radmin-block'));
    }

    public function testByRightGroupsTheAssignmentsAndShowsTheHistoryUnlessUnticked(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $suffix = (string) random_int(100_000, 999_999);
        $rightId = $this->createRight($connection, "Grouping {$suffix}");
        $this->addRightAssignment($connection, 'member-1', $rightId, 5, '"All"', 'Current holder');
        $this->addRightAssignment($connection, 'member-empty', $rightId, 0, '"PL"', 'Stepped back');
        $this->grantManagementRight($connection, 'member-2', 'Rights', '"All"');
        $this->login($client, 'member-2', $entityManager);

        $crawler = $client->request('GET', "/admin/rights/list/rights/{$rightId}");
        self::assertResponseIsSuccessful();
        self::assertSame('/admin/rights/list/rights', $crawler->filter('.p-hero__tab--active')->attr('href'));
        // checked by default
        self::assertNotNull($crawler->filter('.c-radmin-bar input[type="checkbox"][name="history"]')->attr('checked'));
        $group = $crawler->filter('.c-radmin-group');
        self::assertCount(1, $group);
        self::assertStringContainsString("Grouping {$suffix}", $group->text());
        // one current holder
        self::assertSame('1', trim($group->filter('.c-tag')->text()));
        $rows = $crawler->filter('.c-radmin-table__row:not(.c-radmin-table__row--head)');
        self::assertCount(2, $rows);
        $removed = $rows->reduce(static fn (Crawler $row): bool => str_contains($row->text(), 'member-empty'));
        self::assertCount(1, $removed->filter('.c-radmin-member__name.is-removed'));
        self::assertCount(1, $removed->filter('.c-radmin-removed'));
        self::assertCount(0, $removed->filter('[data-remove]'));
        self::assertSame('/admin/rights/list/member/member-empty', $removed->filter('.c-radmin-member')->attr('href'));
        $current = $rows->reduce(static fn (Crawler $row): bool => str_contains($row->text(), 'member-1'));
        self::assertCount(1, $current->filter('[data-remove]'));

        $crawler = $client->request('GET', '/admin/rights/list/rights', ['right' => $rightId, 'history' => '0']);
        self::assertResponseIsSuccessful();
        self::assertNull($crawler->filter('.c-radmin-bar input[type="checkbox"][name="history"]')->attr('checked'));
        self::assertCount(1, $crawler->filter('.c-radmin-table__row:not(.c-radmin-table__row--head)'));

        $crawler = $client->request('GET', '/admin/rights/list/rights', ['right' => $rightId, 'member' => 'empty']);
        self::assertCount(1, $crawler->filter('.c-radmin-table__row:not(.c-radmin-table__row--head)'));
    }

    public function testTheListsArePaginatedByItemsPerPagePreference(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $suffix = (string) random_int(100_000, 999_999);

        for ($index = 1; $index <= 6; ++$index) {
            $rightId = $this->createRight($connection, "Paging {$suffix}-{$index}");
            $this->addRightAssignment($connection, 'member-empty', $rightId, 1, '"All"', 'Paging test');
        }
        $this->grantManagementRight($connection, 'member-2', 'Rights', '"All"');
        $this->setItemsPerPage($connection, 'member-2', 5);
        $this->login($client, 'member-2', $entityManager);

        // By right pages the assignments
        $crawler = $client->request('GET', '/admin/rights/list/rights', ['member' => 'member-empty']);
        self::assertCount(1, $crawler->filter('input#c-radmin-member-filter[name="member"][value="member-empty"][data-member-suggest="submit"]'));
        self::assertCount(1, $crawler->filter('ul#c-radmin-member-filter_list.c-member-suggest'));
        // the filter form sends right= for all rights (and both history fields when it is checked)
        $client->request('GET', '/admin/rights/list/rights', ['member' => 'member-empty', 'right' => '', 'history' => '0']);
        self::assertResponseIsSuccessful();
        $client->request('GET', '/admin/rights/list/members', ['member' => 'member-empty', 'right' => '']);
        self::assertResponseIsSuccessful();
        self::assertResponseIsSuccessful();
        self::assertCount(5, $crawler->filter('.c-radmin-table__row:not(.c-radmin-table__row--head)'));
        $crawler = $client->request('GET', '/admin/rights/list/rights', ['member' => 'member-empty', 'page' => 2]);
        self::assertCount(1, $crawler->filter('.c-radmin-table__row:not(.c-radmin-table__row--head)'));

        // By member pages the members: all six rights are on the one member
        $crawler = $client->request('GET', '/admin/rights/list/members', ['member' => 'member-empty']);
        self::assertCount(1, $crawler->filter('.c-cadmin-row'));
        self::assertGreaterThanOrEqual(6, $crawler->filter('.c-radmin-block')->count());
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
        self::assertSame('/admin/rights', $crawler->filter('.p-hero__tab--active')->attr('href'));
        $form = $crawler->filter('form[name="right_assignment"]')->form([
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
        // the notice in the page, not a toast
        $client->followRedirect();
        self::assertSelectorExists('.c-inline-flash');
        self::assertSelectorNotExists('#toasts .bw-toast');

        $crawler = $client->request('GET', '/admin/rights');
        $client->submit($crawler->filter('form[name="right_assignment"]')->form([
            'right_assignment[username]' => 'member-empty',
            'right_assignment[right]' => (string) $wordsId,
            'right_assignment[level]' => '10',
            'right_assignment[scope]' => '"All"',
            'right_assignment[comment]' => 'Duplicate assignment',
        ]));
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorExists('.c-massmail-form__errors');
        self::assertSame(1, $this->countRightAssignments($connection, 'member-empty', $wordsId));

        $crawler = $client->request('GET', '/admin/rights');
        $client->submit($crawler->filter('form[name="right_assignment"]')->form([
            'right_assignment[username]' => 'nobody-of-that-name',
            'right_assignment[right]' => (string) $groupId,
            'right_assignment[level]' => '10',
            'right_assignment[scope]' => '',
            'right_assignment[comment]' => 'Unknown member',
        ]));
        self::assertResponseStatusCodeSame(422);

        $crawler = $client->request('GET', '/admin/rights');
        $client->submit($crawler->filter('form[name="right_assignment"]')->form([
            'right_assignment[username]' => 'member-empty',
            'right_assignment[right]' => (string) $groupId,
            'right_assignment[level]' => '10',
            'right_assignment[scope]' => '"Words""Group"',
            'right_assignment[comment]' => 'Malformed scope',
        ]));
        self::assertResponseStatusCodeSame(422);
        self::assertSame(0, $this->countRightAssignments($connection, 'member-empty', $groupId));

        $client->request('POST', '/admin/rights', [
            'right_assignment' => [
                'username' => 'member-empty',
                'right' => (string) $groupId,
                'level' => '10',
                'scope' => '"All"',
                'comment' => 'Missing token',
            ],
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSame(0, $this->countRightAssignments($connection, 'member-empty', $groupId));

        // an empty scope is All
        $crawler = $client->request('GET', '/admin/rights/assign/member-empty');
        self::assertSame('disabled', $crawler->filter('#right_assignment_username')->attr('disabled'));
        // a fixed member: no suggestions
        self::assertNull($crawler->filter('#right_assignment_username')->attr('data-member-suggest'));
        self::assertCount(0, $crawler->filter('#right_assignment_username_list'));
        $client->submit($crawler->filter('form[name="right_assignment"]')->form([
            'right_assignment[right]' => (string) $groupId,
            'right_assignment[level]' => '3',
            'right_assignment[scope]' => '',
            'right_assignment[comment]' => 'Without scope',
        ]));
        self::assertResponseRedirects('/admin/rights/list/member/member-empty');
        self::assertSame('"All"', $connection->fetchOne(
            'SELECT rv.Scope FROM rightsvolunteers rv INNER JOIN member m ON m.id = rv.IdMember WHERE m.Username = ? AND rv.IdRight = ?',
            ['member-empty', $groupId],
        ));
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
        $page = "/admin/rights/list/rights?right={$wordsId}";

        // GET doesn't remove; the dialog posts with the token and the page to come back to
        $client->request('GET', "/admin/rights/remove/{$wordsId}/member-empty");
        self::assertResponseStatusCodeSame(405);
        $crawler = $client->request('GET', $page);
        $client->request('POST', "/admin/rights/remove/{$wordsId}/member-empty");
        self::assertResponseStatusCodeSame(403);
        $client->request('POST', "/admin/rights/remove/{$wordsId}/member-empty", [
            '_token' => $this->removeToken($crawler),
            'return' => $page,
        ]);
        self::assertResponseRedirects($page);

        $removed = $connection->fetchAssociative(
            'SELECT Level, Comment, created FROM rightsvolunteers WHERE id = ?',
            [$assignmentId],
        );
        self::assertIsArray($removed);
        self::assertSame(0, (int) $removed['Level']);
        self::assertStringContainsString('Removed by member-2 on ', $removed['Comment']);
        self::assertSame('2020-01-02 03:04:05', $removed['created']);

        $crawler = $client->request('GET', '/admin/rights/list/member/member-empty');
        self::assertStringContainsString('Original assignment', $crawler->filter('.c-radmin-history')->text());

        $crawler = $client->request('GET', '/admin/rights/assign/member-empty');
        $client->submit($crawler->filter('form[name="right_assignment"]')->form([
            'right_assignment[right]' => (string) $wordsId,
            'right_assignment[level]' => '7',
            'right_assignment[scope]' => '"All"',
            'right_assignment[comment]' => 'Assigned again',
        ]));
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

    public function testEditingReturnsToTheViewItCameFrom(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $wordsId = $this->getRightId($connection, 'Words');
        $this->addRightAssignment($connection, 'member-empty', $wordsId, 10, '"All"', 'Before');
        $this->grantManagementRight($connection, 'member-2', 'Rights', '"All"');
        $this->login($client, 'member-2', $entityManager);
        $page = "/admin/rights/list/rights?right={$wordsId}";

        $crawler = $client->request('GET', "/admin/rights/edit/{$wordsId}/member-empty", ['return' => $page]);
        self::assertResponseIsSuccessful();
        // member and right are fixed: the right as its name
        self::assertSame('disabled', $crawler->filter('#right_assignment_username')->attr('disabled'));
        self::assertSame('Words', $crawler->filter('#c-radmin-right')->attr('value'));
        self::assertCount(0, $crawler->filter('select#right_assignment_right'));
        self::assertSame($page, $crawler->filter('.c-page-head__back')->attr('href'));

        $client->submit($crawler->filter('form[name="right_assignment"]')->form([
            'right_assignment[level]' => '4',
            'right_assignment[scope]' => '"All"',
            'right_assignment[comment]' => 'After',
        ]));
        self::assertResponseRedirects($page);
        self::assertSame(4, $this->level($connection, 'member-empty', $wordsId));

        // a return outside the rights tool leads to the member's rights
        $crawler = $client->request('GET', "/admin/rights/edit/{$wordsId}/member-empty", ['return' => 'https://example.com/']);
        $client->submit($crawler->filter('form[name="right_assignment"]')->form());
        self::assertResponseRedirects('/admin/rights/list/member/member-empty');
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
        $client->request('GET', '/admin/rights/create');
        self::assertResponseStatusCodeSame(404);

        $client->loginUser($manager);
        $crawler = $client->request('GET', '/members/member-1');
        self::assertResponseIsSuccessful();
        // The profile content, admin tools included, is rendered once per profile language
        self::assertGreaterThanOrEqual(1, $crawler->filter('.p-pro-admin-tools a[href="/admin/rights/list/member/member-1"]')->count());
    }

    /**
     * The token of the remove dialog of a rights page.
     */
    private function removeToken(Crawler $crawler): string
    {
        return $crawler->filter('[data-remove-form] input[name="_token"]')->attr('value');
    }

    private function level(Connection $connection, string $username, int $rightId): int
    {
        return (int) $connection->fetchOne(
            'SELECT rv.Level FROM rightsvolunteers rv INNER JOIN member m ON m.id = rv.IdMember WHERE m.Username = ? AND rv.IdRight = ?',
            [$username, $rightId],
        );
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

    private function setItemsPerPage(Connection $connection, string $username, int $itemsPerPage): void
    {
        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO memberspreferences (IdMember, IdPreference, Value, updated, created)
                SELECT m.id, p.id, ?, NOW(), NOW()
                FROM member m
                INNER JOIN preferences p ON p.codename = 'ItemsPerPage'
                WHERE m.Username = ?
                SQL,
            [(string) $itemsPerPage, $username],
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
