<?php

namespace App\Tests\Model;

use App\Doctrine\DeleteRequestType;
use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Model\ConversationModel;
use App\Service\Mailer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConversationModelTest extends TestCase
{
    private $entityManager;
    private $mailer;
    private $translator;
    private $model;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mailer = $this->createMock(Mailer::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->model = new ConversationModel(
            $this->mailer,
            $this->entityManager,
            $this->translator
        );
    }

    public function testMarkConversationPurgedUpdatesMessages(): void
    {
        $receiver = new Member();
        $sender = new Member();
        $message = new Message();
        $message->setReceiver($receiver);
        $message->setSender($sender);
        $message->setDeleteRequest('');

        $conversation = [$message];

        // Act: Receiver purges conversation
        $this->model->markConversationPurged($receiver, $conversation);

        // Assert: Message state updated
        $this->assertEquals(DeleteRequestType::RECEIVER_PURGED, $message->getDeleteRequest());
        $this->assertEquals('Normal', $message->getFolder());
    }

    public function testMarkConversationDeletedUpdatesMessages(): void
    {
        $receiver = new Member();
        $sender = new Member();
        $message = new Message();
        $message->setReceiver($receiver);
        $message->setSender($sender);
        $message->setDeleteRequest('');

        $conversation = [$message];

        // Act: Receiver deletes conversation
        $this->model->markConversationDeleted($receiver, $conversation);

        // Assert: Message state updated
        $this->assertEquals(DeleteRequestType::RECEIVER_DELETED, $message->getDeleteRequest());
        $this->assertEquals('Normal', $message->getFolder());
    }

    public function testMarkConversationAsSpamUpdatesStatusAndInfo(): void
    {
        $receiver = new Member();
        $sender = new Member();
        $message = new Message();
        $message->setReceiver($receiver);
        $message->setFolder(InFolderType::NORMAL);
        $message->setStatus(MessageStatusType::SENT);

        $conversation = [$message];

        // Expect persistence
        $this->entityManager->expects($this->once())->method('persist')->with($message);
        $this->entityManager->expects($this->once())->method('flush');

        $this->model->markConversationAsSpam($receiver, $conversation, 'Spam comment');

        $this->assertEquals(InFolderType::SPAM, $message->getFolder());
        $this->assertEquals(MessageStatusType::CHECK, $message->getStatus());
        $this->assertEquals(SpamInfoType::MEMBER_SAYS_SPAM, $message->getSpamInfo());
        $this->assertEquals('Spam comment', $message->getCheckerComment());
    }

    public function testFormatConversationDetectsSpamPatterns(): void
    {
        $message = new Message();
        $message->setMessage('Contact me at test (at) example.com');
        $message->setStatus(MessageStatusType::SENT);
        $message->setFolder(InFolderType::NORMAL);

        $this->model->formatConversation($message);

        $this->assertEquals(InFolderType::SPAM, $message->getFolder());
        $this->assertEquals(MessageStatusType::CHECK, $message->getStatus());
        $this->assertEquals(SpamInfoType::SPAM_BLOCKED_WORD, $message->getSpamInfo());
    }

    public function testFormatConversationIgnoresCleanMessages(): void
    {
        $message = new Message();
        $message->setMessage('Hello, how are you?');
        $message->setStatus(MessageStatusType::SENT);
        $message->setFolder(InFolderType::NORMAL);

        $this->model->formatConversation($message);

        $this->assertEquals(InFolderType::NORMAL, $message->getFolder());
        $this->assertEquals(MessageStatusType::SENT, $message->getStatus());
    }

    public function testUnmarkConversationDeletedRestoresMessages(): void
    {
        $receiver = new Member();
        $sender = new Member();
        $message = new Message();
        $message->setReceiver($receiver);
        $message->setSender($sender);
        // Start as deleted
        $message->setDeleteRequest(DeleteRequestType::RECEIVER_DELETED);

        $conversation = [$message];

        $this->entityManager->expects($this->once())->method('persist')->with($message);
        $this->entityManager->expects($this->once())->method('flush');

        $this->model->unmarkConversationDeleted($receiver, $conversation);

        // Should replace RECEIVER_DELETED relative to empty string or existing state.
        $this->assertEquals('', $message->getDeleteRequest());
        $this->assertEquals('Normal', $message->getFolder());
    }

    public function testUnmarkConversationAsSpamRestoresNormalState(): void
    {
        $receiver = new Member();
        $message = new Message();
        $message->setReceiver($receiver);
        $message->setFolder(InFolderType::SPAM);
        $message->setStatus(MessageStatusType::CHECK);
        $message->setSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);

        $conversation = [$message];

        $this->entityManager->expects($this->once())->method('persist')->with($message);
        $this->entityManager->expects($this->once())->method('flush');

        $this->model->unmarkConversationAsSpam($receiver, $conversation);

        $this->assertEquals(InFolderType::NORMAL, $message->getFolder());
        $this->assertEquals(MessageStatusType::CHECKED, $message->getStatus());
        $this->assertEquals(SpamInfoType::NO_SPAM, $message->getSpamInfo());
    }

    public function testMarkConversationAsReadSetsTime(): void
    {
        $receiver = new Member();
        $message = new Message();
        $message->setReceiver($receiver);
        $message->setFirstRead(null); // Initialize to avoid type error

        $conversation = [$message];

        $this->entityManager->expects($this->once())->method('persist')->with($message);
        $this->entityManager->expects($this->once())->method('flush');

        $this->model->markConversationAsRead($receiver, $conversation);

        $this->assertNotNull($message->getFirstRead());
    }

    public function testGetLastMessageInConversationReturnsLatest(): void
    {
        $subject = new Subject();
        $parent = new Message();
        $parent->setSubject($subject);

        $msg1 = new Message();
        $msg2 = new Message();

        $repo = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $repo->method('findBy')->with(['subject' => $subject])->willReturn([$msg1, $msg2]);

        $this->entityManager->method('getRepository')->with(Message::class)->willReturn($repo);

        $result = $this->model->getLastMessageInConversation($parent);

        $this->assertSame($msg2, $result);
    }

    public function testHasMessageLimitExceededTrueWhenLimitReached(): void
    {
        // Mock DBAL for "hasLimitExceeded" private method logic
        $mockRow = [
            'numberOfComments' => 0, // < 1
            'numberOfMessagesLastHour' => 10,
            'numberOfMessagesLastDay' => 15,
        ];

        $this->setupLimitMock($mockRow);

        // Limit per hour: 5. We have 10. Should return true.
        $member = $this->createStub(Member::class);
        $member->method('getId')->willReturn(1);
        $this->assertTrue($this->model->hasMessageLimitExceeded($member, 5, 20));
    }

    public function testHasMessageLimitExceededFalseIfOneCommentExistEvenWhenLimitReached(): void
    {
        // Mock DBAL for "hasLimitExceeded" private method logic
        $mockRow = [
            'numberOfComments' => 1,
            'numberOfMessagesLastHour' => 10,
            'numberOfMessagesLastDay' => 15,
        ];

        $this->setupLimitMock($mockRow);

        // Limit per hour: 5. We have 10. Should return true.
        $member = $this->createStub(Member::class);
        $member->method('getId')->willReturn(1);
        $this->assertFalse($this->model->hasMessageLimitExceeded($member, 5, 20));
    }

    public function testHasMessageLimitExceededFalseWhenUnderLimit(): void
    {
        $mockRow = [
            'numberOfComments' => 0,
            'numberOfMessagesLastHour' => 2,
            'numberOfMessagesLastDay' => 5,
        ];

        $this->setupLimitMock($mockRow);

        // Limit per hour: 5. We have 2. Limit per day: 20. We have 5. -> False
        $member = $this->createStub(Member::class);
        $member->method('getId')->willReturn(1);
        $this->assertFalse($this->model->hasMessageLimitExceeded($member, 5, 20));
    }

    private function setupLimitMock(array $returnData): void
    {
        $result = $this->createStub(Result::class);
        $result->method('fetchAssociative')->willReturn($returnData);

        $stmt = $this->createStub(Statement::class);
        $stmt->method('executeQuery')->willReturn($result);

        $conn = $this->createStub(Connection::class);
        $conn->method('prepare')->willReturn($stmt);

        $this->entityManager->method('getConnection')->willReturn($conn);
    }
}
