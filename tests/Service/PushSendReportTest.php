<?php

namespace App\Tests\Service;

use App\Service\PushSendReport;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Minishlink\WebPush\MessageSentReport;
use PHPUnit\Framework\TestCase;

class PushSendReportTest extends TestCase
{
    public function testExpiredProviderResponseRemovesSubscription(): void
    {
        $report = PushSendReport::fromMessageSentReport($this->messageSentReport(410, 'Gone'));

        self::assertFalse($report->isSuccess());
        self::assertTrue($report->shouldRemoveSubscription());
    }

    public function testInvalidSubscriptionBadRequestDoesNotRemoveSubscription(): void
    {
        $report = PushSendReport::fromMessageSentReport($this->messageSentReport(
            400,
            'Client error: invalid subscription endpoint.'
        ));

        self::assertFalse($report->isSuccess());
        self::assertFalse($report->shouldRemoveSubscription());
    }

    public function testUnauthorizedFailureDoesNotRemoveSubscription(): void
    {
        $report = PushSendReport::fromMessageSentReport($this->messageSentReport(
            401,
            'Client error: Unauthorized.'
        ));

        self::assertFalse($report->isSuccess());
        self::assertFalse($report->shouldRemoveSubscription());
    }

    public function testAuthenticationFailureDoesNotRemoveSubscription(): void
    {
        $report = PushSendReport::fromMessageSentReport($this->messageSentReport(
            403,
            'Client error: VAPID authentication failed.'
        ));

        self::assertFalse($report->isSuccess());
        self::assertFalse($report->shouldRemoveSubscription());
    }

    public function testGenericBadRequestDoesNotRemoveSubscription(): void
    {
        $report = PushSendReport::fromMessageSentReport($this->messageSentReport(
            400,
            'Client error: payload too large.'
        ));

        self::assertFalse($report->isSuccess());
        self::assertFalse($report->shouldRemoveSubscription());
    }

    private function messageSentReport(int $statusCode, string $reason): MessageSentReport
    {
        return new MessageSentReport(
            new Request('POST', 'https://fcm.googleapis.com/fcm/send/test'),
            new Response($statusCode),
            false,
            $reason
        );
    }
}
