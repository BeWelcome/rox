<?php

namespace App\Tests\Service;

use App\Service\BrowserPushAfterResponse;
use App\Service\BrowserPushNotificationProcessor;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;
use RuntimeException;
use Stringable;

class BrowserPushAfterResponseTest extends TestCase
{
    public function testSendsTheNotificationsOfTheRequestOnceAfterTheResponse(): void
    {
        $processor = $this->createMock(BrowserPushNotificationProcessor::class);
        $processor->expects($this->once())->method('process')->with(2, [7, 9]);
        $afterResponse = new BrowserPushAfterResponse(static fn () => $processor, new NullLogger());

        $afterResponse->add(7);
        $afterResponse->add(9);
        $afterResponse->add(7);
        $afterResponse->send();
        // already sent
        $afterResponse->send();
    }

    public function testDoesNothingWithoutNotifications(): void
    {
        $afterResponse = new BrowserPushAfterResponse(
            fn () => $this->fail('the processor is not needed'),
            new NullLogger(),
        );

        $afterResponse->send();
        $this->addToAssertionCount(1);
    }

    public function testAFailureIsLoggedAndTheResponseIsNotAffected(): void
    {
        $processor = $this->createStub(BrowserPushNotificationProcessor::class);
        $processor->method('process')->willThrowException(new RuntimeException('database gone'));
        $logger = new class extends AbstractLogger {
            /** @var list<string> */
            public array $warnings = [];

            public function log($level, string|Stringable $message, array $context = []): void
            {
                $this->warnings[] = $level . ': ' . $message;
            }
        };
        $afterResponse = new BrowserPushAfterResponse(static fn () => $processor, $logger);

        $afterResponse->add(7);
        $afterResponse->send();

        self::assertSame(['warning: Sending browser push notifications after the response failed.'], $logger->warnings);
    }

    public function testResetForgetsTheNotificationsOfTheLastRequest(): void
    {
        $processor = $this->createMock(BrowserPushNotificationProcessor::class);
        $processor->expects($this->never())->method('process');
        $afterResponse = new BrowserPushAfterResponse(static fn () => $processor, new NullLogger());

        $afterResponse->add(7);
        $afterResponse->reset();
        $afterResponse->send();
    }
}
