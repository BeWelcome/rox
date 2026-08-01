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
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConversationModelTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private ConversationModel $model;

    protected function setUp(): void
    {
        $this->entityManager = $this->createStub(EntityManagerInterface::class);
        $this->model = $this->createModel($this->entityManager);
    }

    public function testMarkConversationPurgedUpdatesMessages(): void
    {
        $receiver = new Member();
        $sender = new Member();
        $message = new Message();
        $message->setReceiver($receiver);
        $message->setSender($sender);
        $message->setDeleteRequest('');
        $message->setFolder(InFolderType::SPAM);
        $this->expectPersistAndFlush($message);

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
        $message->setFolder(InFolderType::SPAM);
        $this->expectPersistAndFlush($message);

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
        $message = new Message();
        $message->setReceiver($receiver);
        $message->setFolder(InFolderType::NORMAL);
        $message->setStatus(MessageStatusType::SENT);
        $this->expectPersistAndFlush($message);

        $conversation = [$message];

        $this->model->markConversationAsSpam($receiver, $conversation, 'Spam comment');

        $this->assertEquals(InFolderType::SPAM, $message->getFolder());
        $this->assertEquals(MessageStatusType::CHECK, $message->getStatus());
        $this->assertEquals(SpamInfoType::MEMBER_SAYS_SPAM, $message->getSpamInfo());
        $this->assertEquals('Spam comment', $message->getCheckerComment());
    }

    public function testFormatConversationDetectsSpamPatterns(): void
    {
        $message = new Message();
        $message->setMessage('Contact me at test (AT) example.com');
        $message->setStatus(MessageStatusType::SENT);
        $message->setFolder(InFolderType::NORMAL);

        $this->model->formatConversation($message);

        $this->assertEquals(InFolderType::SPAM, $message->getFolder());
        $this->assertEquals(MessageStatusType::CHECK, $message->getStatus());
        $this->assertEquals(SpamInfoType::SPAM_BLOCKED_WORD, $message->getSpamInfo());
    }

    public function testFormatConversationDetectsUppercaseObfuscatedAt(): void
    {
        $message = new Message();
        $message->setMessage('TEST (AT) EXAMPLE.COM');
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
        $message->setFolder(InFolderType::SPAM);
        $this->expectPersistAndFlush($message);

        $conversation = [$message];

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
        $this->expectPersistAndFlush($message);

        $conversation = [$message];

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
        $senderMessage = new Message();
        $senderMessage->setReceiver(new Member());
        $senderMessage->setSender($receiver);
        $senderMessage->setFirstRead(null);
        $alreadyReadAt = new DateTime('2026-06-28 12:00:00');
        $alreadyReadMessage = new Message();
        $alreadyReadMessage->setReceiver($receiver);
        $alreadyReadMessage->setFirstRead($alreadyReadAt);
        $this->expectPersistAndFlush($message);

        $conversation = [$message, $senderMessage, $alreadyReadMessage];

        $this->model->markConversationAsRead($receiver, $conversation);

        $this->assertNotNull($message->getFirstRead());
        $this->assertNull($senderMessage->getFirstRead());
        $this->assertSame($alreadyReadAt->getTimestamp(), $alreadyReadMessage->getFirstRead()->getTimestamp());
    }

    public function testGetLastMessageInConversationReturnsLatest(): void
    {
        $subject = new Subject();
        $parent = new Message();
        $parent->setSubject($subject);

        $msg1 = new Message();
        $msg2 = new Message();

        $repo = $this->createMock(EntityRepository::class);
        $repo->expects($this->once())->method('findBy')->with(['subject' => $subject])->willReturn([$msg1, $msg2]);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager->expects($this->once())->method('getRepository')->with(Message::class)->willReturn($repo);
        $this->model = $this->createModel($this->entityManager);

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

    public function testIsMarkedCorrectly(): void
    {
        $message = new Message();
        $message->setMessage('Hello, how are you?');
        $this->assertTrue(SpamInfoType::NO_SPAM === $this->model->formatConversation($message)->getSpamInfo());

        $message = new Message();
        $message->setMessage('bewelcome.919823.shop');
        $this->assertTrue(SpamInfoType::SPAM_BLOCKED_WORD === $this->model->formatConversation($message)->getSpamInfo());

        $message = new Message();
        $message->setMessage('This is verification scam');
        $this->assertTrue(SpamInfoType::SPAM_BLOCKED_WORD === $this->model->formatConversation($message)->getSpamInfo());

        $message = new Message();
        $message->setMessage(<<<MSG
            Hello\u{200b}!\u{200b}

            F\u{200b}o\u{200b}r\u{200b} \u{200b}s\u{200b}e\u{200b}c\u{200b}u\u{200b}r\u{200b}i\u{200b}t\u{200b}y\u{200b} \u{200b}w\u{200b}e\u{200b} \u{200b}n\u{200b}e\u{200b}e\u{200b}d\u{200b} \u{200b}a\u{200b} \u{200b}q\u{200b}u\u{200b}i\u{200b}c\u{200b}k\u{200b} \u{200b}I\u{200b}D\u{200b} \u{200b}c\u{200b}h\u{200b}e\u{200b}c\u{200b}k\u{200b}.\u{200b}

            W\u{200b}i\u{200b}t\u{200b}h\u{200b}o\u{200b}u\u{200b}t\u{200b} \u{200b}i\u{200b}t\u{200b} \u{200b}y\u{200b}o\u{200b}u\u{200b}r\u{200b} \u{200b}B\u{200b}e\u{200b}W\u{200b}e\u{200b}l\u{200b}c\u{200b}o\u{200b}m\u{200b}e\u{200b} \u{200b}a\u{200b}c\u{200b}c\u{200b}o\u{200b}u\u{200b}n\u{200b}t\u{200b} \u{200b}w\u{200b}i\u{200b}l\u{200b}l\u{200b} \u{200b}b\u{200b}e\u{200b} \u{200b}s\u{200b}u\u{200b}s\u{200b}p\u{200b}e\u{200b}n\u{200b}d\u{200b}e\u{200b}d\u{200b}.\u{200b}

            V\u{200b}e\u{200b}r\u{200b}i\u{200b}f\u{200b}y\u{200b}\u{200b}h\u{200b}e\u{200b}r\u{200b}e\u{200b}:\u{200b}
            https://tr.ee/DC82PQI\u{200b}f\u{200b} \u{200b}y\u{200b}o\u{200b}u\u{200b} \u{200b}c\u{200b}a\u{200b}n\u{200b}'t\u{200b} \u{200b}c\u{200b}l\u{200b}i\u{200b}c\u{200b}k\u{200b} \u{200b}o\u{200b}n\u{200b} \u{200b}t\u{200b}h\u{200b}e\u{200b} \u{200b}l\u{200b}i\u{200b}n\u{200b}k\u{200b},\u{200b} \u{200b}c\u{200b}o\u{200b}p\u{200b}y\u{200b} \u{200b}a\u{200b}n\u{200b}d\u{200b} \u{200b}p\u{200b}a\u{200b}s\u{200b}t\u{200b}e\u{200b} \u{200b}i\u{200b}t\u{200b} \u{200b}i\u{200b}n\u{200b}t\u{200b}o\u{200b} \u{200b}y\u{200b}o\u{200b}u\u{200b}r\u{200b} \u{200b}b\u{200b}r\u{200b}o\u{200b}w\u{200b}s\u{200b}e\u{200b}r\u{200b} \u{200b}(S\u{200b}a\u{200b}f\u{200b}a\u{200b}r\u{200b}i\u{200b}/C\u{200b}h\u{200b}r\u{200b}o\u{200b}m\u{200b}e\u{200b},\u{200b} \u{200b}e\u{200b}t\u{200b}c\u{200b}.\u{200b}).")
            MSG);
        $this->assertTrue(SpamInfoType::SPAM_BLOCKED_WORD === $this->model->formatConversation($message)->getSpamInfo());

        $message = new Message();
        $message->setMessage(<<<MSG
            Always good to meet travelers people :) 

            Hello! 

            We are a couple currently on a road trip from the South of France to the European Juggling Convention in Slovenia! starting on August 1st. 

             We’re always looking for ways to learn and teach, meet good people, and be in a pleasant environment close to nature. \u{200b}

            We would love to come stay with you,  If you’re spontaneous and it's feel good.  

            We’re not afraid to try new things, so we’ve learned to do a wide variety of things. 

            And we have a tent! :)

            It's the first time for us in bewelcome, but we are many years in trustroots and workaway. 

            It will be amazing for us to rest and stay 1 or 2 nights before the convention(30,31- or just one even).

            We like cooking and baking sourdough brade, we travel with one ;)

             \u{200b}Thanks in advance. Hope to get to know you  

             Guy and Lirane :)
            MSG
        );

        $message = new Message();
        $message->setMessage(<<<'MSG'
            Hi from Poland ! 

            Hi!

            I'm a solo traveller from Poland :P

            I have expirence in solo travelling, already have been in Helsinki, Berlin, Lithuania, London and have to say that it is great opportunity to feel power and meet amazing people, have adventure and believe in myself 🙃 

            I'm 30 years old system engineer still trying not to be so adult and sometimes travelling only with my backpack. 

            I'm going to be in Emilia-Romagna on 8.08-10.08. 

            My plan is in progress.. But I what I know for now is to see San Marino, buy 3days train pass and travel from city to city on east side of Emilia-Romagn

            Maybe could you join me at least to show me your city and spend some time? ;)  

            I can help in your daily activities and share my food culture. 🙆‍♀️

            Let me know also if you would like to be my host even for a part of these days. if you want to see my reccomedations please check my profile.

            Cheers! 

            Jagoda
            MSG
        );
        $this->assertTrue(SpamInfoType::NO_SPAM === $this->model->formatConversation($message)->getSpamInfo());
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

    private function expectPersistAndFlush(Message $message): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager->expects($this->once())->method('persist')->with($message);
        $this->entityManager->expects($this->once())->method('flush');
        $this->model = $this->createModel($this->entityManager);
    }

    private function createModel(EntityManagerInterface $entityManager): ConversationModel
    {
        return new ConversationModel(
            $this->createStub(Mailer::class),
            $entityManager,
            $this->createStub(TranslatorInterface::class)
        );
    }
}
