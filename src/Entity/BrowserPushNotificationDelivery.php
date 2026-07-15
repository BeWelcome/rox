<?php

namespace App\Entity;

use App\Repository\BrowserPushNotificationDeliveryRepository;
use App\Utilities\LifecycleCallbacksTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'browser_push_notification_delivery')]
#[ORM\Index(name: 'idx_browser_push_notification_delivery_notification_status', columns: ['notification_id', 'status'])]
#[ORM\Index(name: 'idx_browser_push_notification_delivery_subscription', columns: ['subscription_id'])]
#[ORM\Entity(repositoryClass: BrowserPushNotificationDeliveryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class BrowserPushNotificationDelivery
{
    use LifecycleCallbacksTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'notification_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: BrowserPushNotification::class)]
    private BrowserPushNotification $notification;

    #[ORM\JoinColumn(name: 'subscription_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: BrowserPushSubscription::class)]
    private ?BrowserPushSubscription $subscription = null;

    #[ORM\Column(name: 'status', type: 'string', length: 32, nullable: false)]
    private string $status = BrowserPushNotification::STATUS_SCHEDULED;

    #[ORM\Column(name: 'attempts', type: 'integer', nullable: false)]
    private int $attempts = 0;

    #[ORM\Column(name: 'last_error', type: 'string', length: 255, nullable: true)]
    private ?string $lastError = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotification(): BrowserPushNotification
    {
        return $this->notification;
    }

    public function setNotification(BrowserPushNotification $notification): self
    {
        $this->notification = $notification;

        return $this;
    }

    public function getSubscription(): ?BrowserPushSubscription
    {
        return $this->subscription;
    }

    public function setSubscription(?BrowserPushSubscription $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function setAttempts(int $attempts): self
    {
        $this->attempts = $attempts;

        return $this;
    }

    public function incrementAttempts(): self
    {
        ++$this->attempts;

        return $this;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function setLastError(?string $lastError): self
    {
        $this->lastError = null === $lastError ? null : mb_substr($lastError, 0, 255);

        return $this;
    }
}
