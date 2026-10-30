<?php

namespace App\Tests\Command;

use App\Entity\ForumThread;
use App\Entity\Group as GroupEntity;
use App\Entity\Member;
use App\Model\ForumModel;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Mime\Email;

/**
 * New members are in the BeWelcome group: their first mail about the agora tells them so, with a link that stops
 * these mails without logging in. Later mails don't.
 */
#[Group('integration')]
final class AgoraWelcomeNoticeTest extends KernelTestCase
{
    public function testOnlyTheFirstAgoraMailHasTheNotice(): void
    {
        self::bootKernel();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();
        $thread = $entityManager->getRepository(ForumThread::class)->findOneBy(['title' => 'Tips for first-time hosts?']);
        // authors get no mail about their own posts
        $authors = [$thread->getFirstPost()->getAuthor()->getUsername(), $thread->getLastPost()->getAuthor()->getUsername()];
        $username = current(array_diff(['member-1', 'member-2', 'member-3', 'member-4', 'member-5'], $authors));
        $member = $entityManager->getRepository(Member::class)->findOneBy(['username' => $username]);
        $connection->executeStatement(
            "INSERT INTO group_membership (member_id, group_id, status, accept_mass_mail, notifications_enabled, welcome_notice_sent, created, updated)
             VALUES (?, ?, 'In', 0, 1, 0, NOW(), NOW()) ON DUPLICATE KEY UPDATE notifications_enabled = 1, welcome_notice_sent = 0",
            [$member->getId(), GroupEntity::BEWELCOME],
        );
        $connection->executeStatement("UPDATE posts_notificationqueue SET Status = 'Sent'");
        $forumModel = static::getContainer()->get(ForumModel::class);

        $forumModel->queueNotifications($thread->getFirstPost(), ForumModel::NOTIFICATION_REPLY);
        $m = $entityManager->getRepository(\App\Entity\GroupMembership::class)->findOneBy(['group' => GroupEntity::BEWELCOME, 'member' => $member->getId()]);
        $first = $this->sendAndGetMail($member);

        $connection->executeStatement('DELETE FROM posts_notificationqueue WHERE IdMember = ?', [$member->getId()]);
        $forumModel->queueNotifications($thread->getLastPost(), ForumModel::NOTIFICATION_REPLY);
        $second = $this->sendAndGetMail($member);

        self::assertStringContainsString('/forums/agora/notifications/off/' . $member->getId(), $first);
        self::assertStringNotContainsString('/forums/agora/notifications/off/', $second);
        self::assertSame(1, (int) $connection->fetchOne(
            'SELECT welcome_notice_sent FROM group_membership WHERE member_id = ? AND group_id = ?',
            [$member->getId(), GroupEntity::BEWELCOME],
        ));
    }

    private function sendAndGetMail(Member $member): string
    {
        // notifications go out five minutes after the post at the earliest
        static::getContainer()->get(EntityManagerInterface::class)->getConnection()->executeStatement(
            "UPDATE posts_notificationqueue SET created = NOW() - INTERVAL 10 MINUTE WHERE Status = 'ToSend'"
        );
        $tester = new CommandTester(new Application(self::$kernel)->find('send:notifications'));
        self::assertSame(0, $tester->execute([]), $tester->getDisplay());

        foreach (array_reverse(self::getMailerMessages()) as $message) {
            if ($message instanceof Email && $message->getTo()[0]->getAddress() === $member->getEmail()) {
                return (string) $message->getHtmlBody();
            }
        }
        self::fail('No mail to ' . $member->getUsername() . ': ' . $tester->getDisplay());
    }
}
