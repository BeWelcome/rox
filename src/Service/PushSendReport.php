<?php

namespace App\Service;

use Minishlink\WebPush\MessageSentReport;

final readonly class PushSendReport
{
    private function __construct(
        private bool $success,
        private bool $removeSubscription,
        private ?string $error,
    ) {
    }

    public static function success(): self
    {
        return new self(true, false, null);
    }

    public static function failed(string $error): self
    {
        return new self(false, false, $error);
    }

    public static function expired(string $error): self
    {
        return new self(false, true, $error);
    }

    public static function rejected(string $error): self
    {
        return new self(false, true, $error);
    }

    public static function fromMessageSentReport(MessageSentReport $report): self
    {
        if ($report->isSuccess()) {
            return self::success();
        }

        if ($report->isSubscriptionExpired()) {
            return self::expired($report->getReason());
        }

        return self::failed($report->getReason());
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function shouldRemoveSubscription(): bool
    {
        return $this->removeSubscription;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}
