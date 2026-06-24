<?php

namespace App\Service;

use App\Entity\BrowserPushSubscription;
use Minishlink\WebPush\Subscription as WebPushSubscription;
use Psr\Log\LoggerInterface;

final readonly class MinishlinkPushGateway implements PushGatewayInterface
{
    public function __construct(
        private BrowserPushConfig $config,
        private BrowserPushEndpointValidator $endpointValidator,
        private LoggerInterface $logger,
        private BrowserPushWebPushFactoryInterface $webPushFactory,
    ) {
    }

    public function send(BrowserPushSubscription $subscription, BrowserNotificationMessage $message): PushSendReport
    {
        if (!$this->config->isConfigured()) {
            return PushSendReport::failed('Web Push is not configured.');
        }
        $validatedEndpoint = $this->endpointValidator->getValidatedEndpoint($subscription->getEndpoint());
        if (null === $validatedEndpoint) {
            return PushSendReport::rejected('Invalid browser push endpoint.');
        }

        $webPush = $this->webPushFactory->create([
            'VAPID' => [
                'subject' => $this->config->getSubject(),
                'publicKey' => $this->config->getPublicKey(),
                'privateKey' => $this->config->getPrivateKey(),
            ],
        ], $this->getClientOptions($validatedEndpoint), $this->logger);

        $report = $webPush->sendOneNotification(
            WebPushSubscription::create([
                'endpoint' => $validatedEndpoint->getCanonicalEndpoint(),
                'publicKey' => $subscription->getPublicKey(),
                'authToken' => $subscription->getAuthToken(),
                'contentEncoding' => $subscription->getContentEncoding() ?? 'aes128gcm',
            ]),
            $message->toJson()
        );

        return PushSendReport::fromMessageSentReport($report);
    }

    private function getClientOptions(ValidatedBrowserPushEndpoint $endpoint): array
    {
        $pinnedIp = $endpoint->getPinnedIp();
        if (null === $pinnedIp) {
            return ['allow_redirects' => false];
        }

        if (filter_var($pinnedIp, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
            $pinnedIp = '[' . $pinnedIp . ']';
        }

        return [
            'allow_redirects' => false,
            'curl' => [
                \CURLOPT_RESOLVE => [
                    \sprintf('%s:%d:%s', $endpoint->getHost(), $endpoint->getPort(), $pinnedIp),
                ],
            ],
        ];
    }
}
