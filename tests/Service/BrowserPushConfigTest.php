<?php

namespace App\Tests\Service;

use App\Service\BrowserPushConfig;
use PHPUnit\Framework\TestCase;

class BrowserPushConfigTest extends TestCase
{
    public function testAcceptsValidVapidConfiguration(): void
    {
        self::assertTrue(BrowserPushTestConfig::create()->isConfigured());
    }

    public function testRejectsInvalidVapidConfiguration(): void
    {
        self::assertFalse(new BrowserPushConfig('mailto:test@example.org', 'public-key', 'private-key')->isConfigured());
        self::assertFalse(new BrowserPushConfig('test@example.org', BrowserPushTestConfig::PUBLIC_KEY, BrowserPushTestConfig::PRIVATE_KEY)->isConfigured());
    }
}
