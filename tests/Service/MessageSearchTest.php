<?php

namespace App\Tests\Service;

use App\Service\MessageSearch;
use PHPUnit\Framework\TestCase;

/**
 * What the inbox search field sends to Manticore: every word required, as a prefix, without the signs of the
 * query language.
 */
class MessageSearchTest extends TestCase
{
    public function testEveryWordBecomesARequiredPrefix(): void
    {
        self::assertSame('coffee* berl*', MessageSearch::queryString('  coffee   berl '));
    }

    public function testQuerySignsSplitWords(): void
    {
        self::assertSame('Jean* Pierre* hi* there*', MessageSearch::queryString('Jean-Pierre "hi" (there) | !'));
    }

    public function testNothingLeftMeansNoQuery(): void
    {
        self::assertSame('', MessageSearch::queryString(' "*" - '));
    }
}
