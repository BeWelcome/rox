<?php

namespace App\Service;

final readonly class ValidatedBrowserPushEndpoint
{
    public function __construct(
        private string $host,
        private int $port,
        private ?string $pinnedIp,
        private string $canonicalEndpoint,
    ) {
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getPinnedIp(): ?string
    {
        return $this->pinnedIp;
    }

    public function getCanonicalEndpoint(): string
    {
        return $this->canonicalEndpoint;
    }
}
