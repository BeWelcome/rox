<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\Session;

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

        $this->createGroupFixture($connection, $groupId, $owner, $applicant, $inactiveMember);
        $client->loginUser($owner);

        $this->confirmDelete($client, $groupId);

        self::assertResponseRedirects('/groups/');
        self::assertSame(0, $this->countRows($connection, 'group', 'id', $groupId));
        self::assertSame(0, $this->countRows($connection, 'group_membership', 'group_id', $groupId));
        self::assertSame(0, $this->countRows($connection, 'privilegescopes', 'IdType', $groupId));
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

        $this->createGroupFixture($connection, $groupId, $owner, $applicant, $inactiveMember);
        $this->createGroupDeleteBlocker($connection, $groupId);
        $client->loginUser($owner);

        $this->confirmDelete($client, $groupId);

        self::assertResponseRedirects("/group/{$groupId}/delete");
        /** @var Session $session */
        $session = $client->getRequest()->getSession();
        self::assertNotEmpty($session->getFlashBag()->peek('error'));
        self::assertSame(1, $this->countRows($connection, 'group', 'id', $groupId));
        self::assertSame(3, $this->countRows($connection, 'group_membership', 'group_id', $groupId));
        self::assertSame(1, $this->countRows($connection, 'privilegescopes', 'IdType', $groupId));
    }

    public function testConfirmedDeleteRemovesTheGroupsForum(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();
        $owner = $this->getMember($entityManager, 'member-2');
        $groupId = random_int(1_500_000_000, 2_000_000_000);
        $this->createGroupFixture(
            $connection,
            $groupId,
            $owner,
            $this->getMember($entityManager, 'member-1'),
            $this->getMember($entityManager, 'member-banned'),
        );

        // move a fixture thread with its posts into the group, with a subscription and a queued notification
        $threadId = (int) $connection->fetchOne('SELECT t.id FROM forum_thread t WHERE t.IdGroup <> 0 ORDER BY t.id LIMIT 1');
        $postIds = $connection->fetchFirstColumn('SELECT id FROM forum_post WHERE threadid = ?', [$threadId]);
        self::assertNotEmpty($postIds, 'the fixtures contain a group thread with posts');
        $connection->executeStatement('UPDATE forum_thread SET IdGroup = ? WHERE id = ?', [$groupId, $threadId]);
        $connection->executeStatement(
            'INSERT INTO members_threads_subscribed (IdSubscriber, IdThread, ActionToWatch, UnSubscribeKey, notificationsEnabled, created)'
            . " VALUES (?, ?, 'replies', 'key', 1, NOW())",
            [$owner->getId(), $threadId],
        );
        $connection->executeStatement(
            'INSERT INTO posts_notificationqueue (IdMember, IdPost, created, Type, TableSubscription, IdSubscription, Status)'
            . " VALUES (?, ?, NOW(), 'reply', 'members_threads_subscribed', 0, 'ToSend')",
            [$owner->getId(), $postIds[0]],
        );
        $client->loginUser($owner);

        $this->confirmDelete($client, $groupId);

        self::assertResponseRedirects('/groups/');
        self::assertSame(0, $this->countRows($connection, 'group', 'id', $groupId));
        self::assertSame(0, $this->countRows($connection, 'forum_thread', 'id', $threadId));
        self::assertSame(0, $this->countRows($connection, 'forum_thread_translation', 'object_id', $threadId));
        self::assertSame(0, $this->countRows($connection, 'members_threads_subscribed', 'IdThread', $threadId));
        foreach ($postIds as $postId) {
            self::assertSame(0, $this->countRows($connection, 'forum_post', 'id', (int) $postId));
            self::assertSame(0, $this->countRows($connection, 'forum_post_translation', 'object_id', (int) $postId));
            self::assertSame(0, $this->countRows($connection, 'posts_notificationqueue', 'IdPost', (int) $postId));
        }
    }

    public function testDeletingNeedsTheToken(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();
        $owner = $this->getMember($entityManager, 'member-2');
        $groupId = random_int(1_500_000_000, 2_000_000_000);
        $this->createGroupFixture($connection, $groupId, $owner, $this->getMember($entityManager, 'member-1'), $this->getMember($entityManager, 'member-banned'));
        $client->loginUser($owner);

        $client->request('GET', "/group/{$groupId}/delete/true");
        self::assertResponseStatusCodeSame(405);
        $client->request('POST', "/group/{$groupId}/delete/true", ['_token' => 'wrong']);
        self::assertResponseStatusCodeSame(403);
        self::assertSame(1, $this->countRows($connection, 'group', 'id', $groupId));
    }

    /**
     * Deletes the group the way an admin does: the confirmation page's button (a POST with its token).
     */
    private function confirmDelete(KernelBrowser $client, int $groupId): void
    {
        $crawler = $client->request('GET', "/group/{$groupId}/delete");
        self::assertResponseIsSuccessful();
        $client->submit($crawler->filter('form[action$="/delete/true"]')->form());
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
                INSERT INTO `group`
                    (id, name, type, created, picture, members_only, approved)
                VALUES
                    (?, 'Issue 80 regression', 'Public', NOW(), '', 0, 1)
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
                    INSERT INTO group_membership
                        (updated, created, status, accept_mass_mail, notifications_enabled, member_id, group_id)
                    VALUES
                        (NOW(), NOW(), ?, 0, 1, ?, ?)
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
}
