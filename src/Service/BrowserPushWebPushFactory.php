<?php

namespace App\Service;

use Minishlink\WebPush\WebPush;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttplugClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class BrowserPushWebPushFactory implements BrowserPushWebPushFactoryInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    /**
     * WebPush sends through Symfony's HTTP client with the given options; the same client serves its sequential and
     * its concurrent sending, so the options (the pinned IP among them) hold for both.
     */
    public function create(array $auth, array $clientOptions, ?LoggerInterface $logger): WebPush
    {
        $client = $this->httpClient->withOptions($clientOptions);

        return new WebPush(
            $auth,
            client: new Psr18Client($client),
            asyncClient: new HttplugClient($client),
            logger: $logger,
        );
    }
}
