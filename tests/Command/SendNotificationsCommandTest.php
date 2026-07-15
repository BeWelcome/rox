<?php

namespace App\Tests\Command;

use App\Command\SendNotificationsCommand;
use App\Doctrine\MemberStatusType;
use App\Doctrine\NotificationStatusType;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\PostNotification;
use App\Repository\PostNotificationRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionProperty;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Translation\Translator;

class SendNotificationsCommandTest extends TestCase
{
    public function testFirstThreadedNotificationDoesNotReferenceLegacyRows(): void
    {
        [$notification] = $this->createNotifications();
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

        $tester = $this->createCommandTester([$notification], [], $mailer);

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertSame([], $parameters['previousMessageIds']);
        self::assertSame('forum-notification-501@bewelcome.org', $parameters['messageId']);
        self::assertSame('forum-notification-501@bewelcome.org', $notification->getMessageId());
    }

    public function testSuccessfulNotificationBecomesParentOfNextMessage(): void
    {
        [$first, $second] = $this->createNotifications();
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

        $tester = $this->createCommandTester(
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
        [$first, $second] = $this->createNotifications();
        $parameters = [];
        $results = [false, true];
        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects($this->exactly(2))
            ->method('sendNotificationEmail')
            ->willReturnCallback(static function ($sender, $receiver, array $messageParameters) use (&$parameters, &$results): bool {
                $parameters[] = $messageParameters;

                return array_shift($results);
            })
        ;

        $tester = $this->createCommandTester(
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

    private function createCommandTester(
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

        $command = new SendNotificationsCommand(
            $entityManager,
            $this->createStub(LoggerInterface::class),
            $mailer,
            100,
        );
        $command->setTranslator(new Translator('en'));

        return new CommandTester($command);
    }

    /**
     * @return list<PostNotification>
     */
    private function createNotifications(): array
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
            $this->createNotification(501, $receiver, $post),
            $this->createNotification(502, $receiver, $post),
        ];
    }

    private function createNotification(int $id, Member $receiver, ForumPost $post): PostNotification
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
}
