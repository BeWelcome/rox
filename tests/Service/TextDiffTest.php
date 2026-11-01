<?php

namespace App\Tests\Service;

use App\Service\TextDiff;
use PHPUnit\Framework\TestCase;

class TextDiffTest extends TestCase
{
    public function testAChangedWordIsRemovedAndAdded(): void
    {
        self::assertSame([
            ['type' => 'same', 'text' => 'Hosts meet '],
            ['type' => 'removed', 'text' => 'travellers '],
            ['type' => 'added', 'text' => 'guests '],
            ['type' => 'same', 'text' => 'here.'],
        ], new TextDiff()->diff('Hosts meet travellers here.', 'Hosts meet guests here.'));
    }

    public function testAddedAndRemovedWordsInTheMiddle(): void
    {
        self::assertSame([
            ['type' => 'same', 'text' => 'Stay '],
            ['type' => 'removed', 'text' => 'for free '],
            ['type' => 'same', 'text' => 'with locals '],
            ['type' => 'added', 'text' => 'all over the world'],
            ['type' => 'same', 'text' => '.'],
        ], new TextDiff()->diff('Stay for free with locals.', 'Stay with locals all over the world.'));
    }

    public function testTagsAreWordsOfTheirOwn(): void
    {
        self::assertSame([
            ['type' => 'same', 'text' => 'Read the '],
            ['type' => 'added', 'text' => '<a href="/rules">'],
            ['type' => 'same', 'text' => 'rules'],
            ['type' => 'added', 'text' => '</a>'],
        ], new TextDiff()->diff('Read the rules', 'Read the <a href="/rules">rules</a>'));
    }

    public function testTheSameTextIsUnchanged(): void
    {
        self::assertSame([['type' => 'same', 'text' => 'Same text']], new TextDiff()->diff('Same text', 'Same text'));
        self::assertSame([], new TextDiff()->diff('', ''));
    }

    public function testLongTextsAreRemovedAndAddedAsAWhole(): void
    {
        $old = 'Start ' . str_repeat('old ', 600) . 'end';
        $new = 'Start ' . str_repeat('new ', 600) . 'end';

        self::assertSame(['same', 'removed', 'added', 'same'], array_column(new TextDiff()->diff($old, $new), 'type'));
    }
}
