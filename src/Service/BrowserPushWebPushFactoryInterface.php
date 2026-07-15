<?php

namespace App\Service;

use Minishlink\WebPush\WebPush;
use Psr\Log\LoggerInterface;

interface BrowserPushWebPushFactoryInterface
{
    public function create(array $auth, array $clientOptions, ?LoggerInterface $logger): WebPush;
}
