<?php

namespace App\Service;

final readonly class BrowserPushConfig
{
    public function __construct(
        private string $webPushVapidSubject,
        private string $webPushVapidPublicKey,
        private string $webPushVapidPrivateKey,
    ) {
    }

    public function isConfigured(): bool
    {
        return '' !== trim($this->webPushVapidSubject)
            && '' !== trim($this->webPushVapidPublicKey)
            && '' !== trim($this->webPushVapidPrivateKey);
    }

    public function getSubject(): string
    {
        return $this->webPushVapidSubject;
    }

    public function getPublicKey(): string
    {
        return $this->webPushVapidPublicKey;
    }

    public function getPrivateKey(): string
    {
        return $this->webPushVapidPrivateKey;
    }
}
