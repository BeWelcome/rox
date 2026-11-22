<?php

namespace App\Tests\Command;

use App\Command\MembersSuspendCommand;
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
use Symfony\Component\Lock\LockFactory;
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
        $this->removeComments('member-4', received: true, written: true);
        $this->setMember('member-5', MemberStatusType::SUSPENDED, lastActive: '-6 years', updated: '-3 months');
        // suspended by this command (its marker), not by the volunteers
        $this->connection->executeStatement("UPDATE member SET Reminders = 100 WHERE Username = 'member-5'");
        $this->removeComments('member-5', received: true, written: true);

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

    /**
     * A run finishes its batch however long it takes; one that starts meanwhile (the cron starts one every 30
     * minutes) ends at once and leaves the members to the run at work.
     */
    public function testARetentionRunLeavesTheMembersToTheRunAtWork(): void
    {
        $this->setMember('member-5', MemberStatusType::ASKED_TO_LEAVE, lastActive: '-2 years');
        $memberId = (int) $this->member('member-5')['id'];
        $runAtWork = static::getContainer()->get(LockFactory::class)->createLock('data:retention', null);
        self::assertTrue($runAtWork->acquire());

        try {
            $display = $this->execute('data:retention');

            self::assertStringContainsString('The previous run is still at work', $display);
            self::assertSame('member-5', $this->member('member-5')['Username']);
        } finally {
            $runAtWork->release();
        }

        $this->execute('data:retention');
        self::assertSame('retired_' . $memberId, $this->connection->fetchOne(
            'SELECT Username FROM member WHERE id = ?',
            [$memberId],
        ));
    }

    /**
     * The first reminder doesn't lead to the suspension the next night: the member gets a year to come back.
     */
    public function testAMemberAwayForFiveYearsIsSuspendedAYearAfterTheReminder(): void
    {
        $this->setMember('member-4', MemberStatusType::ACTIVE, lastActive: '-6 years', updated: '-6 years');
        $this->removeComments('member-4', received: true, written: true);

        $this->execute('members:remind:login');
        $this->execute('members:suspend:notification');

        self::assertSame(MemberStatusType::OUT_OF_REMIND, $this->member('member-4')['Status']);
        self::assertSame(
            ['email.login.reminder.subject'],
            array_map(static fn (Email $mail): ?string => $mail->getSubject(), $this->mailsTo('member-4')),
            'the reminder, no suspension notice',
        );

        // eleven months after the reminder: still waiting
        $this->connection->executeStatement(
            "UPDATE member SET updated = ? WHERE Username = 'member-4'",
            [new DateTime('-11 months')->format('Y-m-d H:i:s')],
        );
        $this->execute('members:suspend:notification');
        self::assertSame(MemberStatusType::OUT_OF_REMIND, $this->member('member-4')['Status']);

        // a year after the reminder
        $this->connection->executeStatement(
            "UPDATE member SET updated = ? WHERE Username = 'member-4'",
            [new DateTime('-1 year -1 day')->format('Y-m-d H:i:s')],
        );
        $this->execute('members:suspend:notification');
        self::assertSame(MemberStatusType::SUSPENDED, $this->member('member-4')['Status']);
    }

    /**
     * Members the volunteers suspended (no marker) are handed to the retention a year after the suspension, when they
     * didn't get in touch; not two months after it, like those suspended after the reminders.
     */
    public function testMembersSuspendedByTheVolunteersLeaveAYearAfterTheSuspension(): void
    {
        $this->setMember('member-4', MemberStatusType::SUSPENDED, lastActive: '-1 month', updated: '-3 months');
        $this->setMember('member-5', MemberStatusType::SUSPENDED, lastActive: '-2 years', updated: '-13 months');
        $this->removeComments('member-5', received: true, written: true);

        $this->execute('members:suspend:notification');

        self::assertSame(MemberStatusType::SUSPENDED, $this->member('member-4')['Status']);
        self::assertSame(MemberStatusType::ASKED_TO_LEAVE, $this->member('member-5')['Status']);
        self::assertCount(0, $this->mailsTo('member-4'));
        self::assertCount(0, $this->mailsTo('member-5'));
    }

    /**
     * Members who got or wrote a comment are part of the references and aren't suspended, however long they're away.
     */
    public function testMembersWithACommentArentSuspended(): void
    {
        foreach (['member-4', 'member-5', 'member-6'] as $username) {
            $this->setMember($username, MemberStatusType::OUT_OF_REMIND, lastActive: '-6 years', updated: '-1 year -1 day');
        }
        $this->removeComments('member-4', received: false, written: true);
        $this->removeComments('member-5', received: true, written: false);
        $this->removeComments('member-6', received: true, written: true);

        $this->execute('members:suspend:notification');

        self::assertSame(MemberStatusType::OUT_OF_REMIND, $this->member('member-4')['Status'], 'got a comment');
        self::assertSame(MemberStatusType::OUT_OF_REMIND, $this->member('member-5')['Status'], 'wrote a comment');
        self::assertSame(MemberStatusType::SUSPENDED, $this->member('member-6')['Status'], 'no comments');
        self::assertCount(0, $this->mailsTo('member-4'));
        self::assertCount(1, $this->mailsTo('member-6'));
    }

    /**
     * Suspended members who got or wrote a comment stay suspended; the retention doesn't take them.
     */
    public function testSuspendedMembersWithACommentStaySuspended(): void
    {
        foreach (['member-4', 'member-6'] as $username) {
            $this->setMember($username, MemberStatusType::SUSPENDED, lastActive: '-6 years', updated: '-3 months');
            $this->connection->executeStatement('UPDATE member SET Reminders = 100 WHERE Username = ?', [$username]);
        }
        $this->removeComments('member-6', received: true, written: true);

        $this->execute('members:suspend:notification');

        self::assertSame(MemberStatusType::SUSPENDED, $this->member('member-4')['Status'], 'has comments');
        self::assertSame(MemberStatusType::ASKED_TO_LEAVE, $this->member('member-6')['Status'], 'no comments');
    }

    /**
     * The reminder and the suspension share a lock: while one is at work, a run of either ends at once and changes
     * nothing; the next run does the work.
     */
    public function testTheReminderAndTheSuspensionWaitForTheRunAtWork(): void
    {
        $this->setMember('member-4', MemberStatusType::ACTIVE, lastActive: '-2 years', updated: '-2 years');
        $this->setMember('member-6', MemberStatusType::OUT_OF_REMIND, lastActive: '-6 years', updated: '-1 year -1 day');
        $this->removeComments('member-6', received: true, written: true);
        $runAtWork = static::getContainer()->get(LockFactory::class)->createLock(MembersSuspendCommand::LOCK, null);
        self::assertTrue($runAtWork->acquire());

        try {
            self::assertStringContainsString('is at work', $this->execute('members:remind:login'));
            self::assertStringContainsString('is at work', $this->execute('members:suspend:notification'));

            self::assertSame(MemberStatusType::ACTIVE, $this->member('member-4')['Status']);
            self::assertSame(MemberStatusType::OUT_OF_REMIND, $this->member('member-6')['Status']);
            self::assertCount(0, $this->mailsTo('member-4'));
        } finally {
            $runAtWork->release();
        }

        $this->execute('members:remind:login');
        $this->execute('members:suspend:notification');
        self::assertSame(MemberStatusType::OUT_OF_REMIND, $this->member('member-4')['Status']);
        self::assertSame(MemberStatusType::SUSPENDED, $this->member('member-6')['Status']);
    }

    /**
     * A member due for the suspension isn't reminded again (that would put the suspension off by a year); a member
     * with comments, never suspended, gets the yearly reminder.
     */
    public function testAMemberDueForTheSuspensionIsntRemindedAgain(): void
    {
        foreach (['member-4', 'member-6'] as $username) {
            $this->setMember($username, MemberStatusType::OUT_OF_REMIND, lastActive: '-6 years', updated: '-1 year -1 day');
        }
        $this->removeComments('member-6', received: true, written: true);

        $this->execute('members:remind:login');

        self::assertCount(1, $this->mailsTo('member-4'), 'has comments: reminded');
        self::assertCount(0, $this->mailsTo('member-6'), 'due for the suspension: not reminded');

        $this->execute('members:suspend:notification');
        self::assertSame(MemberStatusType::OUT_OF_REMIND, $this->member('member-4')['Status']);
        self::assertSame(MemberStatusType::SUSPENDED, $this->member('member-6')['Status']);
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
        $otherId = (int) $this->member('member-3')['id'];
        // a private note of the member, with a category, and one another member wrote about them
        $note = $this->note($memberId, $otherId);
        $this->connection->executeStatement("INSERT INTO note_category (note_id, name) VALUES (?, 'zztest')", [$note]);
        $noteAbout = $this->note($otherId, $memberId);
        $this->connection->executeStatement(
            "INSERT INTO member_translation (object_id, locale, field, content)
             VALUES (?, 'de', 'aboutMe', 'Über mich')",
            [$memberId],
        );
        $avatars = static::getContainer()->getParameter('data_directory') . '/user/avatars/';
        $filesystem = new Filesystem();
        // the member's pictures (an old name, the original, a size of the pages), one of a member whose id starts with
        // the same digits, and one of a size nobody asks for (left to a clean-up of its own)
        $own = [$memberId . '_30_30', $memberId . '_96', $memberId . '_original', $memberId . '_192_192', $memberId . '_48_48.webp'];
        $others = [$memberId . '9999_30_30', $memberId . '_77_77'];
        foreach ([...$own, ...$others] as $name) {
            $filesystem->dumpFile($avatars . $name, 'x');
        }

        try {
            $this->execute('data:retention');

            self::assertSame('retired_' . $memberId, $this->connection->fetchOne(
                'SELECT Username FROM member WHERE id = ?',
                [$memberId],
            ));
            self::assertSame(0, $this->rows('gallery WHERE user_id_foreign = ?', [$memberId]));
            self::assertSame(0, $this->rows('gallery_items WHERE user_id_foreign = ?', [$memberId]));
            self::assertSame(0, $this->rows('member_translation WHERE object_id = ?', [$memberId]));
            self::assertSame(0, $this->rows('mycontacts WHERE IdMember = ?', [$memberId]), 'the member\'s notes stayed');
            self::assertSame(0, $this->rows('note_category WHERE note_id = ?', [$note]), 'the note\'s categories stayed');
            self::assertSame(1, $this->rows('mycontacts WHERE id = ?', [$noteAbout]), 'a note about the member went');
            self::assertSame(0, $this->rows(
                "message WHERE (sender_id = :id AND FIND_IN_SET('senderpurged', IFNULL(delete_request, '')) = 0)
                 OR (receiver_id = :id AND FIND_IN_SET('receiverpurged', IFNULL(delete_request, '')) = 0)",
                ['id' => $memberId],
            ));
            foreach ($own as $name) {
                self::assertFileDoesNotExist($avatars . $name);
            }
            foreach ($others as $name) {
                self::assertFileExists($avatars . $name);
            }
        } finally {
            $filesystem->remove(array_map(static fn (string $name): string => $avatars . $name, [...$own, ...$others]));
        }
    }

    public function testTheRetentionRemovesWhatTiesTheMemberToOthersAndKeepsTheContributions(): void
    {
        $this->setMember('member-2', MemberStatusType::ASKED_TO_LEAVE, lastActive: '-2 years');
        $memberId = (int) $this->member('member-2')['id'];
        $otherId = (int) $this->member('member-3')['id'];
        $activeId = (int) $this->member('member-4')['id'];
        $now = new DateTime()->format('Y-m-d H:i:s');
        $connection = $this->connection;

        // what ties the member to others or only served them
        $connection->executeStatement('INSERT INTO friend (left_id, right_id, requester_id, created) VALUES (?, ?, ?, NOW())', [$memberId, $otherId, $memberId]);
        $connection->executeStatement('INSERT INTO profilesvisits (IdMember, IdVisitor, created, updated) VALUES (?, ?, NOW(), NOW()), (?, ?, NOW(), NOW())', [$memberId, $otherId, $otherId, $memberId]);
        $connection->executeStatement("INSERT INTO member_photo (member_id, FilePath, created) VALUES (?, 'zztest.jpg', NOW())", [$memberId]);
        $connection->executeStatement("INSERT INTO memberspreferences (IdMember, IdPreference, Value, created, updated) SELECT ?, id, 'zztest', NOW(), NOW() FROM preferences ORDER BY id LIMIT 1", [$memberId]);
        $connection->executeStatement("INSERT INTO passwordreset (member_id, token, generated) VALUES (?, 'zztest-token', NOW())", [$memberId]);
        $connection->executeStatement("INSERT INTO members_threads_subscribed (IdSubscriber, IdThread, ActionToWatch, UnSubscribeKey, notificationsEnabled, created) SELECT ?, id, 'replies', 'zztestkey', 1, NOW() FROM forum_thread ORDER BY id LIMIT 1", [$memberId]);
        $connection->executeStatement("INSERT INTO forum_post_notification (receiver_id, post_id, subscription_id, subscription_table, status, type, created) SELECT ?, id, 0, 'NotSet', 'ToSend', 'reply', NOW() FROM forum_post ORDER BY id LIMIT 1", [$memberId]);
        $connection->executeStatement('INSERT INTO member_activity_notification_sent (member_id, activity_id, created) SELECT ?, id, NOW() FROM activity ORDER BY id LIMIT 1', [$memberId]);
        $memberships = $this->rows('group_membership WHERE member_id = ?', [$memberId]);
        self::assertGreaterThan(0, $memberships, 'the fixtures give member-2 group memberships');

        // answers to an activity that is over and to one still to come
        [$past, $future] = array_map('intval', $connection->fetchFirstColumn('SELECT id FROM activity ORDER BY id LIMIT 2'));
        $connection->executeStatement('UPDATE activity SET starts = ? - INTERVAL 3 DAY, ends = ? - INTERVAL 2 DAY WHERE id = ?', [$now, $now, $past]);
        $connection->executeStatement('UPDATE activity SET starts = ? + INTERVAL 3 DAY, ends = ? + INTERVAL 4 DAY WHERE id = ?', [$now, $now, $future]);
        $connection->executeStatement('DELETE FROM activity_attendee WHERE attendee_id = ? AND activity_id IN (?, ?)', [$memberId, $past, $future]);
        $connection->executeStatement("INSERT INTO activity_attendee (activity_id, attendee_id, organizer, status, comment) VALUES (?, ?, 0, 1, 'I come with my friend Anna Smith'), (?, ?, 0, 1, 'See you')", [$past, $memberId, $future, $memberId]);

        // a donation
        $connection->executeStatement("INSERT INTO donation (donor_id, email, status_private, created, amount, money, name_given, reference_paypal, member_comment, system_comment) VALUES (?, 'zztest@example.org', 'showamountonly', NOW(), '10.00', 'EUR', 'Zztest Name', 'zztestref', 'For the servers', '')", [$memberId]);

        // a conversation with a member retired before (a hosting request), and one with an active member
        $connection->executeStatement("UPDATE member SET Username = CONCAT('retired_', id), Status = ? WHERE id = ?", [MemberStatusType::ASKED_TO_LEAVE, $otherId]);
        $connection->executeStatement('INSERT INTO request (arrival, number_of_travellers, status, created) VALUES (NOW() + INTERVAL 10 DAY, 1, 0, NOW())');
        $request = (int) $connection->lastInsertId();
        $retiredConversation = $this->message($memberId, $otherId, $request);
        $activeConversation = $this->message($memberId, $activeId, null);

        // a trip with a stop another member invited to, and one without invitations
        $connection->executeStatement("INSERT INTO trip (creator_id, summary, description, count_of_travellers, invitation_radius, created, additional_info) VALUES (?, 'Zztest trip of Anna', 'Find me on @anna', 1, 0, NOW(), 'none')", [$memberId]);
        $trip = (int) $connection->lastInsertId();
        $location = (int) $connection->fetchOne('SELECT location_id FROM leg ORDER BY id LIMIT 1');
        $connection->executeStatement('INSERT INTO leg (trip_id, location_id, arrival, departure) VALUES (?, ?, NOW() + INTERVAL 5 DAY, NOW() + INTERVAL 6 DAY), (?, ?, NOW() + INTERVAL 7 DAY, NOW() + INTERVAL 8 DAY)', [$trip, $location, $trip, $location]);
        [$invitedLeg, $otherLeg] = array_map('intval', $connection->fetchFirstColumn('SELECT id FROM leg WHERE trip_id = ? ORDER BY id', [$trip]));
        $connection->executeStatement('INSERT INTO request (arrival, number_of_travellers, status, created, invite_for_leg) VALUES (NOW() + INTERVAL 5 DAY, 1, 0, NOW(), ?)', [$invitedLeg]);
        $invitation = (int) $connection->lastInsertId();
        $this->message($activeId, $memberId, $invitation);
        // a trip without any invitation
        $connection->executeStatement("INSERT INTO trip (creator_id, summary, description, count_of_travellers, invitation_radius, created, additional_info) VALUES (?, 'Zztest trip 2', '', 1, 0, NOW(), 'none')", [$memberId]);
        $gone = (int) $connection->lastInsertId();
        $connection->executeStatement('INSERT INTO leg (trip_id, location_id, arrival, departure) VALUES (?, ?, NOW() + INTERVAL 5 DAY, NOW() + INTERVAL 6 DAY)', [$gone, $location]);

        $this->execute('data:retention');

        $removed = [
            'friend WHERE left_id = :id OR right_id = :id OR requester_id = :id',
            'group_membership WHERE member_id = :id',
            'profilesvisits WHERE IdMember = :id OR IdVisitor = :id',
            'member_photo WHERE member_id = :id',
            'memberspreferences WHERE IdMember = :id',
            'passwordreset WHERE member_id = :id',
            'members_threads_subscribed WHERE IdSubscriber = :id',
            'forum_post_notification WHERE receiver_id = :id',
            'member_activity_notification_sent WHERE member_id = :id',
        ];
        foreach ($removed as $from) {
            self::assertSame(0, $this->rows($from, ['id' => $memberId]), $from);
        }
        self::assertNull($connection->fetchOne('SELECT NewEmail FROM member WHERE id = ?', [$memberId]) ?: null);

        // the answer to the activity that is over stays without its comment, the one to come goes
        self::assertSame('', $connection->fetchOne('SELECT comment FROM activity_attendee WHERE activity_id = ? AND attendee_id = ?', [$past, $memberId]));
        self::assertSame(0, $this->rows('activity_attendee WHERE activity_id = ? AND attendee_id = ?', [$future, $memberId]));

        // the donation stays without who gave it
        $donation = $connection->fetchAssociative("SELECT donor_id, email, name_given, member_comment, amount FROM donation WHERE reference_paypal = 'zztestref'");
        self::assertSame([null, '', '', '', '10.00'], [$donation['donor_id'], $donation['email'], $donation['name_given'], $donation['member_comment'], $donation['amount']]);

        // the conversation with the retired member goes with its request, the other one stays
        self::assertSame(0, $this->rows('message WHERE id = ?', [$retiredConversation]));
        self::assertSame(0, $this->rows('request WHERE id = ?', [$request]));
        self::assertSame(1, $this->rows('message WHERE id = ?', [$activeConversation]));

        // the invited stop stays with its trip, without the trip's texts; the others go
        self::assertSame(1, $this->rows('leg WHERE id = ?', [$invitedLeg]));
        self::assertSame(0, $this->rows('leg WHERE id = ?', [$otherLeg]));
        self::assertSame(['', ''], array_values($connection->fetchAssociative('SELECT summary, description FROM trip WHERE id = ?', [$trip])));
        self::assertSame(0, $this->rows('trip WHERE id = ?', [$gone]));
    }

    /**
     * @return int the id of a message from $sender to $receiver, about the request if one is given
     */
    private function message(int $sender, int $receiver, ?int $request): int
    {
        $this->connection->executeStatement(
            "INSERT INTO message (sender_id, receiver_id, initiator_id, request_id, message, created, status, folder, spam_info)
             VALUES (?, ?, ?, ?, 'Zztest message', NOW(), 'Sent', 'Normal', 'NotSpam')",
            [$sender, $receiver, $sender, $request],
        );

        return (int) $this->connection->lastInsertId();
    }

    /**
     * @return int the id of a private note by $owner about $about
     */
    private function note(int $owner, int $about): int
    {
        $this->connection->executeStatement(
            "INSERT INTO mycontacts (IdMember, IdContact, Category, Comment, created, updated)
             VALUES (?, ?, 'zztest', 'Zztest note', NOW(), NOW())",
            [$owner, $about],
        );

        return (int) $this->connection->lastInsertId();
    }

    private function execute(string $command): string
    {
        static::getContainer()->get(EntityManagerInterface::class)->clear();
        $tester = new CommandTester(new Application(self::$kernel)->find($command));
        self::assertSame(0, $tester->execute([]), $tester->getDisplay());

        return $tester->getDisplay();
    }

    /**
     * @param array<int|string, mixed> $parameters
     */
    private function rows(string $from, array $parameters): int
    {
        return (int) $this->connection->fetchOne('SELECT COUNT(*) FROM ' . $from, $parameters);
    }

    private function removeComments(string $username, bool $received, bool $written): void
    {
        $id = (int) $this->member($username)['id'];
        if ($received) {
            $this->connection->executeStatement('DELETE FROM comment WHERE to_member_id = ?', [$id]);
        }
        if ($written) {
            $this->connection->executeStatement('DELETE FROM comment WHERE from_member_id = ?', [$id]);
        }
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
