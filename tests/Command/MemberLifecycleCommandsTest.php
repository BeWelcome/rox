<?php

namespace App\Tests\Command;

use App\Doctrine\MemberStatusType;
use App\Entity\ForumThread;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\Email;

/**
 * The daily commands around inactive and new members, the forum's edit lock and the data retention.
 */
#[Group('integration')]
final class MemberLifecycleCommandsTest extends KernelTestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->connection = static::getContainer()->get(EntityManagerInterface::class)->getConnection();
    }

    public function testMembersAwayForAYearAreRemindedOnceAYear(): void
    {
        $this->setMember('member-4', MemberStatusType::ACTIVE, lastActive: '-2 years', updated: '-2 years');
        $this->setMember('member-5', MemberStatusType::ACTIVE, lastActive: '-1 month', updated: '-2 years');

        $this->execute('members:remind:login');

        self::assertSame(MemberStatusType::OUT_OF_REMIND, $this->member('member-4')['Status']);
        self::assertSame(1, (int) $this->member('member-4')['Reminders']);
        self::assertSame(MemberStatusType::ACTIVE, $this->member('member-5')['Status']);
        self::assertCount(1, $this->mailsTo('member-4'));
        self::assertCount(0, $this->mailsTo('member-5'));

        // reminded today: not again tomorrow
        $this->execute('members:remind:login');
        self::assertSame(1, (int) $this->member('member-4')['Reminders']);
    }

    public function testMembersAwayForFiveYearsAreSuspendedAndLaterHandedToTheRetention(): void
    {
        $this->setMember('member-4', MemberStatusType::OUT_OF_REMIND, lastActive: '-6 years', updated: '-1 year');
        $this->setMember('member-5', MemberStatusType::SUSPENDED, lastActive: '-6 years', updated: '-3 months');

        $this->execute('members:suspend:notification');

        self::assertSame(MemberStatusType::SUSPENDED, $this->member('member-4')['Status']);
        self::assertSame(100, (int) $this->member('member-4')['Reminders']);
        $mails = $this->mailsTo('member-4');
        self::assertCount(1, $mails);
        self::assertSame('email.suspension.notice.subject', $mails[0]->getSubject());

        // suspended three months ago: the next data:retention run takes the member
        self::assertSame(MemberStatusType::ASKED_TO_LEAVE, $this->member('member-5')['Status']);
        self::assertGreaterThan(365, (int) $this->connection->fetchOne(
            "SELECT DATEDIFF(NOW(), LastActive) FROM member WHERE Username = 'member-5'"
        ));
        self::assertCount(0, $this->mailsTo('member-5'));
    }

    public function testSignupsNotFinishedWithinTwoWeeksAreRemoved(): void
    {
        $this->setMember('member-2', MemberStatusType::AWAITING_MAIL_CONFIRMATION, created: '-15 days');
        $this->setMember('member-3', MemberStatusType::MAIL_CONFIRMED, created: '-15 days');
        $this->setMember('member-4', MemberStatusType::ACCOUNT_ACTIVATED, created: '-15 days');
        $this->setMember('member-5', MemberStatusType::ACCOUNT_ACTIVATED, created: '-13 days');

        $this->execute('members:remove:notconfirmed');

        self::assertSame(MemberStatusType::ASKED_TO_LEAVE, $this->member('member-2')['Status']);
        self::assertSame(MemberStatusType::ASKED_TO_LEAVE, $this->member('member-3')['Status']);
        self::assertSame(MemberStatusType::ASKED_TO_LEAVE, $this->member('member-4')['Status']);
        self::assertSame(MemberStatusType::ACCOUNT_ACTIVATED, $this->member('member-5')['Status']);
    }

    public function testPostsWithAReplyCanNoLongerBeEditedAfterHalfAnHour(): void
    {
        $thread = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(ForumThread::class)->findOneBy(['title' => 'Tips for first-time hosts?']);
        $this->connection->executeStatement(
            "UPDATE forum_post SET OwnerCanStillEdit = 'Yes', create_time = NOW() - INTERVAL 1 HOUR WHERE threadid = ?",
            [$thread->getId()],
        );
        $first = $thread->getFirstPost()->getId();
        $last = $thread->getLastPost()->getId();
        self::assertNotSame($first, $last);

        $this->execute('forum:lock-edit');

        $canEdit = fn (int $post): string => $this->connection->fetchOne(
            'SELECT OwnerCanStillEdit FROM forum_post WHERE id = ?',
            [$post],
        );
        self::assertSame('No', $canEdit($first));
        // nobody replied to the last post yet
        self::assertSame('Yes', $canEdit($last));
    }

    public function testTheRetentionRemovesTheGalleryTranslationsAndAvatarsAndPurgesTheConversations(): void
    {
        $this->setMember('member-2', MemberStatusType::ASKED_TO_LEAVE, lastActive: '-2 years');
        $memberId = (int) $this->member('member-2')['id'];
        $this->connection->executeStatement(
            "INSERT INTO member_translation (object_id, locale, field, content)
             VALUES (?, 'de', 'aboutMe', 'Über mich')",
            [$memberId],
        );
        $avatars = static::getContainer()->getParameter('data_directory') . '/user/avatars/';
        $filesystem = new Filesystem();
        // the member's avatar, and one of a member whose id starts with the same digits
        $filesystem->dumpFile($avatars . $memberId . '_30_30', 'x');
        $filesystem->dumpFile($avatars . $memberId . '9999_30_30', 'x');

        try {
            $this->execute('data:retention');

            self::assertSame('retired_' . $memberId, $this->connection->fetchOne(
                'SELECT Username FROM member WHERE id = ?',
                [$memberId],
            ));
            self::assertSame(0, $this->rows('gallery WHERE user_id_foreign = ?', [$memberId]));
            self::assertSame(0, $this->rows('gallery_items WHERE user_id_foreign = ?', [$memberId]));
            self::assertSame(0, $this->rows('member_translation WHERE object_id = ?', [$memberId]));
            self::assertSame(0, $this->rows(
                "message WHERE (sender_id = :id AND FIND_IN_SET('senderpurged', IFNULL(delete_request, '')) = 0)
                 OR (receiver_id = :id AND FIND_IN_SET('receiverpurged', IFNULL(delete_request, '')) = 0)",
                ['id' => $memberId],
            ));
            self::assertFileDoesNotExist($avatars . $memberId . '_30_30');
            self::assertFileExists($avatars . $memberId . '9999_30_30');
        } finally {
            $filesystem->remove([$avatars . $memberId . '_30_30', $avatars . $memberId . '9999_30_30']);
        }
    }

    private function execute(string $command): void
    {
        static::getContainer()->get(EntityManagerInterface::class)->clear();
        $tester = new CommandTester(new Application(self::$kernel)->find($command));
        self::assertSame(0, $tester->execute([]), $tester->getDisplay());
    }

    /**
     * @param array<int|string, mixed> $parameters
     */
    private function rows(string $from, array $parameters): int
    {
        return (int) $this->connection->fetchOne('SELECT COUNT(*) FROM ' . $from, $parameters);
    }

    private function setMember(
        string $username,
        string $status,
        ?string $lastActive = null,
        ?string $updated = null,
        ?string $created = null,
    ): void {
        $this->connection->executeStatement(
            'UPDATE member SET Status = ?, LastActive = ?, updated = ?, created = COALESCE(?, created), Reminders = 0
             WHERE Username = ?',
            [
                $status,
                $lastActive ? new DateTime($lastActive)->format('Y-m-d H:i:s') : null,
                $updated ? new DateTime($updated)->format('Y-m-d H:i:s') : null,
                $created ? new DateTime($created)->format('Y-m-d H:i:s') : null,
                $username,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function member(string $username): array
    {
        return $this->connection->fetchAssociative('SELECT * FROM member WHERE Username = ?', [$username]);
    }

    /**
     * @return list<Email>
     */
    private function mailsTo(string $username): array
    {
        $email = $this->member($username)['Email'];

        return array_values(array_filter(
            self::getMailerMessages(),
            static fn ($message): bool => $message instanceof Email && $message->getTo()[0]->getAddress() === $email,
        ));
    }
}
