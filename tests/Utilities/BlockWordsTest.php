<?php

namespace App\Tests\Utilities;

use App\Utilities\BlockWords;
use PHPUnit\Framework\TestCase;

final class BlockWordsTest extends TestCase
{
    public function testTheWordsAreFoundOnceInTheText(): void
    {
        self::assertSame(['@', 'support'], BlockWords::found('<p>Mail <a href="mailto:a@b.org">a@b.org</a> or SUPPORT, support.</p>'));
        self::assertSame([], BlockWords::found('<p class="system">Hello</p>'));
        self::assertSame(['support'], BlockWords::found('ｓｕｐｐｏｒｔ'));
    }

    public function testTheWordsAreMarkedOutsideTheTags(): void
    {
        self::assertSame(
            '<p>Mail <a href="mailto:a@b.org">a<mark>@</mark>b.org</a>, <mark>Verif</mark>y</p>',
            BlockWords::mark('<p>Mail <a href="mailto:a@b.org">a@b.org</a>, Verify</p>'),
        );
    }
}
