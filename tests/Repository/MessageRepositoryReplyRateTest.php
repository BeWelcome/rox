<?php

namespace App\Tests\Repository;

use App\Dto\ReplyRate;
use App\Entity\Member;
use App\Entity\Message;
use App\Repository\MessageRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

#[Group('integration')]
class MessageRepositoryReplyRateTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private Connection $connection;
    private MessageRepository $repository;

    private int $host;
    private int $guest;
    private int $otherGuest;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->connection = $this->entityManager->getConnection();
        $this->repository = $this->entityManager->getRepository(Message::class);

        // members that never received a message, so the fixtures don't change the counts
        $members = $this->connection->fetchFirstColumn(
            'SELECT id FROM member WHERE id NOT IN (SELECT IdReceiver FROM messages) ORDER BY id LIMIT 3'
        );
        self::assertCount(3, $members, 'the test fixtures contain members without messages');
        [$this->host, $this->guest, $this->otherGuest] = array_map('intval', $members);
    }

    public function testMemberWithoutRequestsHasNoRate(): void
    {
        $replyRate = $this->replyRate($this->host);

        self::assertSame(0, $replyRate->received);
        self::assertSame(0, $replyRate->answered);
        self::assertNull($replyRate->getRate());
        self::assertNull($replyRate->getPercentage());
    }

    public function testOnlyTheFirstRequestOfAConversationCountsAndAnyReplyAnswersIt(): void
    {
        $this->createConversations();

        $replyRate = $this->replyRate($this->host);

        // answered directly, unanswered, answered after a follow-up, answered with a draft only
        self::assertSame(4, $replyRate->received);
        self::assertSame(2, $replyRate->answered);
        self::assertSame(0.5, $replyRate->getRate());
        self::assertSame(50, $replyRate->getPercentage());
    }

    public function testRequestsTheMemberSentDontCountForThem(): void
    {
        $this->createConversations();

        // the guest received one request from the host and answered it
        $replyRate = $this->replyRate($this->guest);
        self::assertSame(1, $replyRate->received);
        self::assertSame(1, $replyRate->answered);
        self::assertSame(100, $replyRate->getPercentage());

        // the other guest only sent requests
        self::assertSame(0, $this->replyRate($this->otherGuest)->received);
    }

    public function testOnlyRequestsSentSinceTheGivenTimeCount(): void
    {
        $this->createConversations();

        $replyRate = $this->repository->getReplyRate(
            $this->entityManager->find(Member::class, $this->host),
            new DateTimeImmutable('-1 year'),
        );

        // the unanswered request is two years old
        self::assertSame(3, $replyRate->received);
        self::assertSame(2, $replyRate->answered);
    }

    public function testTwigFunctionReturnsTheReplyRate(): void
    {
        $this->createConversations();

        /** @var Environment $twig */
        $twig = static::getContainer()->get('twig');
        $template = $twig->createTemplate(
            '{% set rate = member_reply_rate(member) %}{{ rate.received }}/{{ rate.answered }}/{{ rate.percentage }}'
        );

        self::assertSame('4/2/50', $template->render([
            'member' => $this->entityManager->find(Member::class, $this->host),
        ]));
    }

    private function replyRate(int $memberId): ReplyRate
    {
        return $this->repository->getReplyRate($this->entityManager->find(Member::class, $memberId));
    }

    private function createConversations(): void
    {
        // request answered by the host
        $request = $this->message($this->guest, $this->host, $this->request());
        $this->message($this->host, $this->guest, $this->request(), $request);

        // request without an answer, sent two years ago
        $this->message($this->guest, $this->host, $this->request(), created: '-2 years');

        // request with a follow-up of the guest and then an answer of the host; the follow-up isn't a new request
        $request = $this->message($this->otherGuest, $this->host, $this->request());
        $followUp = $this->message($this->otherGuest, $this->host, $this->request(), $request);
        $this->message($this->host, $this->otherGuest, $this->request(), $followUp);

        // request the host only drafted an answer to
        $request = $this->message($this->otherGuest, $this->host, $this->request());
        $this->message($this->host, $this->otherGuest, $this->request(), $request, status: 'Draft');

        // request that ended up as spam
        $this->message($this->guest, $this->host, $this->request(), folder: 'Spam');

        // request still held back for the spam check
        $this->message($this->otherGuest, $this->host, $this->request(), status: 'ToCheck');

        // plain message, not a request
        $this->message($this->guest, $this->host, null);

        // request of the host to the guest, answered by the guest
        $request = $this->message($this->host, $this->guest, $this->request());
        $this->message($this->guest, $this->host, $this->request(), $request);
    }

    private function request(): int
    {
        $this->connection->insert('request', [
            'arrival' => new DateTimeImmutable('+1 month')->format('Y-m-d H:i:s'),
            'number_of_travellers' => 1,
            'status' => 0,
            'created' => new DateTimeImmutable()->format('Y-m-d H:i:s'),
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function message(
        int $sender,
        int $receiver,
        ?int $request,
        ?int $parent = null,
        string $status = 'Sent',
        string $folder = 'Normal',
        string $created = 'now',
    ): int {
        $created = new DateTimeImmutable($created)->format('Y-m-d H:i:s');
        $this->connection->insert('messages', [
            'MessageType' => 'MemberToMember',
            'created' => $created,
            'DateSent' => $created,
            'SpamInfo' => 'NotSpam',
            'Status' => $status,
            'Message' => 'Hello',
            'InFolder' => $folder,
            'initiator_id' => $sender,
            'IdSender' => $sender,
            'IdReceiver' => $receiver,
            'IdParent' => $parent,
            'request_id' => $request,
        ]);

        return (int) $this->connection->lastInsertId();
    }
}
