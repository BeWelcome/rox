<?php

namespace App\Tests\Model;

use App\Doctrine\InFolderType;
use App\Doctrine\SpamInfoType;
use App\Entity\Message;
use App\Model\ConversationModel;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The blocked-word check catches contact details hidden with Unicode tricks, as on prod (v3.8.5).
 */
class ConversationModelTest extends TestCase
{
    private ConversationModel $conversationModel;

    protected function setUp(): void
    {
        $this->conversationModel = new ConversationModel(
            $this->createStub(Mailer::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(TranslatorInterface::class),
        );
    }

    public static function blockedMessages(): array
    {
        return [
            'at sign' => ['write me at name@example.com'],
            'zero-width characters' => ["contact our sup\u{200B}port team"],
            'full-width at sign (NFKC)' => ["name\u{FF20}example.com"],
            'shop domain' => ['see my-offer.shop'],
            'support' => ['contact our support team'],
        ];
    }

    #[DataProvider('blockedMessages')]
    public function testBlockedWordsGoToSpamAndTheTextStaysAsWritten(string $text): void
    {
        $message = (new Message())->setMessage($text);

        $this->conversationModel->formatConversation($message);

        $this->assertSame(SpamInfoType::SPAM_BLOCKED_WORD, $message->getSpamInfo());
        $this->assertSame(InFolderType::SPAM, $message->getFolder());
        $this->assertSame($text, $message->getMessage());
    }

    public function testAnOrdinaryMessageIsNotTouched(): void
    {
        $message = (new Message())->setMessage('Hello, I arrive on Friday evening by train.');

        $this->conversationModel->formatConversation($message);

        $this->assertNotSame(SpamInfoType::SPAM_BLOCKED_WORD, $message->getSpamInfo());
        $this->assertNotSame(InFolderType::SPAM, $message->getFolder());
    }
}
