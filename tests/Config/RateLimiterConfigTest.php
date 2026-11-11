<?php

namespace App\Tests\Config;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Yaml\Parser;

/**
 * The limits per address come from RATE_LIMIT_LOGIN_IP and RATE_LIMIT_SIGNUP_IP, so a test run whose requests all
 * come from one address can raise them; .env keeps 25 logins a minute and 10 sign-ups an hour. (The suite boots no
 * kernel: compiling the container reports the code's deprecations, which the phpunit bridge counts.)
 */
class RateLimiterConfigTest extends TestCase
{
    public function testTheLimitsPerAddressAreEnvVars(): void
    {
        $limiters = (new Parser())->parseFile(\dirname(__DIR__, 2) . '/config/packages/rate_limiter.yaml')['framework']['rate_limiter'];

        self::assertSame('%env(int:RATE_LIMIT_LOGIN_IP)%', $limiters['login_ip']['limit']);
        self::assertSame('%env(int:RATE_LIMIT_SIGNUP_IP)%', $limiters['signup_ip']['limit']);
        self::assertSame(10, $limiters['login_member']['limit'], 'the per-member limits stay fixed');
        self::assertSame(3, $limiters['confirmation_mail']['limit']);
    }

    public function testDotEnvHasTheDefaults(): void
    {
        $env = (new Dotenv())->parse((string) file_get_contents(\dirname(__DIR__, 2) . '/.env'));

        self::assertSame('25', $env['RATE_LIMIT_LOGIN_IP']);
        self::assertSame('20', $env['RATE_LIMIT_SIGNUP_IP']);
    }
}
