<?php

namespace App\Service;

final readonly class BrowserNotificationSendResult
{
    public function __construct(
        private int $successes = 0,
        private int $terminalFailures = 0,
        private int $transientFailures = 0,
        private ?string $lastError = null,
    ) {
    }

    public static function success(): self
    {
        return new self(successes: 1);
    }

    public static function terminalFailure(?string $error = null): self
    {
        return new self(terminalFailures: 1, lastError: $error);
    }

    public static function transientFailure(?string $error = null): self
    {
        return new self(transientFailures: 1, lastError: $error);
    }

    public function shouldRetryQueuedNotification(): bool
    {
        return $this->hasTransientFailures();
    }

    public function shouldFailQueuedNotification(): bool
    {
        return !$this->hasSuccesses() && !$this->hasTransientFailures() && 0 < $this->terminalFailures;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    private function hasTransientFailures(): bool
    {
        return 0 < $this->transientFailures;
    }

    private function hasSuccesses(): bool
    {
        return 0 < $this->successes;
    }
}
