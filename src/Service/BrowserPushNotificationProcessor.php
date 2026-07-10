<?php

namespace App\Service;

use App\Doctrine\MemberStatusType;
use App\Entity\BrowserPushNotification;
use App\Entity\BrowserPushNotificationDelivery;
use App\Entity\Member;
use App\Repository\BrowserPushNotificationDeliveryRepository;
use App\Repository\BrowserPushNotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

class BrowserPushNotificationProcessor
{
    private const int MAX_ATTEMPTS = 3;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private BrowserNotificationService $browserNotificationService,
    ) {
    }

    public function process(int $batchSize): void
    {
        /** @var BrowserPushNotificationRepository $notificationQueue */
        $notificationQueue = $this->entityManager->getRepository(BrowserPushNotification::class);
        /** @var BrowserPushNotificationDeliveryRepository $deliveryQueue */
        $deliveryQueue = $this->entityManager->getRepository(BrowserPushNotificationDelivery::class);
        $scheduledNotifications = $notificationQueue->claimScheduledNotifications($batchSize);

        foreach ($scheduledNotifications as $index => $notification) {
            $notificationQueue->renewProcessingLease(array_map(
                static fn (BrowserPushNotification $queuedNotification): int => $queuedNotification->getId(),
                \array_slice($scheduledNotifications, $index)
            ));
            $notificationStatus = BrowserPushNotification::STATUS_FROZEN;
            $receiver = $notification->getReceiver();
            $deliveries = $deliveryQueue->findForNotification($notification);
            if (\in_array($receiver->getStatus(), MemberStatusType::ACTIVE_ALL_ARRAY, true)) {
                $notificationStatus = $this->processActiveReceiverNotification(
                    $notification,
                    $receiver,
                    $deliveries
                );
            } else {
                foreach ($deliveries as $delivery) {
                    if ($this->isPendingDelivery($delivery)) {
                        $delivery->setStatus(BrowserPushNotification::STATUS_FROZEN);
                        $this->entityManager->persist($delivery);
                    }
                }
            }

            $notification->setStatus($notificationStatus);
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }
    }

    /**
     * @param BrowserPushNotificationDelivery[] $deliveries
     */
    private function processActiveReceiverNotification(
        BrowserPushNotification $notification,
        Member $receiver,
        array $deliveries,
    ): string {
        if ([] === $deliveries) {
            $notification->incrementAttempts();
            $notification->setLastError('No browser push notification deliveries.');

            return BrowserPushNotification::STATUS_FAILED;
        }

        $notification->incrementAttempts();
        $payload = BrowserNotificationPayload::fromStored(
            $notification->getType(),
            $notification->getSenderUsername(),
            $notification->getUrl()
        );
        foreach ($deliveries as $delivery) {
            if (!$this->isPendingDelivery($delivery)) {
                continue;
            }

            $delivery->incrementAttempts();
            try {
                $result = $this->browserNotificationService->sendDelivery(
                    $receiver,
                    $delivery,
                    $payload
                );
            } catch (Throwable $throwable) {
                $result = BrowserNotificationSendResult::transientFailure($throwable->getMessage());
            }

            if (null !== $result->getLastError()) {
                $delivery->setLastError($result->getLastError());
                $notification->setLastError($result->getLastError());
            }

            if ($result->shouldRetryQueuedNotification()) {
                $delivery->setStatus($this->getRetryStatus($delivery));
            } elseif ($result->shouldFailQueuedNotification()) {
                $delivery->setStatus(BrowserPushNotification::STATUS_FAILED);
            } else {
                $delivery->setStatus(BrowserPushNotification::STATUS_SENT);
                $delivery->setLastError(null);
            }

            $this->entityManager->persist($delivery);
        }

        return $this->getNotificationStatusFromDeliveries($deliveries);
    }

    private function getRetryStatus(BrowserPushNotificationDelivery $delivery): string
    {
        return $delivery->getAttempts() >= self::MAX_ATTEMPTS
            ? BrowserPushNotification::STATUS_FAILED
            : BrowserPushNotification::STATUS_SCHEDULED;
    }

    private function isPendingDelivery(BrowserPushNotificationDelivery $delivery): bool
    {
        return \in_array($delivery->getStatus(), [
            BrowserPushNotification::STATUS_SCHEDULED,
            BrowserPushNotification::STATUS_PROCESSING,
        ], true);
    }

    /**
     * @param BrowserPushNotificationDelivery[] $deliveries
     */
    private function getNotificationStatusFromDeliveries(array $deliveries): string
    {
        $hasPending = false;
        $hasSuccessfulDelivery = false;
        foreach ($deliveries as $delivery) {
            if (BrowserPushNotification::STATUS_SENT === $delivery->getStatus()) {
                $hasSuccessfulDelivery = true;
            }
            if ($this->isPendingDelivery($delivery)) {
                $hasPending = true;
            }
        }

        if ($hasPending) {
            return BrowserPushNotification::STATUS_SCHEDULED;
        }

        return $hasSuccessfulDelivery ? BrowserPushNotification::STATUS_SENT : BrowserPushNotification::STATUS_FAILED;
    }
}
