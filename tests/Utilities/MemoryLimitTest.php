<?php

namespace App\Tests\Utilities;

use App\Utilities\MemoryLimit;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * A task gets at least the memory it asks for, but never less than the process has.
 */
final class MemoryLimitTest extends TestCase
{
    private string $limit;

    protected function setUp(): void
    {
        $this->limit = (string) \ini_get('memory_limit');
    }

    protected function tearDown(): void
    {
        ini_set('memory_limit', $this->limit);
    }

    public function testALowerLimitIsRaisedForTheTaskOnly(): void
    {
        ini_set('memory_limit', '1536M');

        $during = MemoryLimit::atLeast('2G', static fn (): string => (string) \ini_get('memory_limit'));

        self::assertSame('2G', $during);
        self::assertSame('1536M', \ini_get('memory_limit'));
    }

    public function testAHigherLimitOrNoneStays(): void
    {
        foreach (['3G', '-1'] as $limit) {
            ini_set('memory_limit', $limit);

            self::assertSame($limit, MemoryLimit::atLeast('2G', static fn (): string => (string) \ini_get('memory_limit')));
            self::assertSame($limit, \ini_get('memory_limit'));
        }
    }

    public function testTheLimitIsRestoredWhenTheTaskFails(): void
    {
        ini_set('memory_limit', '1536M');

        try {
            MemoryLimit::atLeast('2G', static fn () => throw new RuntimeException('zztest'));
            self::fail('The exception is passed on');
        } catch (RuntimeException $exception) {
            self::assertSame('zztest', $exception->getMessage());
        }

        self::assertSame('1536M', \ini_get('memory_limit'));
    }

    public function testSizesAreReadInBytes(): void
    {
        self::assertSame(512 * 1024 * 1024, MemoryLimit::bytes('512M'));
        self::assertSame(1024 ** 3, MemoryLimit::bytes('1g'));
        self::assertSame(2048, MemoryLimit::bytes('2K'));
        self::assertSame(1048576, MemoryLimit::bytes('1048576'));
    }
}
