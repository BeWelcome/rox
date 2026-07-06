<?php

namespace App\Tests\Service;

use App\Entity\BrowserPushNotification;
use App\Entity\BrowserPushNotificationDelivery;
use App\Entity\BrowserPushSubscription;
use App\Entity\Member;
use App\Service\BrowserNotificationMessage;
use App\Service\BrowserNotificationPayload;
use App\Service\BrowserNotificationService;
use App\Service\BrowserPushConfig;
use App\Service\BrowserPushPreferenceService;
use App\Service\PushGatewayInterface;
use App\Service\PushSendReport;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionProperty;
use RuntimeException;
use Symfony\Contracts\Translation\TranslatorInterface;

class BrowserNotificationServiceTest extends TestCase
{
    private int $nextMemberId = 1;

    public function testDoesNothingWhenDisabled(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('getRepository');

        $service = $this->service($entityManager, $this->createStub(PushGatewayInterface::class), false);
        $service->queue(new Member(), $this->payload());
    }

    public function testDoesNothingWhenPushIsNotConfigured(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('getRepository');

        $service = $this->service(
            $entityManager,
            $this->createStub(PushGatewayInterface::class),
            true,
            new BrowserPushConfig('', '', '')
        );
        $service->queue(new Member(), $this->payload());
    }

    public function testQueuesPayloadWhenConfiguredAndSubscriptionExists(): void
    {
        $receiver = $this->member('receiver');
        $subscription = $this->subscription('https://93.184.216.34/queue');
        $entityManager = $this->entityManagerWithSubscriptions([$subscription], 1);
        $persistedEntities = [];
        $entityManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$persistedEntities): void {
                $persistedEntities[] = $entity;
            })
        ;

        $service = $this->service($entityManager, $this->createStub(PushGatewayInterface::class));
        $service->queue($receiver, $this->payload());

        self::assertInstanceOf(BrowserPushNotification::class, $persistedEntities[0]);
        self::assertSame($receiver, $persistedEntities[0]->getReceiver());
        self::assertSame('message', $persistedEntities[0]->getType());
        self::assertSame('sender', $persistedEntities[0]->getSenderUsername());
        self::assertSame('/conversation/123', $persistedEntities[0]->getUrl());
        self::assertInstanceOf(BrowserPushNotificationDelivery::class, $persistedEntities[1]);
        self::assertSame($persistedEntities[0], $persistedEntities[1]->getNotification());
        self::assertSame($subscription, $persistedEntities[1]->getSubscription());
    }

    public function testDoesNotQueueWithoutSubscriptions(): void
    {
        $entityManager = $this->entityManagerWithSubscriptions([], 0);
        $entityManager->expects($this->never())->method('persist');

        $service = $this->service($entityManager, $this->createStub(PushGatewayInterface::class));
        $service->queue($this->member('receiver'), $this->payload());
    }

    public function testQueuesOpenOnlyPayloadWithoutWebPushDelivery(): void
    {
        $receiver = $this->member('receiver');
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $persistedEntities = [];
        $entityManager
            ->expects($this->never())
            ->method('getRepository')
        ;
        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$persistedEntities): void {
                $persistedEntities[] = $entity;
            })
        ;
        $entityManager->expects($this->once())->method('flush');

        $service = $this->service(
            $entityManager,
            $this->createStub(PushGatewayInterface::class),
            preferenceService: $this->preferenceService('OpenOnly')
        );
        $service->queue($receiver, $this->payload());

        self::assertCount(1, $persistedEntities);
        self::assertInstanceOf(BrowserPushNotification::class, $persistedEntities[0]);
        self::assertSame($receiver, $persistedEntities[0]->getReceiver());
        self::assertSame(BrowserPushNotification::STATUS_OPEN_ONLY, $persistedEntities[0]->getStatus());
        self::assertSame('message', $persistedEntities[0]->getType());
        self::assertSame('/conversation/123', $persistedEntities[0]->getUrl());
    }

    public function testQueueFailureIsLoggedAndSwallowed(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->willThrowException(new RuntimeException('Database unavailable.'))
        ;
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with(
            'Browser push notification queueing failed.',
            self::callback(static fn (array $context): bool => $context['exception'] instanceof RuntimeException)
        );

        $service = $this->service($entityManager, $this->createStub(PushGatewayInterface::class), logger: $logger);
        $service->queue($this->member('receiver'), $this->payload());
    }

    public function testSendsTranslatedPayloadToDeliverySubscription(): void
    {
        $receiver = $this->member('receiver', 'de');
        $this->setMemberId($receiver, 7);
        $subscription = $this->subscription('https://93.184.216.34/success', $receiver);
        $this->setSubscriptionId($subscription, 42);
        $delivery = new BrowserPushNotificationDelivery()
            ->setSubscription($subscription)
        ;
        $gateway = $this->createMock(PushGatewayInterface::class);
        $gateway
            ->expects($this->once())
            ->method('send')
            ->with($subscription, self::callback(static function (BrowserNotificationMessage $message): bool {
                return [
                    'type' => 'message',
                    'title' => 'Translated message title for sender',
                    'body' => 'Translated notification body',
                    'url' => '/conversation/123',
                ] === json_decode($message->toJson(), true, 512, \JSON_THROW_ON_ERROR);
            }))
            ->willReturn(PushSendReport::success())
        ;
        $entityManager = $this->entityManagerWithSubscriptionOwnership($subscription);

        $service = $this->service($entityManager, $gateway, translator: $this->translator());
        $result = $service->sendDelivery($receiver, $delivery, $this->payload());

        self::assertFalse($result->shouldRetryQueuedNotification());
        self::assertFalse($result->shouldFailQueuedNotification());
    }

    public function testGatewayFailureIsLoggedAndSwallowed(): void
    {
        $receiver = $this->member('receiver');
        $this->setMemberId($receiver, 7);
        $subscription = $this->subscription('https://93.184.216.34/failure', $receiver);
        $this->setSubscriptionId($subscription, 42);
        $delivery = new BrowserPushNotificationDelivery()
            ->setSubscription($subscription)
        ;
        $gateway = $this->createMock(PushGatewayInterface::class);
        $gateway
            ->expects($this->once())
            ->method('send')
            ->willThrowException(new RuntimeException('Push provider failed.'))
        ;
        $entityManager = $this->entityManagerWithSubscriptionOwnership($subscription);

        $service = $this->service($entityManager, $gateway);
        $result = $service->sendDelivery($receiver, $delivery, $this->payload());

        self::assertTrue($result->shouldRetryQueuedNotification());
        self::assertFalse($result->shouldFailQueuedNotification());

        self::assertSame('Push provider failed.', $subscription->getLastError());
    }

    public function testExpiredSubscriptionIsRemoved(): void
    {
        $receiver = $this->member('receiver');
        $this->setMemberId($receiver, 7);
        $subscription = $this->subscription('https://93.184.216.34/expired', $receiver);
        $this->setSubscriptionId($subscription, 42);
        $delivery = new BrowserPushNotificationDelivery()
            ->setSubscription($subscription)
        ;
        $gateway = $this->createMock(PushGatewayInterface::class);
        $gateway->expects($this->once())->method('send')->willReturn(PushSendReport::expired('Expired endpoint.'));
        $entityManager = $this->entityManagerWithSubscriptionOwnership($subscription, $subscription);

        $service = $this->service($entityManager, $gateway);
        $result = $service->sendDelivery($receiver, $delivery, $this->payload());

        self::assertFalse($result->shouldRetryQueuedNotification());
        self::assertTrue($result->shouldFailQueuedNotification());
    }

    public function testRejectedSubscriptionIsRemoved(): void
    {
        $receiver = $this->member('receiver');
        $this->setMemberId($receiver, 7);
        $subscription = $this->subscription('https://93.184.216.34/rejected', $receiver);
        $this->setSubscriptionId($subscription, 42);
        $delivery = new BrowserPushNotificationDelivery()
            ->setSubscription($subscription)
        ;
        $gateway = $this->createMock(PushGatewayInterface::class);
        $gateway->expects($this->once())->method('send')->willReturn(PushSendReport::rejected('Invalid endpoint.'));
        $entityManager = $this->entityManagerWithSubscriptionOwnership($subscription, $subscription);

        $service = $this->service($entityManager, $gateway);
        $result = $service->sendDelivery($receiver, $delivery, $this->payload());

        self::assertFalse($result->shouldRetryQueuedNotification());
        self::assertTrue($result->shouldFailQueuedNotification());
    }

    public function testDeliveryForDifferentMemberIsNotSent(): void
    {
        $receiver = $this->member('receiver');
        $subscription = $this->subscription('https://93.184.216.34/wrong-member', $this->member('other'));
        $delivery = new BrowserPushNotificationDelivery()
            ->setSubscription($subscription)
        ;
        $gateway = $this->createMock(PushGatewayInterface::class);
        $gateway->expects($this->never())->method('send');

        $service = $this->service($this->createStub(EntityManagerInterface::class), $gateway);
        $result = $service->sendDelivery($receiver, $delivery, $this->payload());

        self::assertFalse($result->shouldRetryQueuedNotification());
        self::assertTrue($result->shouldFailQueuedNotification());
    }

    public function testDeliveryReownedInDatabaseIsNotSent(): void
    {
        $receiver = $this->member('receiver');
        $this->setMemberId($receiver, 7);
        $subscription = $this->subscription('https://93.184.216.34/stale-reowned', $receiver);
        $this->setSubscriptionId($subscription, 42);
        $delivery = new BrowserPushNotificationDelivery()
            ->setSubscription($subscription)
        ;
        $gateway = $this->createMock(PushGatewayInterface::class);
        $gateway->expects($this->never())->method('send');
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(['member_id' => 8])
        ;
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);

        $service = $this->service($entityManager, $gateway);
        $result = $service->sendDelivery($receiver, $delivery, $this->payload());

        self::assertFalse($result->shouldRetryQueuedNotification());
        self::assertTrue($result->shouldFailQueuedNotification());
    }

    private function service(
        EntityManagerInterface $entityManager,
        PushGatewayInterface $gateway,
        bool $enabled = true,
        ?BrowserPushConfig $config = null,
        ?TranslatorInterface $translator = null,
        ?LoggerInterface $logger = null,
        ?BrowserPushPreferenceService $preferenceService = null,
    ): BrowserNotificationService {
        return new BrowserNotificationService(
            $entityManager,
            $config ?? $this->configuredPush(),
            $gateway,
            $translator ?? $this->translator(),
            $logger ?? new NullLogger(),
            $preferenceService ?? $this->preferenceService(),
            $enabled
        );
    }

    private function configuredPush(): BrowserPushConfig
    {
        return new BrowserPushConfig('mailto:test@example.org', 'public-key', 'private-key');
    }

    private function preferenceService(string $value = 'Always'): BrowserPushPreferenceService
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('fetchAssociative')->willReturn([
            'id' => 1,
            'DefaultValue' => 'Always',
        ]);
        $connection->method('fetchOne')->willReturn($value);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);

        return new BrowserPushPreferenceService($entityManager);
    }

    /**
     * @param BrowserPushSubscription[] $subscriptions
     */
    private function entityManagerWithSubscriptions(
        array $subscriptions,
        int $expectedFlushes,
    ): EntityManagerInterface&MockObject {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())->method('findBy')->willReturn($subscriptions);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')
            ->with(BrowserPushSubscription::class)
            ->willReturn($repository)
        ;
        $entityManager->expects($this->exactly($expectedFlushes))->method('flush');

        return $entityManager;
    }

    private function entityManagerWithSubscriptionOwnership(
        BrowserPushSubscription $subscription,
        ?BrowserPushSubscription $removedSubscription = null,
    ): EntityManagerInterface&MockObject {
        $connection = $this->createStub(Connection::class);
        $connection->method('fetchAssociative')->willReturn([
            'member_id' => $subscription->getMember()->getId(),
        ]);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);
        if (null === $removedSubscription) {
            $entityManager->expects($this->never())->method('remove');
        } else {
            $entityManager->expects($this->once())->method('remove')->with($removedSubscription);
        }

        return $entityManager;
    }

    private function subscription(string $endpoint, ?Member $member = null): BrowserPushSubscription
    {
        $subscription = new BrowserPushSubscription();
        $subscription->setMember($member ?? $this->member('receiver'));
        $subscription->setEndpoint($endpoint);
        $subscription->setEndpointHash(BrowserPushSubscription::hashEndpoint($endpoint));
        $subscription->setPublicKey('public-key');
        $subscription->setAuthToken('auth-token');

        return $subscription;
    }

    private function payload(): BrowserNotificationPayload
    {
        return BrowserNotificationPayload::message($this->member('sender'), '/conversation/123');
    }

    private function member(string $username, string $locale = 'en'): Member
    {
        $member = new Member();
        $member->setUsername($username);
        $member->setLocale($locale);
        $this->setMemberId($member, $this->nextMemberId++);

        return $member;
    }

    private function setMemberId(Member $member, int $id): void
    {
        $property = new ReflectionProperty(Member::class, 'id');
        $property->setValue($member, $id);
    }

    private function setSubscriptionId(BrowserPushSubscription $subscription, int $id): void
    {
        $property = new ReflectionProperty(BrowserPushSubscription::class, 'id');
        $property->setValue($subscription, $id);
    }

    private function translator(): TranslatorInterface
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(static function (
            string $id,
            array $parameters = [],
            ?string $domain = null,
            ?string $locale = null,
        ): string {
            return match ($id) {
                'browser.notification.message.title' => 'Translated message title for ' . $parameters['username'],
                'browser.notification.body' => 'Translated notification body',
                default => $id . ':' . $locale . ':' . $domain,
            };
        });

        return $translator;
    }
}
