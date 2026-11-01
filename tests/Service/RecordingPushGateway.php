<?php

namespace App\Tests\Service;

use App\Entity\BrowserPushSubscription;
use App\Service\BrowserNotificationMessage;
use App\Service\PushGatewayInterface;
use App\Service\PushSendReport;

/**
 * Push gateway for tests: keeps what would be sent and answers with $report (success by default).
 */
final class RecordingPushGateway implements PushGatewayInterface
{
    /** @var list<array{endpoint: string, message: array<string, mixed>}> */
    public array $sent = [];
    public ?PushSendReport $report = null;

    public function send(BrowserPushSubscription $subscription, BrowserNotificationMessage $message): PushSendReport
    {
        $this->sent[] = [
            'endpoint' => $subscription->getEndpoint(),
            'message' => (array) json_decode($message->toJson(), true),
        ];

        return $this->report ?? PushSendReport::success();
    }
}
