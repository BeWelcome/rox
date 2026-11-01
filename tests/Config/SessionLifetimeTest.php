<?php

namespace App\Tests\Config;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Members stay logged in for 7 days after logging in. The sessions are stored in the database; gc_maxlifetime is
 * how long one lasts without a request (PHP's default of 24 minutes logged members out while they wrote a message).
 */
#[Group('integration')]
class SessionLifetimeTest extends KernelTestCase
{
    public function testASessionLastsSevenDays(): void
    {
        self::bootKernel();
        $options = static::getContainer()->getParameter('session.storage.options');

        self::assertSame(7 * 24 * 3600, $options['gc_maxlifetime']);
        self::assertSame(7 * 24 * 3600, $options['cookie_lifetime']);
    }
}
