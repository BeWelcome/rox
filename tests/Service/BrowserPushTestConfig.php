<?php

namespace App\Tests\Service;

use App\Service\BrowserPushConfig;

final class BrowserPushTestConfig
{
    public const string PUBLIC_KEY = 'BJl6pftxKiM7GrkHX-q_b0Rtj3aDOct1LkDvo_9zFwPZAFaB6rMwAwVRYmTIBTBBsqQ-OaSdOwXEN-86oVV8bkg';
    public const string PRIVATE_KEY = '2N9ejENMtuyDJe8fyBiIm-qcxdMqMHZBuXi36dob-Ow';

    public static function create(): BrowserPushConfig
    {
        return new BrowserPushConfig('mailto:test@example.org', self::PUBLIC_KEY, self::PRIVATE_KEY);
    }
}
