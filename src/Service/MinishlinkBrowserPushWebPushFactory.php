<?php

namespace App\Service;

use Minishlink\WebPush\WebPush;
use Psr\Log\LoggerInterface;

final readonly class MinishlinkBrowserPushWebPushFactory implements BrowserPushWebPushFactoryInterface
{
    public function create(array $auth, array $clientOptions, ?LoggerInterface $logger): WebPush
    {
        return new WebPush($auth, clientOptions: $clientOptions, logger: $logger);
    }
}
