<?php

namespace App\Tests\Service;

use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Member;
use App\Entity\PostNotification;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;

class MailerTest extends TestCase
{
    public function testFirstNotificationStartsThread(): void
    {
        $notification = $this->createNotification('newthread');
        $transport = $this->createMock(MailerInterface::class);
        $transport
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(static function (RawMessage $message): bool {
                self::assertInstanceOf(TemplatedEmail::class, $message);
                self::assertSame(
                    '<forum-notification-501@bewelcome.org>',
                    $message->getHeaders()->get('Message-ID')?->getBodyAsString()
                );
                self::assertNull($message->getHeaders()->get('In-Reply-To'));
                self::assertNull($message->getHeaders()->get('References'));

                return true;
            }))
        ;

        $this->assertTrue($this->createMailer($transport)->sendNotificationEmail(
            new Address('forum@bewelcome.org'),
            $notification->getReceiver(),
            [
                'subject' => 'Forum topic',
                'notification' => $notification,
                'messageId' => 'forum-notification-501@bewelcome.org',
            ]
        ));
    }

    public function testReplyNotificationReferencesPreviousMessage(): void
    {
        $notification = $this->createNotification('reply');
        $transport = $this->createMock(MailerInterface::class);
        $transport
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(static function (RawMessage $message): bool {
                self::assertInstanceOf(TemplatedEmail::class, $message);
                self::assertSame(
                    '<forum-notification-501@bewelcome.org>',
                    $message->getHeaders()->get('Message-ID')?->getBodyAsString()
                );
                self::assertSame(
                    '<forum-notification-500@bewelcome.org>',
                    $message->getHeaders()->get('In-Reply-To')?->getBodyAsString()
                );
                self::assertSame(
                    '<forum-notification-500@bewelcome.org>',
                    $message->getHeaders()->get('References')?->getBodyAsString()
                );

                return true;
            }))
        ;

        $this->assertTrue($this->createMailer($transport)->sendNotificationEmail(
            new Address('forum@bewelcome.org'),
            $notification->getReceiver(),
            [
                'subject' => 'Re: Forum topic',
                'notification' => $notification,
                'messageId' => 'forum-notification-501@bewelcome.org',
                'previousMessageIds' => ['forum-notification-500@bewelcome.org'],
            ]
        ));
    }

    public function testLaterNotificationIncludesFullReferenceChain(): void
    {
        $notification = $this->createNotification('reply', 503);
        $transport = $this->createMock(MailerInterface::class);
        $transport
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(static function (RawMessage $message): bool {
                self::assertInstanceOf(TemplatedEmail::class, $message);
                self::assertSame(
                    '<forum-notification-503@bewelcome.org>',
                    $message->getHeaders()->get('Message-ID')?->getBodyAsString()
                );
                self::assertSame(
                    '<forum-notification-502@bewelcome.org>',
                    $message->getHeaders()->get('In-Reply-To')?->getBodyAsString()
                );
                self::assertSame(
                    '<forum-notification-500@bewelcome.org> <forum-notification-501@bewelcome.org> <forum-notification-502@bewelcome.org>',
                    $message->getHeaders()->get('References')?->getBodyAsString()
                );

                return true;
            }))
        ;

        $this->assertTrue($this->createMailer($transport)->sendNotificationEmail(
            new Address('group@bewelcome.org'),
            $notification->getReceiver(),
            [
                'subject' => 'Re: Group topic',
                'notification' => $notification,
                'messageId' => 'forum-notification-503@bewelcome.org',
                'previousMessageIds' => [
                    'forum-notification-500@bewelcome.org',
                    'forum-notification-501@bewelcome.org',
                    'forum-notification-502@bewelcome.org',
                ],
            ]
        ));
    }

    private function createMailer(MailerInterface $transport): Mailer
    {
        return new Mailer(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(UrlGeneratorInterface::class),
            new Translator('en'),
            $transport
        );
    }

    private function createNotification(string $type, int $id = 501): PostNotification
    {
        $receiver = new Member()
            ->setId(7)
            ->setUsername('member-7')
            ->setEmail('member-7@example.org')
            ->setLocale('en')
        ;
        $thread = new ForumThread()->setId(42);
        $post = new ForumPost()->setId(101)->setThread($thread);

        $notification = new PostNotification()
            ->setReceiver($receiver)
            ->setPost($post)
            ->setType($type)
        ;
        new ReflectionProperty($notification, 'id')->setValue($notification, $id);

        return $notification;
    }
}
