<?php

namespace App\Tests\Service;

use App\Entity\BrowserPushSubscription;
use App\Entity\Member;
use App\Service\BrowserNotificationMessage;
use App\Service\BrowserPushEndpointResolverInterface;
use App\Service\BrowserPushEndpointValidator;
use App\Service\BrowserPushWebPushFactoryInterface;
use App\Service\MinishlinkPushGateway;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\SubscriptionInterface;
use Minishlink\WebPush\WebPush;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class MinishlinkPushGatewayTest extends TestCase
{
    public function testRejectsInvalidEndpointBeforeSending(): void
    {
        $gateway = new MinishlinkPushGateway(
            BrowserPushTestConfig::create(),
            new BrowserPushEndpointValidator($this->createStub(BrowserPushEndpointResolverInterface::class)),
            new NullLogger(),
            $this->webPushFactory()
        );

        $report = $gateway->send($this->subscription('https://127.0.0.1/push'), $this->message());

        self::assertFalse($report->isSuccess());
        self::assertTrue($report->shouldRemoveSubscription());
        self::assertSame('Invalid browser push endpoint.', $report->getError());
    }

    public function testTreatsProviderDnsFailureAsRetryable(): void
    {
        $resolver = $this->createStub(BrowserPushEndpointResolverInterface::class);
        $resolver->method('resolve')->willReturn([]);
        $factory = $this->createMock(BrowserPushWebPushFactoryInterface::class);
        $factory->expects($this->never())->method('create');
        $gateway = new MinishlinkPushGateway(
            BrowserPushTestConfig::create(),
            new BrowserPushEndpointValidator($resolver),
            new NullLogger(),
            $factory
        );

        $report = $gateway->send($this->subscription('https://fcm.googleapis.com/push'), $this->message());

        self::assertFalse($report->isSuccess());
        self::assertFalse($report->shouldRemoveSubscription());
        self::assertSame('Browser push endpoint could not be resolved safely.', $report->getError());
    }

    public function testPinsValidatedHostnameToResolvedIp(): void
    {
        $webPush = new class extends WebPush {
            public ?string $sentEndpoint = null;
            public array $sentOptions = [];

            public function __construct()
            {
            }

            public function sendOneNotification(
                SubscriptionInterface $subscription,
                ?string $payload = null,
                array $options = [],
                array $auth = [],
            ): MessageSentReport {
                $this->sentEndpoint = $subscription->getEndpoint();
                $this->sentOptions = $options;

                return new MessageSentReport(new Request('POST', $subscription->getEndpoint()), new Response(201));
            }
        };
        $factory = $this->createMock(BrowserPushWebPushFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->anything(),
                self::callback(static function (array $clientOptions): bool {
                    return false === ($clientOptions['allow_redirects'] ?? null)
                        && 5 === ($clientOptions['connect_timeout'] ?? null)
                        && 10 === ($clientOptions['timeout'] ?? null)
                        && ['fcm.googleapis.com:443:142.250.185.10']
                            === ($clientOptions['curl'][\CURLOPT_RESOLVE] ?? null);
                }),
                $this->anything()
            )
            ->willReturn($webPush)
        ;
        $resolver = $this->createMock(BrowserPushEndpointResolverInterface::class);
        $resolver->expects($this->once())
            ->method('resolve')
            ->with('fcm.googleapis.com')
            ->willReturn(['142.250.185.10'])
        ;
        $gateway = new MinishlinkPushGateway(
            BrowserPushTestConfig::create(),
            new BrowserPushEndpointValidator($resolver),
            new NullLogger(),
            $factory
        );

        $report = $gateway->send($this->subscription('https://FCM.googleapis.com./send'), $this->message());

        self::assertTrue($report->isSuccess());
        self::assertSame('https://fcm.googleapis.com/send', $webPush->sentEndpoint);
        self::assertSame(['TTL' => 3600], $webPush->sentOptions);
    }

    private function subscription(string $endpoint): BrowserPushSubscription
    {
        $subscription = new BrowserPushSubscription();
        $subscription->setMember(new Member());
        $subscription->setEndpoint($endpoint);
        $subscription->setPublicKey('public-key');
        $subscription->setAuthToken('auth-token');

        return $subscription;
    }

    private function message(): BrowserNotificationMessage
    {
        return new BrowserNotificationMessage('message', 'Title', 'Body', '/conversation/123');
    }

    private function webPushFactory(): BrowserPushWebPushFactoryInterface
    {
        return new class implements BrowserPushWebPushFactoryInterface {
            public function create(array $auth, array $clientOptions, ?\Psr\Log\LoggerInterface $logger): WebPush
            {
                return new class extends WebPush {
                    public function __construct()
                    {
                    }

                    public function sendOneNotification(
                        SubscriptionInterface $subscription,
                        ?string $payload = null,
                        array $options = [],
                        array $auth = [],
                    ): MessageSentReport {
                        return new MessageSentReport(
                            new Request('POST', $subscription->getEndpoint()),
                            new Response(201)
                        );
                    }
                };
            }
        };
    }
}
