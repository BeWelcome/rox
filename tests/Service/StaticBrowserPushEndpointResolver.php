<?php

namespace App\Tests\Service;

use App\Service\BrowserPushEndpointResolverInterface;

final class StaticBrowserPushEndpointResolver implements BrowserPushEndpointResolverInterface
{
    public function resolve(string $host): array
    {
        return match (true) {
            \in_array($host, [
                'fcm.googleapis.com',
                'updates.push.services.mozilla.com',
                'web.push.apple.com',
            ], true) => ['142.250.185.10'],
            'notify.windows.com' === $host || str_ends_with($host, '.notify.windows.com') => ['13.107.246.10'],
            default => [],
        };
    }
}
