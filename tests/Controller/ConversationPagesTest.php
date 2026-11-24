<?php

namespace App\Tests\Controller;

use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The pages of a conversation are for its two members: the conversation, with its deleted messages, the reply
 * page and the page that opens the report dialog. Another member gets none of them.
 */
#[Group('integration')]
final class ConversationPagesTest extends WebTestCase
{
    private const string TEXT = 'zztest conversation pages';

    private KernelBrowser $client;
    private Message $message;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $sender = $this->member('member-5');
        $this->message = new Message()
            ->setSender($sender)
            ->setReceiver($this->member('member-2'))
            ->setInitiator($sender)
            ->setMessage(self::TEXT)
            ->setStatus(MessageStatusType::SENT)
            ->setFolder(InFolderType::NORMAL);
        $entityManager->persist($this->message);
        $entityManager->flush();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function pages(): iterable
    {
        yield 'the conversation' => [''];
        yield 'with the deleted messages' => ['/deleted'];
        yield 'the reply page' => ['/reply'];
        yield 'the report dialog' => ['/report'];
    }

    #[DataProvider('pages')]
    public function testTheReceiverOpensThePage(string $page): void
    {
        $this->client->loginUser($this->member('member-2'));

        $this->client->request('GET', '/conversation/' . $this->message->getId() . $page);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString(self::TEXT, (string) $this->client->getResponse()->getContent());
    }

    #[DataProvider('pages')]
    public function testAnotherMemberDoesNot(string $page): void
    {
        $this->client->loginUser($this->member('member-3'));

        $this->client->request('GET', '/conversation/' . $this->message->getId() . $page);

        self::assertResponseStatusCodeSame(403);
    }

    public function testAnotherMembersReportChangesNothing(): void
    {
        $this->client->loginUser($this->member('member-3'));

        $this->client->request('POST', '/conversation/' . $this->message->getId() . '/report', [
            'report_spam' => ['comment' => 'zztest'],
        ]);

        self::assertResponseStatusCodeSame(403);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $message = $entityManager->getRepository(Message::class)->find($this->message->getId());
        self::assertSame(InFolderType::NORMAL, $message->getFolder());
        self::assertStringNotContainsString(SpamInfoType::MEMBER_SAYS_SPAM, (string) $message->getSpamInfo());
    }

    private function member(string $username): Member
    {
        return static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Member::class)->findOneBy(['username' => $username]);
    }
}
