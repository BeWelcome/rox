<?php

namespace App\Tests\Service;

use App\Entity\FeedbackCategory;
use App\Entity\Member;
use App\Logger\Logger;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailerTest extends TestCase
{
    private MailerInterface $innerMailer;
    private Mailer $mailer;

    protected function setUp(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('getLocale')->willReturn('en');
        $translator->method('trans')->willReturnArgument(0);

        $this->innerMailer = $this->createMock(MailerInterface::class);

        $this->mailer = new Mailer(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(UrlGeneratorInterface::class),
            $translator,
            $this->innerMailer,
            $this->createStub(Logger::class),
        );
    }

    public function testSendFeedbackEmailUsesNoreplyAsFrom(): void
    {
        $captured = null;
        $this->innerMailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(static function (Email $email) use (&$captured) {
                $captured = $email;

                return true;
            }));

        $category = $this->createStub(FeedbackCategory::class);
        $category->method('getName')->willReturn('Safety_Concern');

        $this->mailer->sendFeedbackEmail(
            'reporter@yahoo.fr',
            new Address('abuse@bewelcome.org'),
            [
                'IdCategory' => $category,
                'FeedbackQuestion' => 'Test feedback',
                'member' => null,
                'no_reply_needed' => false,
                'browser' => 'Firefox',
                'host' => 'bewelcome.org',
                'version' => '1.0',
            ]
        );

        $this->assertNotNull($captured);

        $from = $captured->getFrom();
        $this->assertCount(1, $from);
        $this->assertSame('noreply@bewelcome.org', $from[0]->getAddress(), 'From must be noreply to pass DMARC');

        $replyTo = $captured->getReplyTo();
        $this->assertCount(1, $replyTo);
        $this->assertSame('reporter@yahoo.fr', $replyTo[0]->getAddress(), 'Reply-To must be the reporter so staff can reply');
    }

    public function testSendFeedbackEmailNullFallbackUsesNoreplyWithNoReplyTo(): void
    {
        $captured = null;
        $this->innerMailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(static function (Email $email) use (&$captured) {
                $captured = $email;

                return true;
            }));

        $category = $this->createStub(FeedbackCategory::class);
        $category->method('getName')->willReturn('General');

        // AboutModel falls back to 'feedback@bewelcome.org' when FeedbackEmail is null
        $this->mailer->sendFeedbackEmail(
            'feedback@bewelcome.org',
            new Address('feedback@bewelcome.org'),
            [
                'IdCategory' => $category,
                'FeedbackQuestion' => 'Test feedback',
                'member' => null,
                'no_reply_needed' => true,
                'browser' => 'Chrome',
                'host' => 'bewelcome.org',
                'version' => '1.0',
            ]
        );

        $from = $captured->getFrom();
        $this->assertSame('noreply@bewelcome.org', $from[0]->getAddress());
    }

    public function testSendCommentReportedFeedbackEmailUsesNoreplyAsFrom(): void
    {
        $captured = null;
        $this->innerMailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(static function (Email $email) use (&$captured) {
                $captured = $email;

                return true;
            }));

        $member = $this->createStub(Member::class);
        $member->method('getEmail')->willReturn('member@gmail.com');

        $category = $this->createStub(FeedbackCategory::class);
        $category->method('getEmailToNotify')->willReturn('account@bewelcome.org');

        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($category);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('getLocale')->willReturn('en');
        $translator->method('trans')->willReturnArgument(0);

        $mailer = new Mailer(
            $em,
            $this->createStub(UrlGeneratorInterface::class),
            $translator,
            $this->innerMailer,
            $this->createStub(Logger::class),
        );

        $mailer->sendCommentReportedFeedbackEmail($member, ['subject' => 'Comment feedback']);

        $this->assertNotNull($captured);

        $from = $captured->getFrom();
        $this->assertCount(1, $from);
        $this->assertSame('noreply@bewelcome.org', $from[0]->getAddress(), 'From must be noreply to pass DMARC');

        $replyTo = $captured->getReplyTo();
        $this->assertCount(1, $replyTo);
        $this->assertSame('member@gmail.com', $replyTo[0]->getAddress(), 'Reply-To must be the member so staff can reply');
    }
}
