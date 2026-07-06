<?php

namespace App\Tests\Service;

use App\Doctrine\MemberStatusType;
use App\Entity\BrowserPushNotification;
use App\Entity\BrowserPushNotificationDelivery;
use App\Entity\BrowserPushSubscription;
use App\Entity\Member;
use App\Repository\BrowserPushNotificationDeliveryRepository;
use App\Repository\BrowserPushNotificationRepository;
use App\Service\BrowserNotificationMessage;
use App\Service\BrowserNotificationService;
use App\Service\BrowserPushConfig;
use App\Service\BrowserPushNotificationProcessor;
use App\Service\BrowserPushPreferenceService;
use App\Service\PushGatewayInterface;
use App\Service\PushSendReport;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionProperty;
use RuntimeException;
use Symfony\Contracts\Translation\TranslatorInterface;

class BrowserPushNotificationProcessorTest extends TestCase
{
    private int $nextMemberId = 1;
    private int $nextSubscriptionId = 1;

    public function testSendsQueuedBrowserPushNotification(): void
    {
        $notification = $this->notification($this->member('receiver', MemberStatusType::ACTIVE));
        $subscription = $this->subscription($notification->getReceiver());
        $delivery = $this->delivery($notification, $subscription);
        $gateway = $this->createMock(PushGatewayInterface::class);
        $gateway
            ->expects($this->once())
            ->method('send')
            ->with($subscription, self::callback(static function (BrowserNotificationMessage $message): bool {
                return [
                    'type' => 'message',
                    'title' => 'Translated message from sender',
                    'body' => 'Translated body',
                    'url' => '/conversation/123',
                ] === json_decode($message->toJson(), true, 512, \JSON_THROW_ON_ERROR);
            }))
            ->willReturn(PushSendReport::success())
        ;
        $entityManager = $this->entityManager([$notification], [$delivery]);

        $this->processQueue($entityManager, $gateway);

        self::assertSame(BrowserPushNotification::STATUS_SENT, $notification->getStatus());
        self::assertSame(BrowserPushNotification::STATUS_SENT, $delivery->getStatus());
    }

    public function testFreezesQueuedBrowserPushForInactiveReceiver(): void
    {
        $notification = $this->notification($this->member('receiver', MemberStatusType::BANNED));
        $delivery = $this->delivery($notification, $this->subscription($notification->getReceiver()));
        $gateway = $this->createMock(PushGatewayInterface::class);
        $gateway->expects($this->never())->method('send');
        $entityManager = $this->entityManager([$notification], [$delivery]);

        $this->processQueue($entityManager, $gateway);

        self::assertSame(BrowserPushNotification::STATUS_FROZEN, $notification->getStatus());
        self::assertSame(BrowserPushNotification::STATUS_FROZEN, $delivery->getStatus());
    }

    public function testGatewayFailureLeavesQueuedNotificationRetryable(): void
    {
        $notification = $this->notification($this->member('receiver', MemberStatusType::ACTIVE));
        $subscription = $this->subscription($notification->getReceiver());
        $delivery = $this->delivery($notification, $subscription);
        $gateway = $this->createMock(PushGatewayInterface::class);
        $gateway->expects($this->once())->method('send')->willThrowException(new RuntimeException('Gateway failed.'));
        $entityManager = $this->entityManager([$notification], [$delivery]);

        $this->processQueue($entityManager, $gateway);

        self::assertSame(BrowserPushNotification::STATUS_SCHEDULED, $notification->getStatus());
        self::assertSame(1, $notification->getAttempts());
        self::assertSame('Gateway failed.', $notification->getLastError());
        self::assertSame(BrowserPushNotification::STATUS_SCHEDULED, $delivery->getStatus());
        self::assertSame(1, $delivery->getAttempts());
        self::assertSame('Gateway failed.', $delivery->getLastError());
        self::assertSame('Gateway failed.', $subscription->getLastError());
    }

    public function testPartialGatewayFailureRetriesOnlyFailedDelivery(): void
    {
        $notification = $this->notification($this->member('receiver', MemberStatusType::ACTIVE));
        $firstSubscription = $this->subscription($notification->getReceiver(), 'first');
        $secondSubscription = $this->subscription($notification->getReceiver(), 'second');
        $firstDelivery = $this->delivery($notification, $firstSubscription);
        $secondDelivery = $this->delivery($notification, $secondSubscription);
        $gateway = $this->createMock(PushGatewayInterface::class);
        $gateway
            ->expects($this->exactly(2))
            ->method('send')
            ->willReturnOnConsecutiveCalls(
                PushSendReport::success(),
                PushSendReport::failed('Temporary provider failure.')
            )
        ;
        $entityManager = $this->entityManager([$notification], [$firstDelivery, $secondDelivery]);

        $this->processQueue($entityManager, $gateway);

        self::assertSame(BrowserPushNotification::STATUS_SCHEDULED, $notification->getStatus());
        self::assertSame(1, $notification->getAttempts());
        self::assertSame('Temporary provider failure.', $notification->getLastError());
        self::assertSame(BrowserPushNotification::STATUS_SENT, $firstDelivery->getStatus());
        self::assertNull($firstDelivery->getLastError());
        self::assertSame(BrowserPushNotification::STATUS_SCHEDULED, $secondDelivery->getStatus());
        self::assertSame('Temporary provider failure.', $secondDelivery->getLastError());
    }

    public function testRetrySkipsAlreadyDeliveredNotificationDelivery(): void
    {
        $notification = $this->notification($this->member('receiver', MemberStatusType::ACTIVE));
        $firstSubscription = $this->subscription($notification->getReceiver(), 'first');
        $secondSubscription = $this->subscription($notification->getReceiver(), 'second');
        $firstDelivery = $this->delivery($notification, $firstSubscription)
            ->setStatus(BrowserPushNotification::STATUS_SENT)
        ;
        $secondDelivery = $this->delivery($notification, $secondSubscription);
        $gateway = $this->createMock(PushGatewayInterface::class);
        $gateway
            ->expects($this->once())
            ->method('send')
            ->with($secondSubscription, $this->isInstanceOf(BrowserNotificationMessage::class))
            ->willReturn(PushSendReport::success())
        ;
        $entityManager = $this->entityManager([$notification], [$firstDelivery, $secondDelivery]);

        $this->processQueue($entityManager, $gateway);

        self::assertSame(BrowserPushNotification::STATUS_SENT, $notification->getStatus());
        self::assertSame(BrowserPushNotification::STATUS_SENT, $firstDelivery->getStatus());
        self::assertSame(BrowserPushNotification::STATUS_SENT, $secondDelivery->getStatus());
    }

    public function testGatewayFailureDeadLettersAfterRetryLimit(): void
    {
        $notification = $this->notification($this->member('receiver', MemberStatusType::ACTIVE))
            ->setAttempts(2)
        ;
        $subscription = $this->subscription($notification->getReceiver());
        $delivery = $this->delivery($notification, $subscription)
            ->setAttempts(2)
        ;
        $gateway = $this->createMock(PushGatewayInterface::class);
        $gateway->expects($this->once())->method('send')->willThrowException(new RuntimeException('Gateway failed.'));
        $entityManager = $this->entityManager([$notification], [$delivery]);

        $this->processQueue($entityManager, $gateway);

        self::assertSame(BrowserPushNotification::STATUS_FAILED, $notification->getStatus());
        self::assertSame(3, $notification->getAttempts());
        self::assertSame('Gateway failed.', $notification->getLastError());
        self::assertSame(BrowserPushNotification::STATUS_FAILED, $delivery->getStatus());
        self::assertSame(3, $delivery->getAttempts());
    }

    public function testNoSubscriptionsMarksQueuedNotificationFailed(): void
    {
        $notification = $this->notification($this->member('receiver', MemberStatusType::ACTIVE));
        $gateway = $this->createMock(PushGatewayInterface::class);
        $gateway->expects($this->never())->method('send');
        $entityManager = $this->entityManager([$notification], []);

        $this->processQueue($entityManager, $gateway);

        self::assertSame(BrowserPushNotification::STATUS_FAILED, $notification->getStatus());
        self::assertSame(1, $notification->getAttempts());
        self::assertSame('No browser push notification deliveries.', $notification->getLastError());
    }

    public function testExpiredSubscriptionMarksQueuedNotificationFailed(): void
    {
        $notification = $this->notification($this->member('receiver', MemberStatusType::ACTIVE));
        $subscription = $this->subscription($notification->getReceiver());
        $delivery = $this->delivery($notification, $subscription);
        $gateway = $this->createMock(PushGatewayInterface::class);
        $gateway->expects($this->once())->method('send')->willReturn(PushSendReport::expired('Expired endpoint.'));
        $entityManager = $this->entityManager([$notification], [$delivery]);
        $entityManager->expects($this->once())->method('remove')->with($subscription);

        $this->processQueue($entityManager, $gateway);

        self::assertSame(BrowserPushNotification::STATUS_FAILED, $notification->getStatus());
        self::assertSame(1, $notification->getAttempts());
        self::assertSame('Expired endpoint.', $notification->getLastError());
        self::assertSame(BrowserPushNotification::STATUS_FAILED, $delivery->getStatus());
    }

    private function processQueue(EntityManagerInterface $entityManager, PushGatewayInterface $gateway): void
    {
        $service = new BrowserNotificationService(
            $entityManager,
            new BrowserPushConfig('mailto:test@example.org', 'public-key', 'private-key'),
            $gateway,
            $this->translator(),
            new NullLogger(),
            $this->preferenceService()
        );
        $processor = new BrowserPushNotificationProcessor($entityManager, $service);
        $processor->process(10);
    }

    /**
     * @param BrowserPushNotification[]         $notifications
     * @param BrowserPushNotificationDelivery[] $deliveries
     */
    private function entityManager(
        array $notifications,
        array $deliveries,
    ): EntityManagerInterface&MockObject {
        $notificationRepository = $this->createMock(BrowserPushNotificationRepository::class);
        $notificationRepository
            ->expects($this->once())
            ->method('claimScheduledNotifications')
            ->with(10)
            ->willReturn($notifications)
        ;
        $deliveryRepository = $this->createMock(BrowserPushNotificationDeliveryRepository::class);
        $deliveryRepository
            ->expects($this->exactly(\count($notifications)))
            ->method('findForNotification')
            ->willReturnCallback(static function (BrowserPushNotification $notification) use ($deliveries): array {
                $notificationDelivery = static function (
                    BrowserPushNotificationDelivery $delivery,
                ) use ($notification): bool {
                    return $delivery->getNotification() === $notification;
                };

                return array_values(array_filter(
                    $deliveries,
                    $notificationDelivery
                ));
            })
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturnMap([
            [BrowserPushNotification::class, $notificationRepository],
            [BrowserPushNotificationDelivery::class, $deliveryRepository],
        ]);
        $entityManager->method('getConnection')->willReturn($this->connectionForDeliveries($deliveries));
        $entityManager->expects($this->atLeastOnce())->method('persist');
        $entityManager->expects($this->once())->method('flush');

        return $entityManager;
    }

    private function connectionForDeliveries(array $deliveries): Connection
    {
        $subscriptions = [];
        foreach ($deliveries as $delivery) {
            $subscription = $delivery->getSubscription();
            if (null === $subscription) {
                continue;
            }
            $subscriptions[$subscription->getId()] = [
                'member_id' => $subscription->getMember()->getId(),
            ];
        }

        $connection = $this->createStub(Connection::class);
        $connection->method('fetchAssociative')
            ->willReturnCallback(static function (string $query, array $parameters) use ($subscriptions): array|false {
                return $subscriptions[(int) $parameters[0]] ?? false;
            })
        ;

        return $connection;
    }

    private function notification(Member $receiver): BrowserPushNotification
    {
        return new BrowserPushNotification()
            ->setReceiver($receiver)
            ->setType('message')
            ->setSenderUsername('sender')
            ->setUrl('/conversation/123')
        ;
    }

    private function subscription(Member $receiver, string $id = 'push'): BrowserPushSubscription
    {
        $endpoint = 'https://fcm.googleapis.com/fcm/send/' . $id;

        $subscription = new BrowserPushSubscription()
            ->setMember($receiver)
            ->setEndpoint($endpoint)
            ->setEndpointHash(BrowserPushSubscription::hashEndpoint($endpoint))
            ->setPublicKey('public-key')
            ->setAuthToken('auth-token')
        ;
        $this->setId($subscription, $this->nextSubscriptionId++);

        return $subscription;
    }

    private function delivery(
        BrowserPushNotification $notification,
        BrowserPushSubscription $subscription,
    ): BrowserPushNotificationDelivery {
        return new BrowserPushNotificationDelivery()
            ->setNotification($notification)
            ->setSubscription($subscription)
        ;
    }

    private function member(string $username, string $status): Member
    {
        $member = new Member()
            ->setUsername($username)
            ->setStatus($status)
            ->setLocale('en')
        ;
        $this->setId($member, $this->nextMemberId++);

        return $member;
    }

    private function setId(object $entity, int $id): void
    {
        $property = new ReflectionProperty($entity, 'id');
        $property->setValue($entity, $id);
    }

    private function translator(): TranslatorInterface
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(static function (string $id, array $parameters = []): string {
            return match ($id) {
                'browser.notification.message.title' => 'Translated message from ' . $parameters['username'],
                'browser.notification.body' => 'Translated body',
                default => $id,
            };
        });

        return $translator;
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
