<?php

namespace App\Tests\Controller;

use App\Entity\Flag;
use App\Entity\Member;
use App\Model\Admin\FlagsModel;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Group('integration')]
final class AdminFlagsControllerTest extends WebTestCase
{
    public function testAccessIsDeniedWithoutFlagsManagementRight(): void
    {
        $client = static::createClient();
        // member-5 has no rights; bwadmin has them all
        $this->login($client, 'member-5');

        $client->request('GET', '/admin/flags');

        self::assertResponseStatusCodeSame(403);
    }

    public function testTheProfileBlocksAndUnblocksForumPostingWithTheSwitch(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $this->grantFlagsManagement($connection, 'bwadmin', '"All"');
        $flagId = $this->forumPostingFlag($connection);
        $this->login($client, 'bwadmin', $entityManager);

        // not blocked: the switch is off, the dialog asks why
        $crawler = $client->request('GET', '/members/member-empty');
        self::assertResponseIsSuccessful();
        $panel = $crawler->filter('.p-pro-flag')->first();
        self::assertSame('false', $panel->filter('[role="switch"]')->attr('aria-checked'));
        self::assertSame('/admin/flags', $panel->filter('.p-pro-flag__history')->attr('href'));
        $form = $crawler->filter('form[action="/admin/flags/member-empty/block"]')->first();
        self::assertCount(1, $form->filter('textarea[name="comment"]'));
        // the Flags link of the Admin box is the switch now
        self::assertCount(0, $crawler->filter('.p-pro-admin-tools__link[href*="/admin/flags"]'));

        // without a reason nothing happens
        $client->submit($form->form(['comment' => '  ']));
        self::assertResponseRedirects('/members/member-empty');
        self::assertSame(0, $this->countFlagAssignments($connection, 'member-empty', $flagId));

        $client->submit($form->form(['comment' => 'Spammed three groups']));
        self::assertResponseRedirects('/members/member-empty');
        $row = $connection->fetchAssociative(
            'SELECT fm.Level, fm.Scope, fm.Comment FROM flagsmembers fm INNER JOIN member m ON m.id = fm.IdMember WHERE m.Username = ? AND fm.IdFlag = ?',
            ['member-empty', $flagId],
        );
        self::assertSame(['Level' => 1, 'Scope' => '', 'Comment' => 'Spammed three groups'], ['Level' => (int) $row['Level']] + $row);
        self::assertSame(1, $this->legacyCurrentLevel($connection, 'member-empty', $flagId));

        // blocked: the switch is on, with the reason; the dialog asks before removing
        $crawler = $client->request('GET', '/members/member-empty');
        $panel = $crawler->filter('.p-pro-flag')->first();
        self::assertSame('true', $panel->filter('[role="switch"]')->attr('aria-checked'));
        self::assertStringContainsString('Spammed three groups', $panel->filter('.p-pro-flag__comment')->text());
        $form = $crawler->filter('form[action="/admin/flags/member-empty/unblock"]')->first();
        self::assertCount(0, $form->filter('textarea'));

        // blocking twice doesn't add a second block
        $client->request('POST', '/admin/flags/member-empty/block', [
            '_token' => $form->filter('input[name="_token"]')->attr('value'),
            'comment' => 'Again',
        ]);
        self::assertSame(1, $this->countFlagAssignments($connection, 'member-empty', $flagId));

        $client->submit($form->form());
        self::assertResponseRedirects('/members/member-empty');
        self::assertSame(0, $this->legacyCurrentLevel($connection, 'member-empty', $flagId));
        self::assertStringContainsString('Removed by bwadmin on ', (string) $connection->fetchOne(
            'SELECT fm.Comment FROM flagsmembers fm INNER JOIN member m ON m.id = fm.IdMember WHERE m.Username = ? AND fm.IdFlag = ?',
            ['member-empty', $flagId],
        ));

        // the history: one earlier block
        $crawler = $client->request('GET', '/members/member-empty');
        self::assertSame('/admin/flags?member=member-empty', $crawler->filter('.p-pro-flag__history')->first()->attr('href'));
    }

    public function testBlockingAgainStartsANewRowThatMainReadsAsCurrent(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $this->grantFlagsManagement($connection, 'bwadmin', '"All"');
        $flagId = $this->forumPostingFlag($connection);
        // removed this very second: the new row must still be the newest
        $this->addFlagAssignment($connection, 'member-empty', $flagId, 0, 'Earlier block', date('Y-m-d H:i:s'));
        $this->login($client, 'bwadmin', $entityManager);

        $crawler = $client->request('GET', '/members/member-empty');
        $client->submit($crawler->filter('form[action="/admin/flags/member-empty/block"]')->first()->form(['comment' => 'Blocked again']));

        $cycles = $connection->fetchAllAssociative(
            <<<'SQL'
                SELECT fm.Level, fm.Comment, fm.created
                FROM flagsmembers fm
                INNER JOIN member m ON m.id = fm.IdMember
                WHERE m.Username = ? AND fm.IdFlag = ?
                ORDER BY fm.created DESC, fm.id DESC
                SQL,
            ['member-empty', $flagId],
        );
        self::assertCount(2, $cycles);
        self::assertSame([1, 'Blocked again'], [(int) $cycles[0]['Level'], $cycles[0]['Comment']]);
        self::assertGreaterThan($cycles[1]['created'], $cycles[0]['created']);
        self::assertSame(1, $this->legacyCurrentLevel($connection, 'member-empty', $flagId));
    }

    public function testTheListShowsEveryFlagCurrentOnesFirstAndRemovesFromThere(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $this->grantFlagsManagement($connection, 'bwadmin', '"All"');
        $flagId = $this->forumPostingFlag($connection);
        $connection->executeStatement('DELETE FROM flagsmembers WHERE IdFlag = ?', [$flagId]);
        $this->addFlagAssignment($connection, 'member-1', $flagId, 0, 'Removed after a talk', '2026-06-01 00:00:00');
        $this->addFlagAssignment($connection, 'member-2', $flagId, 1, 'Paid tours', '2026-05-01 00:00:00');
        $this->addFlagAssignment($connection, 'member-3', $flagId, 1, 'Same request in 12 groups', '2026-10-01 00:00:00');
        // another flag isn't listed
        $otherId = $this->createFlag($connection, 'Some other flag ' . random_int(1000, 9999));
        $this->addFlagAssignment($connection, 'member-4', $otherId, 1, 'Not this flag', '2026-10-02 00:00:00');
        $this->login($client, 'bwadmin', $entityManager);

        $crawler = $client->request('GET', '/admin/flags');
        self::assertResponseIsSuccessful();
        $rows = $crawler->filter('.c-radmin-table__row:not(.c-radmin-table__row--head)');
        // the current ones first, newest first, then the removed one
        self::assertSame(['member-3', 'member-2', 'member-1'], $rows->filter('.c-radmin-member__name')->each(
            static fn (Crawler $name): string => $name->text(),
        ));
        self::assertCount(2, $crawler->filter('[data-remove]'));
        self::assertCount(1, $rows->eq(2)->filter('.c-radmin-member__name.is-removed'));
        self::assertCount(0, $rows->eq(2)->filter('[data-remove]'));
        self::assertSame('/members/member-3', $rows->eq(0)->filter('.c-radmin-member')->attr('href'));

        $crawler = $client->request('GET', '/admin/flags', ['member' => 'member-2']);
        self::assertCount(1, $crawler->filter('.c-radmin-table__row:not(.c-radmin-table__row--head)'));

        // removing from the list leads back to it
        $client->request('POST', '/admin/flags/member-2/unblock', [
            '_token' => $crawler->filter('[data-remove-form] input[name="_token"]')->attr('value'),
            'return' => '/admin/flags?member=member-2',
        ]);
        self::assertResponseRedirects('/admin/flags?member=member-2');
        self::assertSame(0, $this->legacyCurrentLevel($connection, 'member-2', $flagId));
        $client->followRedirect();
        self::assertSelectorExists('.c-inline-flash');
    }

    public function testBlockingNeedsTheTokenAndTheFlagsRight(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $flagId = $this->forumPostingFlag($connection);

        $this->login($client, 'member-5', $entityManager);
        $client->request('POST', '/admin/flags/member-empty/block', ['comment' => 'No right']);
        self::assertResponseStatusCodeSame(403);
        $crawler = $client->request('GET', '/members/member-empty');
        self::assertCount(0, $crawler->filter('.p-pro-flag'));

        $this->grantFlagsManagement($connection, 'bwadmin', '"All"');
        $this->login($client, 'bwadmin', $entityManager);
        $client->request('POST', '/admin/flags/member-empty/block', ['comment' => 'No token']);
        self::assertResponseStatusCodeSame(403);
        $client->request('GET', '/admin/flags/member-empty/block');
        self::assertResponseStatusCodeSame(405);
        self::assertSame(0, $this->countFlagAssignments($connection, 'member-empty', $flagId));
    }

    public function testAStaleBlockCannotBeRemoved(): void
    {
        static::createClient();
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $flagId = $this->forumPostingFlag($connection);
        $this->addFlagAssignment($connection, 'member-empty', $flagId, 5, 'Older cycle', '2020-01-01 00:00:00');
        $entityManager->clear();

        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'member-empty']);
        $manager = $entityManager->getRepository(Member::class)->findOneBy(['username' => 'bwadmin']);
        $flag = $entityManager->getRepository(Flag::class)->find($flagId);
        self::assertInstanceOf(Member::class, $member);
        self::assertInstanceOf(Member::class, $manager);
        self::assertInstanceOf(Flag::class, $flag);

        $model = static::getContainer()->get(FlagsModel::class);
        $staleBlock = $model->findBlock($member, $flag);
        self::assertNotNull($staleBlock);

        $this->addFlagAssignment($connection, 'member-empty', $flagId, 6, 'Current cycle', '2021-01-01 00:00:00');
        self::assertFalse($model->unblock($staleBlock, $manager));

        $currentBlock = $model->findBlock($member, $flag);
        self::assertNotNull($currentBlock);
        $connection->update('flagsmembers', ['Level' => 0, 'Comment' => 'Removed by another request'], ['id' => $currentBlock->getId()]);
        self::assertFalse($model->unblock($currentBlock, $manager));
        self::assertSame(
            ['Older cycle', 'Removed by another request'],
            $connection->fetchFirstColumn('SELECT Comment FROM flagsmembers WHERE IdFlag = ? AND IdMember = ? ORDER BY created, id', [$flagId, $member->getId()]),
        );
    }

    public function testFlagRoutesAndTheirOldPages(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $this->grantFlagsManagement($entityManager->getConnection(), 'bwadmin', '"All"');
        $this->login($client, 'bwadmin', $entityManager);
        $router = static::getContainer()->get(UrlGeneratorInterface::class);

        self::assertSame('/admin/flags', $router->generate('admin_flags'));
        self::assertSame('/admin/flags/member-1/block', $router->generate('admin_flags_block', ['username' => 'member-1']));
        self::assertSame('/admin/flags/member-1/unblock', $router->generate('admin_flags_unblock', ['username' => 'member-1']));
        foreach (['/admin/flags/overview', '/admin/flags/list/members', '/admin/flags/assign', '/admin/flags/create'] as $gone) {
            $client->request('GET', $gone);
            self::assertResponseStatusCodeSame(404, $gone);
        }
    }

    /**
     * The id of the NotAllowedToPostInForum flag; created where the test database lacks it.
     */
    private function forumPostingFlag(Connection $connection): int
    {
        $id = $connection->fetchOne('SELECT id FROM flags WHERE Name = ?', [Flag::NOT_ALLOWED_TO_POST_IN_FORUM]);

        return false === $id ? $this->createFlag($connection, Flag::NOT_ALLOWED_TO_POST_IN_FORUM) : (int) $id;
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
