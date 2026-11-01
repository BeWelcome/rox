<?php

namespace App\Service;

use Closure;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ResetInterface;
use Throwable;

/**
 * Browser push notifications about something a member just did (a message, a stay request, an invitation, a
 * friendship request) go out right after the response, when the member already sees the next page
 * (kernel.terminate). What fails stays queued: send:notifications sends it with the queue.
 */
final class BrowserPushAfterResponse implements ResetInterface
{
    /** @var array<int, true> */
    private array $notificationIds = [];

    /**
     * The processor is created when needed: it uses BrowserNotificationService, which uses this service.
     *
     * @param Closure(): BrowserPushNotificationProcessor $processor
     */
    public function __construct(
        #[AutowireServiceClosure(BrowserPushNotificationProcessor::class)]
        private readonly Closure $processor,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function add(int $notificationId): void
    {
        $this->notificationIds[$notificationId] = true;
    }

    #[AsEventListener(KernelEvents::TERMINATE)]
    public function send(): void
    {
        $notificationIds = array_keys($this->notificationIds);
        $this->notificationIds = [];
        if ([] === $notificationIds) {
            return;
        }

        try {
            ($this->processor)()->process(\count($notificationIds), $notificationIds);
        } catch (Throwable $throwable) {
            $this->logger->warning('Sending browser push notifications after the response failed.', [
                'exception' => $throwable,
            ]);
        }
    }

    public function reset(): void
    {
        $this->notificationIds = [];
    }
}
