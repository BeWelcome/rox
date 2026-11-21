<?php

namespace App\Tests\Service;

use App\Service\BrowserPushWebPushFactory;
use Minishlink\WebPush\Subscription;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class BrowserPushWebPushFactoryTest extends TestCase
{
    public function testTheNotificationIsSentWithTheClientOptions(): void
    {
        $requests = [];
        $httpClient = new MockHttpClient(static function (string $method, string $url, array $options) use (&$requests): MockResponse {
            $requests[] = [$method, $url, $options];

            return new MockResponse('', ['http_code' => 201]);
        });
        $factory = new BrowserPushWebPushFactory($httpClient);

        $webPush = $factory->create([], [
            'max_redirects' => 0,
            'max_connect_duration' => 5,
            'max_duration' => 10,
            'resolve' => ['fcm.googleapis.com' => '142.250.185.10'],
        ], new NullLogger());
        $report = $webPush->sendOneNotification(Subscription::create(['endpoint' => 'https://fcm.googleapis.com/send/abc']));

        self::assertTrue($report->isSuccess());
        self::assertCount(1, $requests);
        [$method, $url, $options] = $requests[0];
        self::assertSame('POST', $method);
        self::assertSame('https://fcm.googleapis.com/send/abc', $url);
        self::assertSame(0, $options['max_redirects']);
        self::assertSame(5.0, (float) $options['max_connect_duration']);
        self::assertSame(10.0, (float) $options['max_duration']);
        self::assertSame(['fcm.googleapis.com' => '142.250.185.10'], $options['resolve']);
    }

    public function testAFailedRequestIsReported(): void
    {
        $factory = new BrowserPushWebPushFactory(new MockHttpClient(new MockResponse('gone', ['http_code' => 410])));

        $report = $factory->create([], [], null)
            ->sendOneNotification(Subscription::create(['endpoint' => 'https://fcm.googleapis.com/send/abc']));

        self::assertFalse($report->isSuccess());
        self::assertTrue($report->isSubscriptionExpired());
    }
}
