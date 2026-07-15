<?php

namespace App\Tests\Model\Admin;

use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\BrowserPushNotification;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Model\Admin\CheckerModel;
use App\Service\BrowserNotificationService;
use App\Service\BrowserPushPreferenceService;
use App\Service\Mailer;
use App\Service\PushGatewayInterface;
use App\Tests\Service\BrowserPushTestConfig;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionProperty;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckerModelTest extends TestCase
{
    public function testApprovedBlockedMessageQueuesBrowserNotification(): void
    {
        $sender = $this->member(1, 'sender');
        $receiver = $this->member(2, 'receiver');
        $message = new Message()
            ->setSender($sender)
            ->setReceiver($receiver)
            ->setSubject(new Subject()->setSubject('Approved message'))
            ->setMessage('Message body')
            ->setStatus(MessageStatusType::SEND)
            ->setSpamInfo(SpamInfoType::SPAM_BLOCKED_WORD)
            ->setFolder(InFolderType::SPAM)
        ;
        $this->setId($message, 123);

        $messageRepository = $this->createStub(EntityRepository::class);
        $messageRepository->method('findBy')->willReturn([$message]);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->with(Message::class)->willReturn($messageRepository);
        $entityManager->expects(self::once())->method('persist')->with($message);
        $entityManager->expects(self::once())->method('flush');

        $queued = [];
        $notificationEntityManager = $this->createMock(EntityManagerInterface::class);
        $notificationEntityManager
            ->expects(self::once())
            ->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$queued): void {
                $queued[] = $entity;
            })
        ;
        $notificationEntityManager->expects(self::once())->method('flush');

        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects(self::once())
            ->method('sendMessageNotificationEmail')
            ->with($sender, $receiver, 'message', self::isArray())
            ->willReturn(true)
        ;
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/conversation/123');

        $model = new CheckerModel(
            $entityManager,
            $mailer,
            new BrowserNotificationService(
                $notificationEntityManager,
                BrowserPushTestConfig::create(),
                $this->createStub(PushGatewayInterface::class),
                $this->createStub(TranslatorInterface::class),
                new NullLogger(),
                $this->openOnlyPreferenceService(),
            ),
            $urlGenerator,
        );

        $model->unmarkAsSpamByChecker([$message->getId()]);

        self::assertSame(MessageStatusType::CHECKED, $message->getStatus());
        self::assertSame(InFolderType::NORMAL, $message->getFolder());
        self::assertCount(1, $queued);
        self::assertInstanceOf(BrowserPushNotification::class, $queued[0]);
        self::assertSame($receiver, $queued[0]->getReceiver());
        self::assertSame('message', $queued[0]->getType());
        self::assertSame('sender', $queued[0]->getSenderUsername());
        self::assertSame('/conversation/123', $queued[0]->getUrl());
        self::assertSame(BrowserPushNotification::STATUS_OPEN_ONLY, $queued[0]->getStatus());
    }

    private function openOnlyPreferenceService(): BrowserPushPreferenceService
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('fetchAssociative')->willReturn([
            'id' => 1,
            'DefaultValue' => BrowserPushPreferenceService::VALUE_NO,
        ]);
        $connection->method('fetchOne')->willReturn(BrowserPushPreferenceService::VALUE_OPEN_ONLY);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);

        return new BrowserPushPreferenceService($entityManager);
    }

    private function member(int $id, string $username): Member
    {
        $member = new Member();
        $member->setUsername($username);
        $this->setId($member, $id);

        return $member;
    }

    private function setId(object $entity, int $id): void
    {
        $property = new ReflectionProperty($entity, 'id');
        $property->setValue($entity, $id);
    }
}
