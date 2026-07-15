<?php

namespace App\Tests\Command;

use App\Command\SendAndPushNotificationsCommand;
use App\Doctrine\MemberStatusType;
use App\Doctrine\NotificationStatusType;
use App\Entity\BrowserPushNotification;
use App\Entity\BrowserPushSubscription;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\PostNotification;
use App\Repository\PostNotificationRepository;
use App\Service\BrowserNotificationService;
use App\Service\BrowserPushConfig;
use App\Service\BrowserPushNotificationProcessor;
use App\Service\BrowserPushPreferenceService;
use App\Service\Mailer;
use App\Service\PushGatewayInterface;
use App\Tests\Service\BrowserPushTestConfig;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionProperty;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class SendAndPushNotificationsCommandTest extends TestCase
{
    private int $nextMemberId = 1;

    public function testProcessesBrowserPushQueueWhenForumQueueIsEmpty(): void
    {
        $postNotificationRepository = $this->createMock(PostNotificationRepository::class);
        $postNotificationRepository
            ->expects($this->once())
            ->method('getScheduledNotifications')
            ->with(10)
            ->willReturn([])
        ;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(PostNotification::class)
            ->willReturn($postNotificationRepository)
        ;
        $browserPushNotificationProcessor = $this->createMock(BrowserPushNotificationProcessor::class);
        $browserPushNotificationProcessor
            ->expects($this->once())
            ->method('process')
            ->with(10)
        ;

        $command = new SendAndPushNotificationsCommand(
            $entityManager,
            $this->createStub(LoggerInterface::class),
            $this->createStub(Mailer::class),
            new BrowserNotificationService(
                $entityManager,
                new BrowserPushConfig('', '', ''),
                $this->createStub(PushGatewayInterface::class),
                $this->createStub(TranslatorInterface::class),
                new NullLogger(),
                $this->preferenceService()
            ),
            $this->createStub(UrlGeneratorInterface::class),
            $browserPushNotificationProcessor,
            10
        );
        $tester = new CommandTester($command);

        self::assertSame(0, $tester->execute([]));
    }

    public function testQueuesForumBrowserPushNotificationWhenEmailFails(): void
    {
        $postNotification = $this->forumPostNotification();
        $postNotificationRepository = $this->createMock(PostNotificationRepository::class);
        $postNotificationRepository
            ->expects($this->once())
            ->method('getScheduledNotifications')
            ->with(10)
            ->willReturn([$postNotification])
        ;
        $postNotificationRepository
            ->expects($this->once())
            ->method('getSentMessageIds')
            ->with($postNotification->getReceiver(), $postNotification->getPost()->getThread())
            ->willReturn([])
        ;
        $subscription = new BrowserPushSubscription()
            ->setMember($postNotification->getReceiver())
            ->setEndpoint('https://example.com/push')
            ->setPublicKey('public-key')
            ->setAuthToken('auth-token')
        ;
        $subscriptionRepository = $this->createMock(EntityRepository::class);
        $subscriptionRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['member' => $postNotification->getReceiver()])
            ->willReturn([$subscription])
        ;
        $queuedNotifications = [];
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturnMap([
            [PostNotification::class, $postNotificationRepository],
            [BrowserPushSubscription::class, $subscriptionRepository],
        ]);
        $entityManager
            ->expects($this->exactly(3))
            ->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$queuedNotifications): void {
                if ($entity instanceof BrowserPushNotification) {
                    $queuedNotifications[] = $entity;
                }
            })
        ;
        $entityManager->expects($this->exactly(2))->method('flush');
        $browserPushNotificationProcessor = $this->createMock(BrowserPushNotificationProcessor::class);
        $browserPushNotificationProcessor
            ->expects($this->once())
            ->method('process')
            ->with(10)
        ;
        $mailer = $this->createMock(Mailer::class);
        $mailer->expects($this->once())->method('sendNotificationEmail')->willReturn(false);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with('forum_thread', ['threadId' => 456, '_fragment' => 'post123'])
            ->willReturn('/forums/s456#post123')
        ;

        $command = new SendAndPushNotificationsCommand(
            $entityManager,
            $this->createStub(LoggerInterface::class),
            $mailer,
            new BrowserNotificationService(
                $entityManager,
                BrowserPushTestConfig::create(),
                $this->createStub(PushGatewayInterface::class),
                $this->createStub(TranslatorInterface::class),
                new NullLogger(),
                $this->preferenceService()
            ),
            $urlGenerator,
            $browserPushNotificationProcessor,
            10
        );
        $command->setTranslator(new Translator('en'));
        $tester = new CommandTester($command);

        self::assertSame(0, $tester->execute([]));
        self::assertSame(NotificationStatusType::FROZEN, $postNotification->getStatus());
        self::assertNull($postNotification->getMessageId());
        self::assertCount(1, $queuedNotifications);
        self::assertSame($postNotification->getReceiver(), $queuedNotifications[0]->getReceiver());
        self::assertSame('forum', $queuedNotifications[0]->getType());
        self::assertSame('author', $queuedNotifications[0]->getSenderUsername());
        self::assertSame('/forums/s456#post123', $queuedNotifications[0]->getUrl());
    }

    public function testBrowserPushQueueFailureDoesNotFailForumNotificationCommand(): void
    {
        $postNotificationRepository = $this->createMock(PostNotificationRepository::class);
        $postNotificationRepository
            ->expects($this->once())
            ->method('getScheduledNotifications')
            ->with(10)
            ->willReturn([])
        ;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(PostNotification::class)
            ->willReturn($postNotificationRepository)
        ;
        $browserPushNotificationProcessor = $this->createMock(BrowserPushNotificationProcessor::class);
        $browserPushNotificationProcessor
            ->expects($this->once())
            ->method('process')
            ->willThrowException(new RuntimeException('Queue unavailable.'))
        ;

        $command = new SendAndPushNotificationsCommand(
            $entityManager,
            $this->createStub(LoggerInterface::class),
            $this->createStub(Mailer::class),
            new BrowserNotificationService(
                $entityManager,
                new BrowserPushConfig('', '', ''),
                $this->createStub(PushGatewayInterface::class),
                $this->createStub(TranslatorInterface::class),
                new NullLogger(),
                $this->preferenceService()
            ),
            $this->createStub(UrlGeneratorInterface::class),
            $browserPushNotificationProcessor,
            10
        );
        $tester = new CommandTester($command);

        self::assertSame(0, $tester->execute([]));
    }

    public function testFirstThreadedNotificationDoesNotReferenceLegacyRows(): void
    {
        [$notification] = $this->createThreadedNotifications();
        $parameters = [];
        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects($this->once())
            ->method('sendNotificationEmail')
            ->willReturnCallback(static function ($sender, $receiver, array $messageParameters) use (&$parameters): bool {
                $parameters = $messageParameters;

                return true;
            })
        ;

        $tester = $this->createThreadingCommandTester([$notification], [], $mailer);

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertSame([], $parameters['previousMessageIds']);
        self::assertSame('forum-notification-501@bewelcome.org', $parameters['messageId']);
        self::assertSame('forum-notification-501@bewelcome.org', $notification->getMessageId());
    }

    public function testSuccessfulNotificationBecomesParentOfNextMessage(): void
    {
        [$first, $second] = $this->createThreadedNotifications();
        $parameters = [];
        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects($this->exactly(2))
            ->method('sendNotificationEmail')
            ->willReturnCallback(static function ($sender, $receiver, array $messageParameters) use (&$parameters): bool {
                $parameters[] = $messageParameters;

                return true;
            })
        ;

        $tester = $this->createThreadingCommandTester(
            [$first, $second],
            ['forum-notification-400@bewelcome.org'],
            $mailer,
        );

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertSame(
            ['forum-notification-400@bewelcome.org'],
            $parameters[0]['previousMessageIds'],
        );
        self::assertSame(
            ['forum-notification-400@bewelcome.org', 'forum-notification-501@bewelcome.org'],
            $parameters[1]['previousMessageIds'],
        );
        self::assertSame('forum-notification-501@bewelcome.org', $first->getMessageId());
        self::assertSame('forum-notification-502@bewelcome.org', $second->getMessageId());
        self::assertSame(NotificationStatusType::SENT, $first->getStatus());
        self::assertSame(NotificationStatusType::SENT, $second->getStatus());
    }

    public function testFailedNotificationIsNotReferencedByNextMessage(): void
    {
        [$first, $second] = $this->createThreadedNotifications();
        $parameters = [];
        $results = [false, true];
        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects($this->exactly(2))
            ->method('sendNotificationEmail')
            ->willReturnCallback(
                static function ($sender, $receiver, array $messageParameters) use (&$parameters, &$results): bool {
                    $parameters[] = $messageParameters;

                    return array_shift($results);
                }
            )
        ;

        $tester = $this->createThreadingCommandTester(
            [$first, $second],
            ['forum-notification-400@bewelcome.org'],
            $mailer,
        );

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertSame(
            ['forum-notification-400@bewelcome.org'],
            $parameters[0]['previousMessageIds'],
        );
        self::assertSame(
            ['forum-notification-400@bewelcome.org'],
            $parameters[1]['previousMessageIds'],
        );
        self::assertNull($first->getMessageId());
        self::assertSame('forum-notification-502@bewelcome.org', $second->getMessageId());
        self::assertSame(NotificationStatusType::FROZEN, $first->getStatus());
        self::assertSame(NotificationStatusType::SENT, $second->getStatus());
        self::assertStringContainsString('Sent 1 messages, skipped 1 messages', $tester->getDisplay());
    }

    private function createThreadingCommandTester(
        array $notifications,
        array $sentMessageIds,
        Mailer $mailer,
    ): CommandTester {
        $repository = $this->createMock(PostNotificationRepository::class);
        $repository
            ->expects($this->once())
            ->method('getScheduledNotifications')
            ->willReturn($notifications)
        ;
        $repository
            ->expects($this->once())
            ->method('getSentMessageIds')
            ->willReturn($sentMessageIds)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(PostNotification::class)
            ->willReturn($repository)
        ;
        $entityManager->expects($this->exactly(\count($notifications)))->method('persist');
        $entityManager->expects($this->once())->method('flush');
        $browserPushNotificationProcessor = $this->createMock(BrowserPushNotificationProcessor::class);
        $browserPushNotificationProcessor
            ->expects($this->once())
            ->method('process')
            ->with(100)
        ;

        $command = new SendAndPushNotificationsCommand(
            $entityManager,
            $this->createStub(LoggerInterface::class),
            $mailer,
            new BrowserNotificationService(
                $entityManager,
                new BrowserPushConfig('', '', ''),
                $this->createStub(PushGatewayInterface::class),
                $this->createStub(TranslatorInterface::class),
                new NullLogger(),
                $this->preferenceService()
            ),
            $this->createStub(UrlGeneratorInterface::class),
            $browserPushNotificationProcessor,
            100,
        );
        $command->setTranslator(new Translator('en'));

        return new CommandTester($command);
    }

    /**
     * @return list<PostNotification>
     */
    private function createThreadedNotifications(): array
    {
        $language = new Language()->setShortCode('en');
        $receiver = new Member()
            ->setId(7)
            ->setUsername('receiver')
            ->setEmail('receiver@example.org')
            ->setStatus(MemberStatusType::ACTIVE)
        ;
        new ReflectionProperty($receiver, 'preferredLanguage')->setValue($receiver, $language);
        $author = new Member()->setId(8)->setUsername('author');
        $thread = new ForumThread()->setId(42)->setTitle('Forum topic');
        $post = new ForumPost()
            ->setId(101)
            ->setThread($thread)
            ->setAuthor($author)
        ;

        return [
            $this->createThreadedNotification(501, $receiver, $post),
            $this->createThreadedNotification(502, $receiver, $post),
        ];
    }

    private function createThreadedNotification(int $id, Member $receiver, ForumPost $post): PostNotification
    {
        $notification = new PostNotification()
            ->setReceiver($receiver)
            ->setPost($post)
            ->setType('reply')
        ;
        $notification->onPrePersist();
        new ReflectionProperty($notification, 'id')->setValue($notification, $id);

        return $notification;
    }

    private function forumPostNotification(): PostNotification
    {
        $receiver = $this->member('receiver');
        $author = $this->member('author');
        $post = new ForumPost()
            ->setId(123)
            ->setAuthor($author)
        ;
        $thread = new ForumThread()
            ->setId(456)
            ->setTitle('Forum thread')
            ->setGroup(null)
        ;
        $post->setThread($thread);
        $postNotification = new PostNotification()
            ->setReceiver($receiver)
            ->setPost($post)
            ->setType('reply')
            ->setTableSubscription('members_threads_subscribed')
        ;
        $this->setPrivateProperty($postNotification, 'id', 789);
        $this->setPrivateProperty($postNotification, 'created', new DateTime());

        return $postNotification;
    }

    private function member(string $username): Member
    {
        $language = new Language()
            ->setShortCode('en')
            ->setName('English')
        ;
        $member = new Member()
            ->setUsername($username)
            ->setStatus(MemberStatusType::ACTIVE)
            ->setLocale('en')
        ;
        $this->setPrivateProperty($member, 'id', $this->nextMemberId++);
        $this->setPrivateProperty($member, 'preferredLanguage', $language);

        return $member;
    }

    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflectionProperty = new ReflectionProperty($object, $property);
        $reflectionProperty->setValue($object, $value);
    }

    private function preferenceService(): BrowserPushPreferenceService
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('fetchAssociative')->willReturn([
            'id' => 1,
            'DefaultValue' => BrowserPushPreferenceService::VALUE_ALWAYS,
        ]);
        $connection->method('fetchOne')->willReturn(BrowserPushPreferenceService::VALUE_ALWAYS);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);

        return new BrowserPushPreferenceService($entityManager);
    }
}
