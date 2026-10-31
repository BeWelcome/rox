<?php

namespace App\Tests\Model;

use App\Entity\BrowserPushNotification;
use App\Entity\Friend;
use App\Entity\Member;
use App\Model\FriendshipModel;
use App\Service\BrowserNotificationService;
use App\Service\BrowserPushPreferenceService;
use App\Service\Mailer;
use App\Service\PushGatewayInterface;
use App\Tests\Service\BrowserPushTestConfig;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FriendshipModelTest extends TestCase
{
    private Member $requester;
    private Member $receiver;
    private Member $stranger;

    /** @var object[] */
    private array $persisted = [];

    /** @var object[] */
    private array $removed = [];

    private FriendshipModel $model;

    protected function setUp(): void
    {
        $this->requester = new Member()->setId(7)->setUsername('requester');
        $this->receiver = new Member()->setId(3)->setUsername('receiver');
        $this->stranger = new Member()->setId(9)->setUsername('stranger');

        // answering a request mails nobody
        $mailer = $this->createMock(Mailer::class);
        $mailer->expects(self::never())->method('sendFriendshipNotification');
        $this->model = $this->model($mailer);
    }

    public function testARequestIsSavedMailedAndQueuedForTheBrowser(): void
    {
        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects(self::once())
            ->method('sendFriendshipNotification')
            ->with(self::callback(fn (Friend $friend): bool => $friend->getRequester() === $this->requester))
            ->willReturn(true)
        ;

        $friend = $this->model($mailer)->request($this->requester, $this->receiver);

        self::assertFalse($friend->isConfirmed());
        self::assertSame($this->receiver, $friend->getReceiver());
        self::assertSame($friend, $this->persisted[0]);
        $notification = $this->persisted[1];
        self::assertInstanceOf(BrowserPushNotification::class, $notification);
        self::assertSame($this->receiver, $notification->getReceiver());
        self::assertSame('friendship', $notification->getType());
        self::assertSame('requester', $notification->getSenderUsername());
        self::assertSame('/members/receiver/friendships', $notification->getUrl());
    }

    /**
     * @return array<string, array{string, bool, string[]}>
     */
    public static function actions(): array
    {
        return [
            'receiver of a request' => ['receiver', false, [FriendshipModel::ACCEPT, FriendshipModel::DECLINE]],
            'requester of a request' => ['requester', false, [FriendshipModel::WITHDRAW]],
            'receiver of a friendship' => ['receiver', true, [FriendshipModel::REMOVE]],
            'requester of a friendship' => ['requester', true, [FriendshipModel::REMOVE]],
            'someone else' => ['stranger', true, []],
        ];
    }

    /**
     * @param string[] $expected
     */
    #[DataProvider('actions')]
    public function testTheActionsDependOnTheSideAndTheState(string $member, bool $confirmed, array $expected): void
    {
        $friend = $this->friend($confirmed);

        self::assertSame($expected, $this->model->getActions($friend, $this->{$member}));
    }

    public function testTheReceiverAccepts(): void
    {
        $friend = $this->friend(false);

        self::assertTrue($this->model->answer($friend, $this->receiver, FriendshipModel::ACCEPT));
        self::assertTrue($friend->isConfirmed());
        self::assertSame([], $this->removed);
    }

    public function testTheRequesterCantAcceptTheirOwnRequest(): void
    {
        $friend = $this->friend(false);

        self::assertFalse($this->model->answer($friend, $this->requester, FriendshipModel::ACCEPT));
        self::assertFalse($friend->isConfirmed());
    }

    /**
     * @return array<string, array{string, bool, string}>
     */
    public static function removals(): array
    {
        return [
            'decline' => ['receiver', false, FriendshipModel::DECLINE],
            'withdraw' => ['requester', false, FriendshipModel::WITHDRAW],
            'remove (receiver)' => ['receiver', true, FriendshipModel::REMOVE],
            'remove (requester)' => ['requester', true, FriendshipModel::REMOVE],
        ];
    }

    #[DataProvider('removals')]
    public function testDecliningWithdrawingAndRemovingDeleteTheFriendship(string $member, bool $confirmed, string $action): void
    {
        $friend = $this->friend($confirmed);

        self::assertTrue($this->model->answer($friend, $this->{$member}, $action));
        self::assertSame([$friend], $this->removed);
    }

    public function testAnUnknownActionChangesNothing(): void
    {
        $friend = $this->friend(true);

        self::assertFalse($this->model->answer($friend, $this->receiver, 'befriend'));
        self::assertFalse($this->model->answer($friend, $this->stranger, FriendshipModel::REMOVE));
        self::assertSame([], $this->removed);
    }

    private function model(Mailer&MockObject $mailer): FriendshipModel
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('persist')->willReturnCallback(function (object $entity): void {
            $this->persisted[] = $entity;
        });
        $entityManager->method('remove')->willReturnCallback(function (object $entity): void {
            $this->removed[] = $entity;
        });
        $entityManager->method('getConnection')->willReturn($this->openOnlyConnection());

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnCallback(
            static fn (string $route, array $parameters): string => '/members/' . $parameters['username'] . '/friendships'
        );

        return new FriendshipModel(
            $entityManager,
            $mailer,
            new BrowserNotificationService(
                $entityManager,
                BrowserPushTestConfig::create(),
                $this->createStub(PushGatewayInterface::class),
                $this->createStub(TranslatorInterface::class),
                new NullLogger(),
                new BrowserPushPreferenceService($entityManager),
            ),
            $urlGenerator,
        );
    }

    private function friend(bool $confirmed): Friend
    {
        $friend = Friend::request($this->requester, $this->receiver);

        return $confirmed ? $friend->confirm() : $friend;
    }

    /**
     * The receiver gets browser notifications when they open the site (no push subscription needed).
     */
    private function openOnlyConnection(): Connection
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('fetchAssociative')->willReturn([
            'id' => 1,
            'DefaultValue' => BrowserPushPreferenceService::VALUE_NO,
        ]);
        $connection->method('fetchOne')->willReturn(BrowserPushPreferenceService::VALUE_OPEN_ONLY);

        return $connection;
    }
}
