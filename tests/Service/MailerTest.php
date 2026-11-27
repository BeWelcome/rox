<?php

namespace App\Tests\Service;

use App\Entity\FeedbackCategory;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Logger\Logger;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;

class MailerTest extends TestCase
{
    private MailerInterface $innerMailer;
    private Mailer $mailer;

    protected function setUp(): void
    {
        // Mailer calls getLocale(), which TranslatorInterface only declares from Symfony 6 on.
        $translator = $this->createStub(Translator::class);
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

        // Mailer calls getLocale(), which TranslatorInterface only declares from Symfony 6 on.
        $translator = $this->createStub(Translator::class);
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

    public function testSendNewsletterEmailRefusesASubjectThatIsStillItsKey(): void
    {
        $this->innerMailer->expects($this->never())->method('send');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('broadcast_title_suspension.notification.2026');

        // The translator has the body but no subject: it answers with the key.
        $this->newsletterMailer(['broadcast_body_suspension.notification.2026' => 'Body'])
            ->sendNewsletterEmail($this->newsletter(), $this->member('tr'), []);
    }

    public function testSendNewsletterEmailRefusesATextThatIsStillItsKey(): void
    {
        $this->innerMailer->expects($this->never())->method('send');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('broadcast_body_suspension.notification.2026');

        $this->newsletterMailer(['broadcast_title_suspension.notification.2026' => 'Title'])
            ->sendNewsletterEmail($this->newsletter(), $this->member('tr'), []);
    }

    public function testSendNewsletterEmailSendsATranslatedNewsletter(): void
    {
        $captured = null;
        $this->innerMailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(static function (Email $email) use (&$captured) {
                $captured = $email;

                return true;
            }));

        $sent = $this->newsletterMailer([
            'broadcast_title_suspension.notification.2026' => 'Title',
            'broadcast_body_suspension.notification.2026' => 'Body',
        ])->sendNewsletterEmail($this->newsletter(), $this->member('en'), ['unsubscribe_key' => 'key']);

        $this->assertTrue($sent);
        $this->assertSame('Title', $captured->getSubject());
    }

    private function newsletter(): Newsletter
    {
        return (new Newsletter())
            ->setName('suspension.notification.2026')
            ->setType(Newsletter::SUSPENSION_NOTIFICATION);
    }

    private function member(string $locale): Member
    {
        $language = (new Language())->setShortCode($locale);
        $member = $this->createStub(Member::class);
        $member->method('getPreferredLanguage')->willReturn($language);
        $member->method('getEmail')->willReturn('member@example.org');
        $member->method('getUsername')->willReturn('member');

        return $member;
    }

    /**
     * @param array<string, string> $messages
     */
    private function newsletterMailer(array $messages): Mailer
    {
        $translator = $this->createStub(Translator::class);
        $translator->method('getLocale')->willReturn('en');
        $translator->method('trans')->willReturnCallback(
            static fn (string $id): string => $messages[$id] ?? $id
        );

        return new Mailer(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(UrlGeneratorInterface::class),
            $translator,
            $this->innerMailer,
            $this->createStub(Logger::class),
        );
    }
}
