<?php

namespace App\Tests\Model;

use App\Doctrine\CommentQualityType;
use App\Doctrine\MessageStatusType;
use App\Entity\Comment;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Model\CommentModel;
use App\Model\ConversationModel;
use DateTime;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The limits for new members (no positive comment yet) and the check against repeated comments, on the tables of
 * the mapping (comment, messages), not the legacy comments table.
 */
#[Group('integration')]
class SendingLimitsTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ConversationModel $conversationModel;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->conversationModel = static::getContainer()->get(ConversationModel::class);
    }

    public function testANewMemberMaySendOnlyAFewMessagesPerHour(): void
    {
        [$newMember, $other] = $this->newMemberAndOther();

        $this->send($newMember, $other, 4);
        self::assertFalse($this->conversationModel->hasMessageLimitExceeded($newMember, 5, 10));

        $this->send($newMember, $other, 1);
        self::assertTrue($this->conversationModel->hasMessageLimitExceeded($newMember, 5, 10));
    }

    public function testMessagesOfTheLastDayCountForTheDailyLimit(): void
    {
        [$newMember, $other] = $this->newMemberAndOther();

        $this->send($newMember, $other, 10, sentAt: new DateTime('-3 hours'));

        self::assertTrue($this->conversationModel->hasMessageLimitExceeded($newMember, 5, 10));
        self::assertFalse($this->conversationModel->hasMessageLimitExceeded($newMember, 5, 11));
    }

    public function testRequestsAndMessagesHaveTheirOwnLimits(): void
    {
        [$newMember, $other] = $this->newMemberAndOther();

        $this->send($newMember, $other, 5, request: true);

        self::assertTrue($this->conversationModel->hasRequestLimitExceeded($newMember, 5, 10));
        self::assertFalse($this->conversationModel->hasMessageLimitExceeded($newMember, 5, 10));
    }

    public function testAMemberWithAPositiveCommentHasNoLimit(): void
    {
        [$newMember, $other] = $this->newMemberAndOther();
        $this->send($newMember, $other, 6);
        $this->send($newMember, $other, 6, request: true);

        $this->comment($other, $newMember, 'Lovely guest.', CommentQualityType::POSITIVE);

        self::assertFalse($this->conversationModel->hasMessageLimitExceeded($newMember, 5, 10));
        self::assertFalse($this->conversationModel->hasRequestLimitExceeded($newMember, 5, 10));
    }

    public function testTheSameCommentAgainRightAwayIsRefused(): void
    {
        [$author, $other] = $this->newMemberAndOther();
        $this->comment($author, $other, 'Great host, I would stay there again any time!');

        self::assertTrue($this->commentModel()->checkCommentSpam($author, $this->draft('Great host, I would stay there again any time!')));
    }

    public function testADifferentCommentRightAwayIsFine(): void
    {
        [$author, $other] = $this->newMemberAndOther();
        $this->comment($author, $other, 'Great host, I would stay there again any time!');

        self::assertFalse($this->commentModel()->checkCommentSpam($author, $this->draft('We met at the meetup in Paris, a nice evening.')));
    }

    public function testTheSameCommentLaterIsFine(): void
    {
        [$author, $other] = $this->newMemberAndOther();
        $written = $this->comment($author, $other, 'Great host, I would stay there again any time!');
        $this->connection()->executeStatement(
            'UPDATE comment SET created = ? WHERE id = ?',
            [new DateTime('-7 hours')->format('Y-m-d H:i:s'), $written->getId()]
        );

        self::assertFalse($this->commentModel()->checkCommentSpam($author, $this->draft('Great host, I would stay there again any time!')));
    }

    /**
     * @return array{Member, Member} an active member without a positive comment and another member
     */
    private function newMemberAndOther(): array
    {
        $ids = $this->connection()->fetchFirstColumn(
            "SELECT m.id FROM member m WHERE m.Status = 'Active'
             AND NOT EXISTS (SELECT 1 FROM comment c WHERE c.to_member_id = m.id AND c.quality = 'positive')
             ORDER BY m.id LIMIT 2"
        );
        self::assertCount(2, $ids, 'the fixtures need two active members without a positive comment');
        // only what the test sends counts (the fixture messages reply to each other, so they stay, but long ago)
        $this->connection()->executeStatement("UPDATE message SET created = '2000-01-01' WHERE sender_id IN (?, ?)", $ids);
        $this->connection()->executeStatement('DELETE FROM comment WHERE from_member_id IN (?, ?)', $ids);
        $members = $this->entityManager->getRepository(Member::class);

        return [$members->find($ids[0]), $members->find($ids[1])];
    }

    private function send(Member $sender, Member $receiver, int $count, ?DateTime $sentAt = null, bool $request = false): void
    {
        $messages = [];
        for ($i = 0; $i < $count; ++$i) {
            $message = $messages[] = new Message()
                ->setSender($sender)
                ->setReceiver($receiver)
                ->setMessage('Hello ' . $i)
                ->setStatus(MessageStatusType::SENT)
                ->setDeleteRequest('');
            if ($request) {
                $hostingRequest = new HostingRequest()
                    ->setArrival(new DateTime('+10 days'))
                    ->setDeparture(new DateTime('+12 days'));
                $this->entityManager->persist($hostingRequest);
                $message->setRequest($hostingRequest);
            }
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
        if (null !== $sentAt) {
            // saving sets the time sent to now
            $this->connection()->executeStatement(
                'UPDATE message SET created = ? WHERE id IN (?)',
                [$sentAt->format('Y-m-d H:i:s'), array_map(static fn (Message $sent): int => $sent->getId(), $messages)],
                [ParameterType::STRING, ArrayParameterType::INTEGER]
            );
        }
    }

    private function comment(Member $from, Member $to, string $text, string $quality = CommentQualityType::NEUTRAL): Comment
    {
        $comment = $this->draft($text)->setFromMember($from)->setToMember($to)->setQuality($quality);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return $comment;
    }

    private function draft(string $text): Comment
    {
        return new Comment()->setComment($text);
    }

    private function commentModel(): CommentModel
    {
        return static::getContainer()->get(CommentModel::class);
    }

    private function connection(): Connection
    {
        return static::getContainer()->get(Connection::class);
    }
}
