<?php

namespace App\Service;

use App\Entity\BrowserPushNotification;
use App\Entity\BrowserPushNotificationDelivery;
use App\Entity\BrowserPushSubscription;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

final readonly class BrowserNotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BrowserPushConfig $browserPushConfig,
        private PushGatewayInterface $gateway,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
        private bool $enabled = true,
    ) {
    }

    public function queue(Member $receiver, BrowserNotificationPayload $payload): void
    {
        if (!$this->enabled || !$this->browserPushConfig->isConfigured()) {
            return;
        }

        try {
            $subscriptions = $this->findSubscriptions($receiver);
            if ([] === $subscriptions) {
                return;
            }

            $notification = new BrowserPushNotification()
                ->setReceiver($receiver)
                ->setType($payload->getType())
                ->setSenderUsername($payload->getSenderUsername())
                ->setUrl($payload->getUrl())
            ;

            $this->entityManager->persist($notification);
            foreach ($subscriptions as $subscription) {
                $delivery = new BrowserPushNotificationDelivery()
                    ->setNotification($notification)
                    ->setSubscription($subscription)
                ;
                $this->entityManager->persist($delivery);
            }
            $this->entityManager->flush();
        } catch (Throwable $throwable) {
            $this->logger->warning('Browser push notification queueing failed.', [
                'exception' => $throwable,
            ]);
        }
    }

    public function sendDelivery(
        Member $receiver,
        BrowserPushNotificationDelivery $delivery,
        BrowserNotificationPayload $payload,
    ): BrowserNotificationSendResult {
        if (!$this->enabled || !$this->browserPushConfig->isConfigured()) {
            return BrowserNotificationSendResult::transientFailure('Browser push notifications are not configured.');
        }

        $subscription = $delivery->getSubscription();
        if (null === $subscription) {
            return BrowserNotificationSendResult::terminalFailure('Browser push subscription is no longer available.');
        }
        if (!$this->isSameMember($subscription->getMember(), $receiver)) {
            return BrowserNotificationSendResult::terminalFailure(
                'Browser push subscription does not belong to the notification receiver.'
            );
        }
        if (!$this->subscriptionStillBelongsToReceiver($subscription, $receiver)) {
            return BrowserNotificationSendResult::terminalFailure(
                'Browser push subscription is no longer assigned to the notification receiver.'
            );
        }

        try {
            return $this->sendToSubscription(
                $subscription,
                $payload->toMessage($this->translator, $receiver->getLocale())
            );
        } catch (Throwable $throwable) {
            $this->logger->warning('Browser push notification sending failed.', [
                'exception' => $throwable,
            ]);

            return BrowserNotificationSendResult::transientFailure($throwable->getMessage());
        }
    }

    private function sendToSubscription(
        BrowserPushSubscription $subscription,
        BrowserNotificationMessage $message,
    ): BrowserNotificationSendResult {
        try {
            $report = $this->gateway->send($subscription, $message);
            if ($report->shouldRemoveSubscription()) {
                $this->entityManager->remove($subscription);

                return BrowserNotificationSendResult::terminalFailure($report->getError());
            }

            $subscription->setLastError($report->isSuccess() ? null : $report->getError());

            return $report->isSuccess()
                ? BrowserNotificationSendResult::success()
                : BrowserNotificationSendResult::transientFailure($report->getError());
        } catch (Throwable $throwable) {
            $subscription->setLastError($throwable->getMessage());
            $this->logger->warning('Browser push notification failed.', [
                'exception' => $throwable,
                'subscription' => $subscription->getEndpointHash(),
            ]);

            return BrowserNotificationSendResult::transientFailure($throwable->getMessage());
        }
    }

    /**
     * @return BrowserPushSubscription[]
     */
    private function findSubscriptions(Member $receiver): array
    {
        return $this->entityManager
            ->getRepository(BrowserPushSubscription::class)
            ->findBy(['member' => $receiver])
        ;
    }

    private function isSameMember(Member $first, Member $second): bool
    {
        if ($first === $second) {
            return true;
        }

        try {
            return $first->getId() === $second->getId();
        } catch (Error) {
            return false;
        }
    }

    private function subscriptionStillBelongsToReceiver(
        BrowserPushSubscription $subscription,
        Member $receiver,
    ): bool {
        $subscriptionId = $subscription->getId();
        $receiverId = $this->getMemberId($receiver);
        if (null === $subscriptionId || null === $receiverId) {
            return false;
        }

        $row = $this->entityManager->getConnection()->fetchAssociative(
            'SELECT member_id FROM browser_push_subscription WHERE id = ?',
            [$subscriptionId]
        );
        if (false === $row) {
            return false;
        }

        return $receiverId === (int) $row['member_id'];
    }

    private function getMemberId(Member $member): ?int
    {
        try {
            return $member->getId();
        } catch (Error) {
            return null;
        }
    }
}
