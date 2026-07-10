<?php

namespace App\Service;

use Minishlink\WebPush\VAPID;
use Throwable;

final readonly class BrowserPushConfig
{
    private bool $configured;

    public function __construct(
        private string $webPushVapidSubject,
        private string $webPushVapidPublicKey,
        private string $webPushVapidPrivateKey,
    ) {
        $this->configured = $this->hasValidConfiguration();
    }

    public function isConfigured(): bool
    {
        return $this->configured;
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

    private function hasValidConfiguration(): bool
    {
        if (!$this->hasValidSubject()) {
            return false;
        }

        try {
            VAPID::validate([
                'subject' => $this->webPushVapidSubject,
                'publicKey' => $this->webPushVapidPublicKey,
                'privateKey' => $this->webPushVapidPrivateKey,
            ]);
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    private function hasValidSubject(): bool
    {
        if (str_starts_with($this->webPushVapidSubject, 'mailto:')) {
            return false !== filter_var(substr($this->webPushVapidSubject, 7), \FILTER_VALIDATE_EMAIL);
        }

        $url = filter_var($this->webPushVapidSubject, \FILTER_VALIDATE_URL);

        return false !== $url && 'https' === parse_url($url, \PHP_URL_SCHEME);
    }
}
