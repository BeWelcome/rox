<?php

namespace App\Entity;

use App\Repository\BrowserPushSubscriptionRepository;
use App\Utilities\LifecycleCallbacksTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'browser_push_subscription')]
#[ORM\UniqueConstraint(name: 'uniq_browser_push_subscription_endpoint_hash', columns: ['endpoint_hash'])]
#[ORM\Index(name: 'idx_browser_push_subscription_member', columns: ['member_id'])]
#[ORM\Entity(repositoryClass: BrowserPushSubscriptionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class BrowserPushSubscription
{
    use LifecycleCallbacksTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $member;

    #[ORM\Column(name: 'endpoint_hash', type: 'string', length: 64, nullable: false)]
    private string $endpointHash;

    #[ORM\Column(name: 'endpoint', type: 'text', nullable: false)]
    private string $endpoint;

    #[ORM\Column(name: 'public_key', type: 'text', nullable: false)]
    private string $publicKey;

    #[ORM\Column(name: 'auth_token', type: 'string', length: 255, nullable: false)]
    private string $authToken;

    #[ORM\Column(name: 'content_encoding', type: 'string', length: 32, nullable: true)]
    private ?string $contentEncoding = null;

    #[ORM\Column(name: 'user_agent', type: 'string', length: 255, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(name: 'last_seen', type: 'datetime', nullable: true)]
    private ?DateTime $lastSeen = null;

    #[ORM\Column(name: 'last_error', type: 'string', length: 255, nullable: true)]
    private ?string $lastError = null;

    public static function hashEndpoint(string $endpoint): string
    {
        return hash('sha256', $endpoint);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setMember(Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getEndpointHash(): string
    {
        return $this->endpointHash;
    }

    public function setEndpointHash(string $endpointHash): self
    {
        $this->endpointHash = $endpointHash;

        return $this;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getEndpointHost(): string
    {
        return parse_url($this->endpoint, \PHP_URL_HOST) ?: '';
    }

    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;
        $this->endpointHash = self::hashEndpoint($endpoint);

        return $this;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): self
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    public function setAuthToken(string $authToken): self
    {
        $this->authToken = $authToken;

        return $this;
    }

    public function getContentEncoding(): ?string
    {
        return $this->contentEncoding;
    }

    public function setContentEncoding(?string $contentEncoding): self
    {
        $this->contentEncoding = $contentEncoding;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = null === $userAgent ? null : mb_substr($userAgent, 0, 255);

        return $this;
    }

    public function getLastSeen(): ?DateTime
    {
        return $this->lastSeen;
    }

    public function touchLastSeen(): self
    {
        $this->lastSeen = new DateTime();

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
