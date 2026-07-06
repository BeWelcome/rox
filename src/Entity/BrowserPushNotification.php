<?php

namespace App\Entity;

use App\Repository\BrowserPushNotificationRepository;
use App\Utilities\LifecycleCallbacksTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'browser_push_notification')]
#[ORM\Index(name: 'idx_browser_push_notification_status', columns: ['status'])]
#[ORM\Entity(repositoryClass: BrowserPushNotificationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class BrowserPushNotification
{
    use LifecycleCallbacksTrait;

    public const string STATUS_SCHEDULED = 'ToSend';
    public const string STATUS_PROCESSING = 'Processing';
    public const string STATUS_SENT = 'Sent';
    public const string STATUS_FAILED = 'Failed';
    public const string STATUS_FROZEN = 'Freeze';
    public const string STATUS_OPEN_ONLY = 'OpenOnly';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $receiver;

    #[ORM\Column(name: 'status', type: 'string', length: 32, nullable: false)]
    private string $status = self::STATUS_SCHEDULED;

    #[ORM\Column(name: 'type', type: 'string', length: 32, nullable: false)]
    private string $type;

    #[ORM\Column(name: 'sender_username', type: 'string', length: 255, nullable: true)]
    private ?string $senderUsername = null;

    #[ORM\Column(name: 'url', type: 'string', length: 2048, nullable: false)]
    private string $url;

    #[ORM\Column(name: 'last_error', type: 'string', length: 255, nullable: true)]
    private ?string $lastError = null;

    #[ORM\Column(name: 'attempts', type: 'integer', nullable: false)]
    private int $attempts = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReceiver(): Member
    {
        return $this->receiver;
    }

    public function setReceiver(Member $receiver): self
    {
        $this->receiver = $receiver;

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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = mb_substr($type, 0, 32);

        return $this;
    }

    public function getSenderUsername(): ?string
    {
        return $this->senderUsername;
    }

    public function setSenderUsername(?string $senderUsername): self
    {
        $this->senderUsername = null === $senderUsername ? null : mb_substr($senderUsername, 0, 255);

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = mb_substr($url, 0, 2048);

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
}
